<?php

namespace Drupal\datastore\Service;

use Drupal\common\DataResource;
use Drupal\datastore\DatastoreService;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\datastore\PostImportResult;
use Drupal\datastore\Service\ResourceProcessor\ResourceDoesNotHaveDictionary;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;
use Psr\Log\LoggerInterface;
use Drupal\datastore\PostImportResultFactory;

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
   * The datastore service.
   *
   * @var \Drupal\datastore\DatastoreService
   */
  protected DatastoreService $datastoreService;

  /**
   * The post import result factory.
   *
   * @var \Drupal\datastore\PostImportResultFactory
   */
  protected PostImportResultFactory $postImportResultFactory;

  /**
   * Constructs a new PostImport service.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   * @param \Drupal\datastore\Service\ResourceProcessorCollector $resourceProcessorCollector
   *   The resource processor collector service.
   * @param \Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface $dataDictionaryDiscovery
   *   The data dictionary discovery interface.
   * @param \Drupal\datastore\DatastoreService $datastoreService
   *   The datastore service.
   * @param \Drupal\datastore\Service\PostImportResultFactory $postImportResultFactory
   *   The post import result factory.
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    LoggerInterface $logger,
    ResourceProcessorCollector $resourceProcessorCollector,
    DataDictionaryDiscoveryInterface $dataDictionaryDiscovery,
    DatastoreService $datastoreService,
    PostImportResultFactory $postImportResultFactory
  ) {
    $this->configFactory = $configFactory;
    $this->logger = $logger;
    $this->resourceProcessorCollector = $resourceProcessorCollector;
    $this->dataDictionaryDiscovery = $dataDictionaryDiscovery;
    $this->datastoreService = $datastoreService;
    $this->postImportResultFactory = $postImportResultFactory;
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
      $this->datastoreService->invalidateCacheTags($resource->getIdentifier());
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
    $latestResource = $this->datastoreService->getResourceMapper()->get($resource->getIdentifier());

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
    return $this->postImportResultFactory->initializeFromResource($status, $message, $resource);
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

}
