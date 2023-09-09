<?php

namespace Drupal\datastore;

use Drupal\common\DataResource;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\datastore\Service\Factory\ImportFactoryInterface;
use Drupal\datastore\Service\Info\ImportInfoList;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer;
use Drupal\metastore\ResourceMapper;
use FileFetcher\FileFetcher;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Main services for the datastore.
 */
class DatastoreService implements ContainerInjectionInterface {

  /**
   * Resource localizer for handling remote resource URLs.
   *
   * @var \Drupal\datastore\Service\ResourceLocalizer
   */
  private $resourceLocalizer;

  /**
   * Datastore import factory class.
   *
   * @var \Drupal\datastore\Service\Factory\ImportFactoryInterface
   */
  private $importServiceFactory;

  /**
   * Drupal queue.
   *
   * @var \Drupal\Core\Queue\QueueFactory
   */
  private $queue;

  /**
   * JobStore factory object.
   *
   * @var \Drupal\common\Storage\JobStoreFactory
   */
  private $jobStoreFactory;

  /**
   * Datastore Query object for conversion.
   *
   * @var \Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer
   */
  private $dictionaryEnforcer;

  /**
   * Resource mapper service.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  private ResourceMapper $resourceMapper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.datastore.service.resource_localizer'),
      $container->get('dkan.datastore.service.factory.import'),
      $container->get('queue'),
      $container->get('dkan.common.job_store'),
      $container->get('dkan.datastore.import_info_list'),
      $container->get('dkan.datastore.service.resource_processor.dictionary_enforcer'),
      $container->get('dkan.metastore.resource_mapper')
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\datastore\Service\ResourceLocalizer $resourceLocalizer
   *   Resource localizer service.
   * @param \Drupal\datastore\Service\Factory\ImportFactoryInterface $importServiceFactory
   *   Import factory service.
   * @param \Drupal\Core\Queue\QueueFactory $queue
   *   Queue factory service.
   * @param \Drupal\common\Storage\JobStoreFactory $jobStoreFactory
   *   Jobstore factory service.
   * @param \Drupal\datastore\Service\Info\ImportInfoList $importInfoList
   *   Import info list service.
   * @param \Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer $dictionaryEnforcer
   *   Dictionary Enforcer object.
   * @param \Drupal\metastore\ResourceMapper $resourceMapper
   *   Resource mapper service.
   */
  public function __construct(
    ResourceLocalizer $resourceLocalizer,
    ImportFactoryInterface $importServiceFactory,
    QueueFactory $queue,
    JobStoreFactory $jobStoreFactory,
    ImportInfoList $importInfoList,
    DictionaryEnforcer $dictionaryEnforcer,
    ResourceMapper $resourceMapper
  ) {
    $this->queue = $queue;
    $this->resourceLocalizer = $resourceLocalizer;
    $this->importServiceFactory = $importServiceFactory;
    $this->jobStoreFactory = $jobStoreFactory;
    $this->importInfoList = $importInfoList;
    $this->dictionaryEnforcer = $dictionaryEnforcer;
    $this->resourceMapper = $resourceMapper;
  }

  /**
   * Start the import process for a resource.
   *
   * This is the entry point for both the file localization step and the
   * database import step. This method knows how to do both.
   *
   * This method will also try to short-circuit import steps if they are already
   * complete.
   *
   * This method should also not re-trigger processes that are already in
   * progress.
   *
   * @param string $identifier
   *   The data resource identifier.
   * @param bool $deferred
   *   (Optional) Whether to create queue workers for the import process. If
   *   TRUE, will create a localize_import queue worker for the resource, which
   *   will in turn create a datastore_import worker when successful. If FALSE,
   *   will perform file localization and then data import without queueing
   *   jobs. Defaults to FALSE.
   * @param string|null $version
   *   (Optional) The resource version. If NULL, the most recent version will
   *   be used.
   *
   * @return array
   *   Array of response messages from the various import-related services we
   *   call. Key is the name of the class, value is the message.
   */
  public function import(string $identifier, bool $deferred = FALSE, $version = NULL): array {
    $results = [];
    // @todo Determine if we have already done all this for the whole dataset
    //   before continuing.
    // Have we localized yet?
    if (
      $this->resourceMapper->get($identifier, ResourceLocalizer::LOCAL_FILE_PERSPECTIVE, $version) === NULL
    ) {
      $result = $this->resourceLocalizer->localizeTask($identifier, $version, $deferred);
      $results[$this->getLabelFromObject($this->resourceLocalizer)] = $result;
      // If the localize task is deferred, then it will send events to
      // re-trigger the database import later, so we should stop here.
      if ($deferred) {
        return $results;
      }
    }

    // Now work on the database. If we passed $deferred, add to the queue for
    // later.
    if ($deferred) {
      return $this->importDeferred($identifier, $version);
    }

    // Get the resource object.
    $resource = $this->resourceLocalizer->get($identifier, $version);
    if (!$resource) {
      return $results;
    }
    // Do the database import.
    return array_merge(
      $results,
      $this->doImport($resource)
    );
  }

