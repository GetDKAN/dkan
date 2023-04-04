<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;

use Drupal\common\DataResource;
use Drupal\datastore\DataDictionary\AlterTableQueryBuilderInterface;
use Drupal\datastore\Service\ResourceProcessorCollector;
use Drupal\metastore\ResourceMapper;
use Drupal\datastore\PostImportResult;
use Drupal\datastore\Service\PostImport;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;

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
   * A logger channel for this plugin.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected LoggerChannelInterface $logger;

  /**
   * The metastore resource mapper service.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  protected ResourceMapper $resourceMapper;

  /**
   * The resource processor collector service.
   *
   * @var \Drupal\datastore\Service\ResourceProcessorCollector
   */
  protected ResourceProcessorCollector $resourceProcessorCollector;

  /**
   * The PostImport service.
   *
   * @var \Drupal\datastore\Service\PostImport
   */
  protected PostImport $postImport;

  /**
   * Data dictionary discovery service.
   *
   * @var \Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface
   */
  protected $dataDictionaryDiscovery;

  /**
   * Build queue worker.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\datastore\DataDictionary\AlterTableQueryBuilderInterface $alter_table_query_builder
   *   The alter table query factory service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   A logger channel factory instance.
   * @param \Drupal\metastore\ResourceMapper $resource_mapper
   *   The metastore resource mapper service.
   * @param \Drupal\datastore\Service\ResourceProcessorCollector $processor_collector
   *   The resource processor collector service.
   * @param \Drupal\datastore\Service\PostImport $post_import
   *   The post import service.
   * @param \Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface $data_dictionary_discovery
   *   The data-dictionary discovery service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    AlterTableQueryBuilderInterface $alter_table_query_builder,
    LoggerChannelFactoryInterface $logger_factory,
    ResourceMapper $resource_mapper,
    ResourceProcessorCollector $processor_collector,
    PostImport $post_import,
    DataDictionaryDiscoveryInterface $data_dictionary_discovery
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger_factory->get('datastore');
    $this->resourceMapper = $resource_mapper;
    $this->resourceProcessorCollector = $processor_collector;
    $this->postImport = $post_import;
    $this->dataDictionaryDiscovery = $data_dictionary_discovery;
    // Set the timeout for database connections to the queue lease time.
    // This ensures that database connections will remain open for the
    // duration of the time the queue is being processed.
    $timeout = (int) $plugin_definition['cron']['lease_time'];
    $alter_table_query_builder->setConnectionTimeout($timeout);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('dkan.datastore.data_dictionary.alter_table_query_builder.mysql'),
      $container->get('logger.factory'),
      $container->get('dkan.metastore.resource_mapper'),
      $container->get('dkan.datastore.service.resource_processor_collector'),
      $container->get('dkan.datastore.service.post_import'),
      $container->get('dkan.metastore.data_dictionary_discovery'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $postImportResult = $this->postImportProcessItem($data);
    // Store the results of the PostImportResult object.
    $postImportResult->storeResult();
  }

  /**
   * Pass along new resource to resource processors.
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
      }
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
      $postImportResult = $this->createPostImportResult('error', $e->getMessage(), $resource);
    }

    return $postImportResult;
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
