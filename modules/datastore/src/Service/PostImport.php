<?php

namespace Drupal\datastore\Service;

use Drupal\common\DataResource;
use Drupal\datastore\DatastoreService;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\datastore\PostImportResult;
use Drupal\datastore\Service\ResourceProcessor\ResourceDoesNotHaveDictionary;
use Drupal\datastore\Service\ResourceProcessorCollector;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;
use Drupal\metastore\Reference\ReferenceLookup;
use Drupal\metastore\ResourceMapper;
use Psr\Log\LoggerInterface;
use Drupal\Core\Database\Connection;

/**
 * Service to handle post-import resource processing.
 */
class PostImport {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected LoggerInterface $logger;

  /**
   * The resource mapper.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  protected ResourceMapper $resourceMapper;

  /**
   * The resource processor collector.
   *
   * @var \Drupal\datastore\Service\ResourceProcessorCollector
   */
  protected ResourceProcessorCollector $resourceProcessorCollector;

  /**
   * The data dictionary discovery interface.
   *
   * @var \Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface
   */
  protected DataDictionaryDiscoveryInterface $dataDictionaryDiscovery;

  /**
   * The reference lookup service.
   *
   * @var \Drupal\metastore\Reference\ReferenceLookup
   */
  protected ReferenceLookup $referenceLookup;

  /**
   * The post import result service.
   *
   * @var \Drupal\datastore\PostImportResult
   */
  protected PostImportResult $postImportResult;

  /**
   * The datastore service.
   *
   * @var \Drupal\datastore\DatastoreService
   */
  protected DatastoreService $datastoreService;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $connection;

  /**
   * Constructs a new PostImport service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   * @param \Drupal\metastore\ResourceMapper $resourceMapper
   *   The resource mapper service.
   * @param \Drupal\datastore\Service\ResourceProcessorCollector $resourceProcessorCollector
   *   The resource processor collector service.
   * @param \Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface $dataDictionaryDiscovery
   *   The data dictionary discovery interface.
   * @param \Drupal\metastore\Reference\ReferenceLookup $referenceLookup
   *   The reference lookup service.
   * @param \Drupal\datastore\DatastoreService $datastoreService
   *   The datastore service.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    LoggerInterface $logger,
    ResourceMapper $resourceMapper,
    ResourceProcessorCollector $resourceProcessorCollector,
    DataDictionaryDiscoveryInterface $dataDictionaryDiscovery,
    ReferenceLookup $referenceLookup,
    DatastoreService $datastoreService,
    Connection $connection,
  ) {
    $this->configFactory = $configFactory;
    $this->logger = $logger;
    $this->resourceMapper = $resourceMapper;
    $this->resourceProcessorCollector = $resourceProcessorCollector;
    $this->dataDictionaryDiscovery = $dataDictionaryDiscovery;
    $this->referenceLookup = $referenceLookup;
    $this->datastoreService = $datastoreService;
    $this->connection = $connection;
  }

  /**
   * Pass along new resource to resource processors.
   *
   * @todo This method should not contain references to data dictionary
   *   behavior. Put all the dictionary-related logic into
   *   DictionaryEnforcer::process().
   *
   * @param \Drupal\common\DataResource $resource
   *   DKAN Resource.
   *
   * @return \Drupal\datastore\PostImportResult
   *   The post import result service.
   */
  public function processResource(DataResource $resource): PostImportResult {
    if ($result = $this->validateResource($resource)) {
      return $result;
    }

    try {
      $this->processResourceProcessors($resource);
      $this->logger->notice('Post import job for resource @id completed.', ['@id' => $resource->getIdentifier()]);
      $this->invalidateCacheTags($resource->getIdentifier());
      return $this->createPostImportResult('done', NULL, $resource);
    }
    catch (ResourceDoesNotHaveDictionary $e) {

      $this->logger->notice($e->getMessage());
      return $this->createPostImportResult('done', 'Resource does not have a data dictionary.', $resource);
    }
    catch (\Exception $e) {

      $this->handleProcessingError($resource, $e);
      return $this->createPostImportResult('error', $e->getMessage(), $resource);
    }
  }

  /**
   * Handle errors during resource processing.
   *
   * @param \Drupal\common\DataResource $resource
   *   DKAN Resource.
   * @param \Exception $exception
   *   The caught exception.
   */
  private function handleProcessingError(DataResource $resource, \Exception $exception): void {
    $identifier = $resource->getIdentifier();

    if ($this->configFactory->get('datastore.settings')->get('drop_datastore_on_post_import_error')) {
      try {
        $this->drop($identifier, NULL, FALSE);
        $this->logger->notice('Successfully dropped the datastore for resource @identifier due to a post import error. Visit the Datastore Import Status dashboard for details.', [
          '@identifier' => $identifier,
        ]);
      }
      catch (\Exception $dropException) {
        $this->logger->error($dropException->getMessage());
      }
    }

    $this->logger->error($exception->getMessage());
  }