  /**
   * Create a queue item for the import.
   *
   * @param string $identifier
   *   The data resource identifier.
   * @param string|null $version
   *   (Optional) The resource version. If NULL, the most recent version will
   *   be used.
   *
   * @return array
   *   Array of response messages from the various import-related services we
   *   call. Key is the name of the class, value is the message.
   */
  public function importDeferred(string $identifier, $version = NULL): array {
    $queueId = $this->queue->get('datastore_import')->createItem([
      'identifier' => $identifier,
      'version' => $version,
    ]);

    if ($queueId === FALSE) {
      throw new \RuntimeException('Failed to create datastore_import queue for ' . $identifier . ':' . $version);
    }
    return [
      'message' => 'Resource ' . $identifier . ':' . $version . ' has been queued to be imported.',
    ];
  }

  /**
   * Private.
   */
  private function doImport($resource) {
    $importService = $this->getImportService($resource);
    $importService->import();
    return [$this->getLabelFromObject($importService) => $importService->getResult()];
  }

  /**
   * Private.
   */
  private function getLabelFromObject($object) {
    return substr(strrchr(get_class($object), '\\'), 1);
  }

  /**
   * Getter.
   */
  public function getImportService(DataResource $resource) {
    return $this->importServiceFactory->getInstance($resource->getUniqueIdentifier(), ['resource' => $resource]);
  }

  /**
   * Returns the Data Dictionary fields.
   */
  public function getDataDictionaryFields() {
    return $this->dictionaryEnforcer->returnDataDictionaryFields();
  }

  /**
   * Drop a resources datastore.
   *
   * @param string $identifier
   *   A resource's identifier.
   * @param string|null $version
   *   A resource's version.
   * @param bool $remove_local_resource
   *   (optional) Whether to remove the local resource. If FALSE, keep the
   *   localized files for this resource. Defaults to TRUE.
   */
  public function drop(string $identifier, ?string $version = NULL, bool $remove_local_resource = TRUE) {
    $storage = $this->getStorage($identifier, $version);
    $resource = $this->resourceLocalizer->get($identifier, $version);

    if ($storage) {
      $storage->destruct();
      $this->jobStoreFactory
        ->getInstance(ImportJob::class)
        ->remove(md5($resource->getUniqueIdentifier()));
    }

    if ($remove_local_resource) {
      $this->resourceLocalizer->remove($identifier, $version);
      $this->jobStoreFactory
        ->getInstance(FileFetcher::class)
        ->remove($resource->getUniqueIdentifierNoPerspective());
    }
  }

  /**
   * Get a list of all stored importers and filefetchers, and their status.
   *
   * @return array
   *   The importer list object.
   */
  public function list() {
    return $this->importInfoList->buildList();
  }

  /**
   * Summary.
   */
  public function summary($identifier) {
    $id = NULL;
    $version = NULL;
    [$id, $version] = DataResource::getIdentifierAndVersion($identifier);
    $storage = $this->getStorage($id, $version);

    if ($storage) {
      $data = $storage->getSummary();
      return $data;
    }
    throw new \Exception('no storage');
  }

  /**
   * Get Storage.
   *
   * @param string $identifier
   *   The unique identifier of a resource.
   * @param string|null $version
   *   The version of the resource.
   *
   * @return \Drupal\datastore\Storage\DatabaseTable
   *   Storage object.
   *
   * @throws \InvalidArgumentException
   */
  public function getStorage(string $identifier, $version = NULL) {
    $resource = $this->resourceMapper->get(
      $identifier,
      ResourceLocalizer::LOCAL_FILE_PERSPECTIVE,
      $version
    );
    if ($resource) {
      $importService = $this->getImportService($resource);
      return $importService->getStorage();
    }
    throw new \InvalidArgumentException('No datastore storage found for ' . $identifier . ':' . $version . '.');
  }

  /**
   * Return the resource localizer.
   *
   * @return \Drupal\datastore\Service\ResourceLocalizer
   *   Resource localizer.
   */
  public function getResourceLocalizer() : ResourceLocalizer {
    return $this->resourceLocalizer;
  }

  /**
   * Return the queue factory.
   *
   * @return \Drupal\Core\Queue\QueueFactory
   *   Queue factory.
   */
  public function getQueueFactory(): QueueFactory {
    return $this->queue;
  }

}
