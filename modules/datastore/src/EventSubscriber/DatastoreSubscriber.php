<?php

namespace Drupal\datastore\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

use Drupal\common\Events\Event;
use Drupal\common\DataResource;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\DatastoreService;
use Drupal\datastore\Service\ResourcePurger;
use Drupal\metastore\LifeCycle\LifeCycle;
use Drupal\metastore\MetastoreItemInterface;
use Drupal\metastore\ResourceMapper;

use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber.
 */
class DatastoreSubscriber implements EventSubscriberInterface {

  /**
   * Drupal Config Factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $loggerFactory;

  /**
   * Inherited.
   *
   * @{inheritdocs}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('logger.factory'),
      $container->get('dkan.datastore.service'),
      $container->get('dkan.datastore.service.resource_purger'),
      $container->get('dkan.common.job_store')
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A ConfigFactory service instance.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory service.
   * @param \Drupal\datastore\DatastoreService $service
   *   The dkan.datastore.service service.
   * @param \Drupal\datastore\Service\ResourcePurger $resourcePurger
   *   The dkan.datastore.service.resource_purger service.
   * @param \Drupal\common\Storage\JobStoreFactory $jobStoreFactory
   *   The dkan.common.job_store service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LoggerChannelFactory $logger_factory, DatastoreService $service, ResourcePurger $resourcePurger, JobStoreFactory $jobStoreFactory) {
    $this->configFactory = $config_factory;
    $this->loggerFactory = $logger_factory;
    $this->service = $service;
    $this->resourcePurger = $resourcePurger;
    $this->jobStoreFactory = $jobStoreFactory;
  }

  /**
   * Inherited.
   *
   * @codeCoverageIgnore
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[ResourceMapper::EVENT_RESOURCE_MAPPER_PRE_REMOVE_SOURCE][] = ['drop'];
    $events[ResourceMapper::EVENT_REGISTRATION][] = ['onRegistration'];
    $events[LifeCycle::EVENT_DATASET_UPDATE][] = ['purgeResources'];
    $events[LifeCycle::EVENT_PRE_REFERENCE][] = ['onPreReference'];
    return $events;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function onRegistration(Event $event) {

    /** @var \Drupal\common\Events\Event $event */
    $resource = $event->getData();

    if ($resource->getPerspective() == 'source' && $this->isDataStorable($resource)) {
      try {
        $this->service->import($resource->getIdentifier(), TRUE, $resource->getVersion());
      }
      catch (\Exception $e) {
        $this->loggerFactory->get('datastore')->error($e->getMessage());
      }
    }
  }

  /**
   * Private.
   */
  private function isDataStorable(DataResource $resource) : bool {
    return in_array($resource->getMimeType(), [
      'text/csv',
      'text/tab-separated-values',
    ]);
  }

  /**
   * Purge resources.
   *
   * @param \Drupal\common\Events\Event $event
   *   Dataset publication.
   */
  public function purgeResources(Event $event) {
    $node = $event->getData();
    $this->resourcePurger->schedule([$node->getIdentifier()]);
  }

  /**
   * React to a distribution being orphaned.
   *
   * @param \Drupal\common\Events\Event $event
   *   The event object containing the resource object.
   */
  public function drop(Event $event) {
    /** @var \Drupal\common\Events\Event $event */
    $resource = $event->getData();
    $ref_uuid = $resource->getUniqueIdentifier();
    $id = md5(str_replace('source', 'local_file', $ref_uuid));
    try {
      $this->service->drop($resource->getIdentifier(), $resource->getVersion());
      $this->loggerFactory->get('datastore')->notice('Dropping datastore for @id', ['@id' => $id]);
    }
    catch (\Exception $e) {
      $this->loggerFactory->get('datastore')->error('Failed to drop datastore for @id. @message',
      [
        '@id' => $id,
        '@message' => $e->getMessage(),
      ]);
    }
    try {
      $this->jobStoreFactory->getInstance(ImportJob::class)->remove($id);
    }
    catch (\Exception $e) {
      $this->loggerFactory->get('datastore')->error('Failed to remove importer job. @message',
      [
        '@message' => $e->getMessage(),
      ]);
    }
  }

  /**
   * React to a preReference to check if datastore update should be triggered.
   *
   * @param \Drupal\common\Events\Event $event
   *   The event object containing the resource uuid.
   */
  public function onPreReference(Event $event) {
    // Attempt to retrieve new and original revisions of metadata object.
    $data = $event->getData();
    $original = $data->getOriginal();
    // Retrieve a list of metadata properties which, when changed, should
    // trigger a new metadata resource revision.
    $datastore_settings = $this->configFactory->get('datastore.settings');
    $triggers = array_filter($datastore_settings->get('triggering_properties') ?? []);
    // Ensure at least one trigger has been selected in datastore settings, and
    // that a valid MetastoreItem data object was found for the previous version
    // of the wrapped node.
    // If a change was found in one of the triggering elements, change the
    // "new revision" flag to true in order to trigger a datastore update.
    if (!empty($triggers) && $original instanceof MetastoreItemInterface &&
        $this->lazyDiffObject($original->getMetadata(), $data->getMetadata(), $triggers)) {
      // Assign value to static variable.
      $rev = &drupal_static('metastore_resource_mapper_new_revision');
      $rev = 1;
    }
  }

  /**
   * Determine differences in the supplied objects in the given property scope.
   *
   * @param object $a
   *   The first object being compared.
   * @param object $b
   *   The second object being compared.
   * @param array $scope
   *   Shared object properties being compared.
   *
   * @returns bool
   *   Whether any differences were found in the scoped two objects.
   */
  protected function lazyDiffObject($a, $b, array $scope): bool {
    $changed = FALSE;
    foreach ($scope as $property) {
      if ($a->{$property} != $b->{$property}) {
        $changed = TRUE;
        break;
      }
    }

    return $changed;
  }

}