  /**
   * Process resource.
   *
   * @param \Drupal\common\DataResource $resource
   *   DKAN Resource.
   *
   * @throws \Exception
   */
  private function processResourceProcessors(DataResource $resource): void {
    $processors = $this->resourceProcessorCollector->getResourceProcessors();
    array_map(fn($processor) => $processor->process($resource), $processors);
  }

  /**
   * Validation checks before processing resource.
   *
   * @param \Drupal\common\DataResource $resource
   *   DKAN Resource.
   *
   * @return \Drupal\datastore\PostImportResult|null
   *   Post import result if validation fails, or NULL if validation passes.
   */
  private function validateResource(DataResource $resource): ?PostImportResult {
    $latestResource = $this->resourceMapper->get($resource->getIdentifier());

    if (!$latestResource) {
      $this->logger->notice('Cancelling resource processing; resource no longer exists.');
      return $this->createPostImportResult('error', 'Cancelling resource processing; resource no longer exists.', $resource);
    }

    if ($resource->getVersion() !== $latestResource->getVersion()) {
      $this->logger->notice('Cancelling resource processing; resource has changed.');
      return $this->createPostImportResult('error', 'Cancelling resource processing; resource has changed.', $resource);
    }

    if ($this->dataDictionaryDiscovery->getDataDictionaryMode() === DataDictionaryDiscoveryInterface::MODE_NONE) {
      $this->logger->notice('Data-Dictionary Disabled');
      return $this->createPostImportResult('N/A', 'Data-Dictionary Disabled', $resource);
    }

    return NULL;
  }

  /**
   * Create the PostImportResult object.
   *
   * @param string $status
   *   Status of the post import process.
   * @param string $message
   *   Error messages retrieved during the post import process.
   * @param \Drupal\common\DataResource $resource
   *   The DKAN resource being imported.
   *
   * @return \Drupal\datastore\PostImportResult
   *   The post import result service.
   */
  protected function createPostImportResult($status, $message, DataResource $resource): PostImportResult {
    return new PostImportResult([
      'resource_identifier' => $resource->getIdentifier(),
      'resourceVersion' => $resource->getVersion(),
      'postImportStatus' => $status,
      'postImportMessage' => $message,
    ],
    $this);
  }

  /**
   * Store row.
   *
   * @param string $resourceIdentifier
   *   The resource identifier of the distribution.
   * @param string $resourceVersion
   *   The resource version of the distribution.
   * @param string $status
   *   The status of the post import job.
   * @param string $message
   *   The error message of the post import job.
   */
  public function storeJobStatus($resourceIdentifier, $resourceVersion, $status, $message): bool {
    try {
      $this->connection->insert('dkan_post_import_job_status')
        ->fields([
          'resource_identifier' => $resourceIdentifier,
          'resource_version' => $resourceVersion,
          'post_import_status' => $status,
          'post_import_error' => $message,
        ])
        ->execute();

      return TRUE;
    }
    catch (\Exception) {
      return FALSE;
    }
  }

  /**
   * Retrieve row.
   *
   * @param string $resourceIdentifier
   *   The resource identifier of the distribution.
   * @param string $resourceVersion
   *   The resource version of the distribution.
   */
  public function retrieveJobStatus($resourceIdentifier, $resourceVersion) {
    try {
      return $this->connection->select('dkan_post_import_job_status')
        ->condition('resource_identifier', $resourceIdentifier, '=')
        ->condition('resource_version', $resourceVersion, '=')
        ->fields('dkan_post_import_job_status', [
          'resource_version',
          'post_import_status',
          'post_import_error',
        ])
        ->execute()
        ->fetchAssoc();
    }
    catch (\Exception) {
      return FALSE;
    }
  }

  /**
   * Remove row.
   *
   * @param string $resourceIdentifier
   *   The resource identifier of the distribution.
   */
  public function removeJobStatus($resourceIdentifier): bool {
    try {
      $latest_resource = $this->resourceMapper->get($resourceIdentifier);
      $latest_version = $latest_resource->getVersion();
      $this->connection->delete('dkan_post_import_job_status')
        ->condition('resource_identifier', $resourceIdentifier, '=')
        ->condition('resource_version', $latest_version, '=')
        ->execute();

      return TRUE;
    }
    catch (\Exception) {
      return FALSE;
    }
  }

  /**
   * Remove row.
   *
   * @param string $resourceIdentifier
   *   The resource identifier of the distribution.
   */
  public function drop($resourceIdentifier): bool {
    try {
      $this->datastoreService->drop($resourceIdentifier, NULL, FALSE);
      return TRUE;
    }
    catch (\Exception $e) {
      throw $e;
    }
  }

  /**
   * Invalidate all appropriate cache tags for this resource.
   *
   * @param mixed $resourceId
   *   A resource ID.
   */
  public function invalidateCacheTags($resourceId): void {
    $this->referenceLookup->invalidateReferencerCacheTags('distribution', $resourceId, 'downloadURL');
  }

}
