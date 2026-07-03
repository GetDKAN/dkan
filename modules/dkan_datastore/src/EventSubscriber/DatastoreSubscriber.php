<?php

namespace Drupal\dkan_datastore\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\dkan_common\DataResource;
use Drupal\dkan_common\Events\Event;
use Drupal\dkan_datastore\DatastoreService;
use Drupal\dkan_datastore\Service\ResourceLocalizer;
use Drupal\dkan_datastore\Service\ResourcePurger;
use Drupal\dkan_datastore\Storage\ImportJobStoreFactory;
use Drupal\dkan_metastore\LifeCycle\LifeCycle;
use Drupal\dkan_metastore\MetastoreItemInterface;
use Drupal\dkan_metastore\ResourceMapper;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Subscriber.
 */
class DatastoreSubscriber implements EventSubscriberInterface {

  /**
   * Config factory service.
   */
  private ConfigFactoryInterface $configFactory;

  /**
   * Datastore logger channel service.
   */
  protected LoggerInterface $logger;

  /**
   * Datastore service.
   */
  private DatastoreService $datastoreService;

  /**
   * Resource purger service.
   */
  private ResourcePurger $resourcePurger;

  /**
   * Import job store factory.
   */
  private ImportJobStoreFactory $importJobStoreFactory;

  /**
   * Event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected EventDispatcherInterface $eventDispatcher;

  /**
   * Inherited.
   *
  * @inheritdoc
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('dkan.datastore.logger_channel'),
      $container->get('dkan.datastore.service'),
      $container->get('dkan.datastore.service.resource_purger'),
      $container->get('dkan.datastore.import_job_store_factory'),
      $container->get('event_dispatcher')
    );
  }

  /**
   * Constructor.
   *
    * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
    *   Config factory.
   * @param \Psr\Log\LoggerInterface $loggerChannel
   *   Logger channel.
   * @param \Drupal\dkan_datastore\DatastoreService $service
   *   The dkan.datastore.service service.
   * @param \Drupal\dkan_datastore\Service\ResourcePurger $resourcePurger
   *   The dkan.datastore.service.resource_purger service.
   * @param \Drupal\dkan_datastore\Storage\ImportJobStoreFactory $importJobStoreFactory
   *   The dkan.datastore.import_job_store_factory service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
   *   The event dispatcher service.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    LoggerInterface $loggerChannel,
    DatastoreService $service,
    ResourcePurger $resourcePurger,
    ImportJobStoreFactory $importJobStoreFactory,
    EventDispatcherInterface $eventDispatcher,
  ) {
    $this->configFactory = $configFactory;
    $this->logger = $loggerChannel;
    $this->datastoreService = $service;
    $this->resourcePurger = $resourcePurger;
    $this->importJobStoreFactory = $importJobStoreFactory;
    $this->eventDispatcher = $eventDispatcher;
  }

  /**
   * Inherited.
   *
   * @codeCoverageIgnore
   * @inheritdoc
   */
  public static function getSubscribedEvents(): array {
    $events = [];
    $events[ResourceMapper::EVENT_RESOURCE_MAPPER_PRE_REMOVE_SOURCE][] = ['drop'];
    $events[ResourceMapper::EVENT_REGISTRATION][] = ['onRegistration'];
    $events[LifeCycle::EVENT_DATASET_UPDATE][] = ['purgeResources'];
    $events[LifeCycle::EVENT_PRE_REFERENCE][] = ['onPreReference'];
    $events[ResourceLocalizer::EVENT_RESOURCE_LOCALIZED][] = ['onLocalizeComplete'];
    return $events;
  }

  /**
   * The resource mapper has registered a resource.
   *
   * @param \Drupal\dkan_common\Events\Event $event
   *   Event.
   *
   * @see ResourceMapper::EVENT_REGISTRATION
   */
  public function onRegistration(Event $event) {
    $resource = $event->getData();

    if ($resource->getPerspective() == 'source' && $this->isDataStorable($resource)) {
      try {
        $this->datastoreService->import($resource->getIdentifier(), TRUE, $resource->getVersion());
      }
      catch (\Exception $e) {
        $this->logger->error($e->getMessage());
      }
    }
  }

