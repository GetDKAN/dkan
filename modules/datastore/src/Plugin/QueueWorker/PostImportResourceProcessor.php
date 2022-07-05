<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;

use Drupal\common\Resource;
use Drupal\datastore\DataDictionary\AlterTableQueryFactoryInterface;
use Drupal\datastore\Service\ResourceProcessorCollector;
use Drupal\metastore\ResourceMapper;

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
   * Build queue worker.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\datastore\DataDictionary\AlterTableQueryFactoryInterface $alter_table_query_factory
   *   The alter table query factory service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   A logger channel factory instance.
   * @param \Drupal\metastore\ResourceMapper $resource_mapper
   *   The metastore resource mapper service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    AlterTableQueryFactoryInterface $alter_table_query_factory,
    LoggerChannelFactoryInterface $logger_factory,
    ResourceMapper $resource_mapper,
    ResourceProcessorCollector $processor_collector
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger_factory->get('datastore');
    $this->resourceMapper = $resource_mapper;
    $this->resourceProcessorCollector = $processor_collector;
    // Set the timeout for database connections to the queue lease time.
    // This ensures that database connections will remain open for the
    // duration of the time the queue is being processed.
    $timeout = (int) $plugin_definition['cron']['lease_time'];
    $alter_table_query_factory->setConnectionTimeout($timeout);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('dkan.datastore.data_dictionary.alter_table_query_factory.mysql'),
      $container->get('logger.factory'),
      $container->get('dkan.metastore.resource_mapper'),
      $container->get('dkan.datastore.service.resource_processor_collector'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Catch and log any exceptions thrown when processing the queue item to
    // prevent the item from being requeued.
    try {
      $this->doProcessItem($data);
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }
  }

  /**
   * Pass along new resource to resource processors.
   *
   * @param \Drupal\common\Resource $resource
   *   DKAN Resource.
   */
  public function doProcessItem(Resource $resource): void {
    $identifier = $resource->getIdentifier();
    $version = $resource->getVersion();

    $latest_resource = $this->resourceMapper->get($identifier);
    // Stop if resource no longer exists.
    if (!isset($latest_resource)) {
      $this->logger->notice('Cancelling resource processing; resource no longer exists.');
      return;
    }
    // Stop if resource has changed.
    if ($version !== $latest_resource->getVersion()) {
      $this->logger->notice('Cancelling resource processing; resource has changed.');
      return;
    }
    // Run all tagged resource processors.
    $processors = $this->resourceProcessorCollector->getResourceProcessors();
    array_map(fn ($processor) => $processor->process($resource), $processors);
  }

}
