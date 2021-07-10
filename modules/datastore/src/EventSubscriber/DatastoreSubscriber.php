<?php

namespace Drupal\datastore\EventSubscriber;

use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\common\Resource;
use Drupal\common\Events\Event;
use Drupal\datastore\Service;
use Drupal\datastore\Service\ResourcePurger;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\LifeCycle\LifeCycle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Dkan\Datastore\Importer;
use Drupal\common\Storage\JobStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Subscriber.
 */
class DatastoreSubscriber implements EventSubscriberInterface {

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
      $container->get('logger.factory'),
      $container->get('dkan.datastore.service'),
      $container->get('dkan.datastore.service.resource_purger'),
      $container->get('dkan.common.job_store')
    );
  }

  /**
   * Constructor.
   *
   * @param Drupal\Core\Logger\LoggerChannelFactory $logger_factory
   *   LoggerChannelFactory service.
   * @param \Drupal\datastore\Service $service
   *   The dkan.datastore.service service.
   * @param \Drupal\datastore\Service\ResourcePurger $resourcePurger
   *   The dkan.datastore.service.resource_purger service.
   * @param \Drupal\common\Storage\JobStoreFactory $jobStoreFactory
   *   The dkan.common.job_store service.
   */
  public function __construct(LoggerChannelFactory $logger_factory, Service $service, ResourcePurger $resourcePurger, JobStoreFactory $jobStoreFactory) {
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
  private function isDataStorable(Resource $resource) : bool {
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
      $this->jobStoreFactory->getInstance(Importer::class)->remove($id);
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
    $data = $event->getData();
    $original = $data->getOriginal();
    $field = \Drupal::service('config.factory')->getEditable('datastore.settings')->get('triggering_property');
    $field = $field ? $field : 'modified';
    if ($original) {
      $old = $original->getMetaData();
      $new = $data->getMetaData();
      if ($old->{$field} != $new->{$field}) {
        // Assign value to static variable.
        $rev = &drupal_static('metastore_resource_mapper_new_revision');
        $rev = 1;
      }
    }
  }

}