  /**
   * Private.
   */
  private function isDataStorable(DataResource $resource) : bool {
    return in_array($resource->getMimeType(), DataResource::IMPORTABLE_FILE_TYPES);
  }

  /**
   * Purge resources.
   *
   * @param \Drupal\dkan_common\Events\Event $event
   *   Dataset publication.
   */
  public function purgeResources(Event $event) {
    $node = $event->getData();
    $this->resourcePurger->schedule([$node->getIdentifier()]);
  }

  /**
   * React to a distribution being orphaned.
   *
   * @param \Drupal\dkan_common\Events\Event $event
   *   The event object containing the resource object.
   */
  public function drop(Event $event) {
    $resource = $event->getData();
    $id = md5(str_replace(DataResource::DEFAULT_SOURCE_PERSPECTIVE, ResourceLocalizer::LOCAL_FILE_PERSPECTIVE, $resource->getUniqueIdentifier()));
    try {
      // @todo Check if the datastore exists before dropping. Don't log an
      //   error if it wasn't there to begin with.
      $this->datastoreService->drop($resource->getIdentifier(), $resource->getVersion());
      $this->logger->notice('Dropping datastore for @id', ['@id' => $id]);
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to drop datastore for @id. @message',
      [
        '@id' => $id,
        '@message' => $e->getMessage(),
      ]);
    }
    try {
      $this->importJobStoreFactory->getInstance()->remove($id);
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to remove importer job. @message',
      [
        '@message' => $e->getMessage(),
      ]);
    }
  }

  /**
   * React to a preReference to check if datastore update should be triggered.
   *
   * @param \Drupal\dkan_common\Events\Event $event
   *   The event object containing pre-reference context.
   */
  public function onPreReference(Event $event) {
    $eventData = $event->getData();
    $item = NULL;

    // New explicit form passes a payload object with the item and decision.
    if (is_object($eventData) && isset($eventData->item) && $eventData->item instanceof MetastoreItemInterface) {
      $item = $eventData->item;
    }
    // Backward compatibility for direct MetastoreItem event payloads.
    elseif ($eventData instanceof MetastoreItemInterface) {
      $item = $eventData;
    }

    if (!$item) {
      return;
    }

    $triggers = array_filter($this->configFactory->get('dkan_datastore.settings')->get('triggering_properties') ?? []);
    if (empty($triggers)) {
      return;
    }

    $baseline = NULL;
    if (method_exists($item, 'getLatestRevision')) {
      $latest = $item->getLatestRevision();
      if ($latest instanceof MetastoreItemInterface) {
        $baseline = $latest->getMetadata();
      }
    }

    if (!is_object($baseline)) {
      $baseline = $item->getRawMetadata();
    }

    if (!is_object($baseline)) {
      return;
    }

    $shouldCreateNewResourceVersion = FALSE;
    $metadata = $item->getMetadata();
    foreach ($triggers as $property) {
      if (($baseline->{$property} ?? NULL) != ($metadata->{$property} ?? NULL)) {
        $shouldCreateNewResourceVersion = TRUE;
        break;
      }
    }

    if (is_object($eventData)) {
      $eventData->createNewResourceVersion = $shouldCreateNewResourceVersion;
      $event->setData($eventData);
    }
  }

  /**
   * React to files being localized.
   *
   * This happens when the source CSV has been downloaded to the local file
   * system. When that happens successfully, we create queue items for importing
   * the file into the database.
   *
   * @param \Drupal\dkan_common\Events\Event $event
   *   The Event.
   *
   * @see \Drupal\dkan_datastore\Service\ResourceLocalizer::EVENT_RESOURCE_LOCALIZED
   */
  public function onLocalizeComplete(Event $event) {
    $data = $event->getData();
    $this->datastoreService->import(
      $data['identifier'] ?? NULL,
      TRUE,
      $data['version'] ?? NULL
    );
  }

}
