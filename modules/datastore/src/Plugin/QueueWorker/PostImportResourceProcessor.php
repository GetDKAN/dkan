<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\common\DataResource;
use Drupal\datastore\DataDictionary\AlterTableQueryBuilderInterface;
use Drupal\datastore\PostImportResult;
use Drupal\datastore\DatastoreService;
use Drupal\datastore\Service\PostImport;
use Drupal\datastore\Service\ResourceProcessor\ResourceDoesNotHaveDictionary;
use Drupal\datastore\Service\ResourceProcessorCollector;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;
use Drupal\metastore\Reference\ReferenceLookup;
use Drupal\metastore\ResourceMapper;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Apply specified data-dictionary to datastore belonging to specified dataset.
 *
 * @QueueWorker(
 *   id = "post_import",
 *   title = @Translation("Pass along new resources to resource processors"),
 *   cron = {
 *     "time" = 180,
 *     "lease_time" = 10800
 *   }
 * )
 */
class PostImportResourceProcessor extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * The datastore.settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * A logger channel for this plugin.
   */
  protected LoggerInterface $logger;

  /**
   * The metastore resource mapper service.
   */
  protected ResourceMapper $resourceMapper;

  /**
   * The resource processor collector service.
   */
  protected ResourceProcessorCollector $resourceProcessorCollector;

  /**
   * The datastore service.
   */
  protected DatastoreService $datastoreService;

  /**
   * The PostImport service.
   */
  protected PostImport $postImport;

  /**
   * Data dictionary discovery service.
   *
   * @var \Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface
   */
  protected $dataDictionaryDiscovery;

  /**
   * Reference lookup service.
   *
   * @var \Drupal\metastore\Reference\ReferenceLookup
   */
  protected $referenceLookup;

  /**
   * Build queue worker.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config.factory service.
   * @param \Drupal\datastore\DataDictionary\AlterTableQueryBuilderInterface $alter_table_query_builder
   *   The alter table query factory service.
   * @param \Psr\Log\LoggerInterface $logger_channel
   *   A logger channel factory instance.
   * @param \Drupal\metastore\ResourceMapper $resource_mapper
   *   The metastore resource mapper service.
   * @param \Drupal\datastore\Service\ResourceProcessorCollector $processor_collector
   *   The resource processor collector service.
   * @param \Drupal\datastore\DatastoreService $datastoreService
   *   The resource datastore service.
   * @param \Drupal\datastore\Service\PostImport $post_import
   *   The post import service.
   * @param \Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface $data_dictionary_discovery
   *   The data-dictionary discovery service.
   * @param \Drupal\metastore\Reference\ReferenceLookup $referenceLookup
   *   The reference lookup service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $configFactory,
    AlterTableQueryBuilderInterface $alter_table_query_builder,
    LoggerInterface $logger_channel,
    ResourceMapper $resource_mapper,
    ResourceProcessorCollector $processor_collector,
    DatastoreService $datastoreService,
    PostImport $post_import,
    DataDictionaryDiscoveryInterface $data_dictionary_discovery,
    ReferenceLookup $referenceLookup
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->config = $configFactory;
    $this->logger = $logger_channel;
    $this->resourceMapper = $resource_mapper;
    $this->resourceProcessorCollector = $processor_collector;
    $this->datastoreService = $datastoreService;
    $this->postImport = $post_import;
    $this->dataDictionaryDiscovery = $data_dictionary_discovery;
    // Set the timeout for database connections to the queue lease time.
    // This ensures that database connections will remain open for the
    // duration of the time the queue is being processed.
    $timeout = (int) $plugin_definition['cron']['lease_time'];
    $alter_table_query_builder->setConnectionTimeout($timeout);
    $this->referenceLookup = $referenceLookup;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('dkan.datastore.data_dictionary.alter_table_query_builder.mysql'),
      $container->get('dkan.datastore.logger_channel'),
      $container->get('dkan.metastore.resource_mapper'),
      $container->get('dkan.datastore.service.resource_processor_collector'),
      $container->get('dkan.datastore.service'),
      $container->get('dkan.datastore.service.post_import'),
      $container->get('dkan.metastore.data_dictionary_discovery'),
      $container->get('dkan.metastore.reference_lookup'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $postImportResult = $this->postImportProcessItem($data);
    $drop_config = $this->config->get('datastore.settings')
      ->get('drop_datastore_on_post_import_error');

    if ($postImportResult->getPostImportStatus() === 'done') {
      $this->invalidateCacheTags(DataResource::buildUniqueIdentifier(
        $data->getIdentifier(),
        $data->getVersion(),
        DataResource::DEFAULT_SOURCE_PERSPECTIVE
      ));
    }
    if ($postImportResult->getPostImportStatus() === 'error' && $drop_config) {
      $identifier = $data->getIdentifier();
      try {
        $this->datastoreService->drop($identifier, NULL, FALSE);
        $this->logger->notice('Successfully dropped the datastore for resource @identifier due to a post import error. Visit the Datastore Import Status dashboard for details.', [
          '@identifier' => $identifier,
        ]);
      }
      catch (\Exception $e) {
        $this->logger->error($e->getMessage());
      }
    }
    // Store the results of the PostImportResult object.
    $postImportResult->storeResult();
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
   */
  public function postImportProcessItem(DataResource $resource): PostImportResult {
    $latest_resource = $this->resourceMapper->get($resource->getIdentifier());

    // Stop if resource no longer exists.
    if (!isset($latest_resource)) {
      $this->logger->notice('Cancelling resource processing; resource no longer exists.');
      return $this->createPostImportResult('error', 'Cancelling resource processing; resource no longer exists.', $resource);
    }
    // Stop if resource has changed.
    if ($resource->getVersion() !== $latest_resource->getVersion()) {
      $this->logger->notice('Cancelling resource processing; resource has changed.');
      return $this->createPostImportResult('error', 'Cancelling resource processing; resource has changed.', $resource);
    }

    try {
      // Run all tagged resource processors.
      $processors = $this->resourceProcessorCollector->getResourceProcessors();

      if (DataDictionaryDiscoveryInterface::MODE_NONE === $this->dataDictionaryDiscovery->getDataDictionaryMode()) {
        $postImportResult = $this->createPostImportResult('N/A', 'Data-Dictionary Disabled', $resource);
      }
      else {
        array_map(fn ($processor) => $processor->process($resource), $processors);
        $postImportResult = $this->createPostImportResult('done', NULL, $resource);
        $this->logger->notice('Post import job for resource @id completed.', ['@id' => (string) $resource->getIdentifier()]);
      }
    }
    catch (ResourceDoesNotHaveDictionary $e) {
      // ResourceDoesNotHaveDictionary means there was no data dictionary for
      // the given resource. This is not an error because not all resources have
      // data dictionaries, but we should tell the user in case they think the
      // resource should have one.
      // @see \Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer::getDataDictionaryForResource()
      $this->logger->notice($e->getMessage());
      $postImportResult = $this->createPostImportResult('done', 'Resource ' . $e->getResourceId() . ' does not have a data dictionary.', $resource);
    }
    catch (\Exception $e) {
      // General catch-all for errors.
      $this->logger->error($e->getMessage());
      $postImportResult = $this->createPostImportResult('error', $e->getMessage(), $resource);
    }

    return $postImportResult;
  }

  /**
   * Invalidate all appropriate cache tags for this resource.
   *
   * @param mixed $resourceId
   *   A resource ID.
   */
  protected function invalidateCacheTags(mixed $resourceId) {
    $this->referenceLookup->invalidateReferencerCacheTags('distribution', $resourceId, 'downloadURL');
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
   */
  private function createPostImportResult($status, $message, DataResource $resource): PostImportResult {
    return new PostImportResult([
      'resource_identifier' => $resource->getIdentifier(),
      'resourceVersion' => $resource->getVersion(),
      'postImportStatus' => $status,
      'postImportMessage' => $message,
    ], $this->resourceMapper, $this->postImport);
  }

}
