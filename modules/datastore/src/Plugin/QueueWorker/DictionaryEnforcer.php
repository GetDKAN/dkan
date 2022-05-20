<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;

use Drupal\common\Resource;
use Drupal\datastore\DataDictionary\AlterTableQueryFactoryInterface;
use Drupal\metastore\Service as MetastoreService;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;
use Drupal\metastore\ResourceMapper;

use RootedData\RootedJsonData;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Apply specified data-dictionary to datastore belonging to specified dataset.
 *
 * @QueueWorker(
 *   id = "dictionary_enforcer",
 *   title = @Translation("Alter datastore table schemas for datasets with data-dictionaries"),
 *   cron = {
 *     "time" = 180,
 *     "lease_time" = 10800
 *   }
 * )
 */
class DictionaryEnforcer extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Datastore table query service.
   *
   * @var \Drupal\datastore\DataDictionary\AlterTableQueryFactoryInterface
   */
  protected $alterTableQueryFactory;

  /**
   * Data dictionary discovery service.
   *
   * @var \Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface
   */
  protected $dataDictionaryDiscovery;

  /**
   * A logger channel for this plugin.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The metastore service.
   * 
   * @var \Drupal\metastore\Service
   */
  protected $metastore;

  /**
   * The metastore resource mapper service.
   * 
   * @var \Drupal\metastore\ResourceMapper
   */
  protected $resourceMapper;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
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
   * @param \Drupal\metastore\Service $metastore
   *   The metastore service.
   * @param \Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface $data_dictionary_discovery
   *   The data-dictionary discovery service.
   * @param \Drupal\metastore\ResourceMapper $resource_mapper
   *   The metastore resource mapper service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    AlterTableQueryFactoryInterface $alter_table_query_factory,
    LoggerChannelFactoryInterface $logger_factory,
    MetastoreService $metastore,
    DataDictionaryDiscoveryInterface $data_dictionary_discovery,
    ResourceMapper $resource_mapper
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger_factory->get('datastore');
    $this->metastore = $metastore;
    // Set the timeout for database connections to the queue lease time.
    // This ensures that database connections will remain open for the
    // duration of the time the queue is being processed.
    $timeout = (int) $plugin_definition['cron']['lease_time'];
    $this->alterTableQueryFactory = $alter_table_query_factory->setConnectionTimeout($timeout);
    $this->dataDictionaryDiscovery = $data_dictionary_discovery;
    $this->resourceMapper = $resource_mapper;
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
      $container->get('dkan.metastore.service'),
      $container->get('dkan.metastore.data_dictionary_discovery'),
      $container->get('dkan.metastore.resource_mapper')
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
   * Retrieve dictionary and datastore table details; apply dictionary to table.
   *
   * @param \Drupal\common\Resource $resource
   *   DKAN Resource.
   */
  public function doProcessItem(Resource $resource): void {
    $identifier = $resource->getIdentifier();
    $version = $resource->getVersion();

    $latest_resource = $this->resourceMapper->get($identifier);
    // Do not apply data-dictionary if resource no longer exists.
    if (!isset($latest_resource)) {
      $this->logger->notice('Cancelling data-dictionary enforcement; resource no longer exists.');
      return;
    }
    // Do not apply data-dictionary if resource has changed.
    if ($version !== $latest_resource->getVersion()) {
      $this->logger->notice('Cancelling data-dictionary enforcement; resource has changed.');
      return;
    }

    // Retrieve name of datastore table for resource.
    $datastore_table = $resource->getTableName();
    // Get data-dictionary for the given resource.
    $dictionary = $this->getDataDictionaryForResource($resource);
    // Extract data-dictionary field types.
    $dictionary_fields = $dictionary->{'$.data.fields'};

    $this->applyDictionary($dictionary_fields, $datastore_table);
  }

  /**
   * Retrieve the data-dictionary metadata object for the given resource.
   *
   * @param \Drupal\common\Resource $resource
   *   DKAN Resource.
   *
   * @return \RootedData\RootedJsonData
   *   Data-dictionary metadata.
   */
  protected function getDataDictionaryForResource(Resource $resource): RootedJsonData {
    $resource_id = $resource->getIdentifier();
    $resource_version = $resource->getVersion();
    $dict_id = $this->dataDictionaryDiscovery->dictionaryIdFromResource($resource_id, $resource_version);

    if (!isset($dict_id)) {
      throw new \UnexpectedValueException(sprintf('No data-dictionary found for resource with id "%s" and version "%s".', $resource_id, $resource_version));
    }
    return $this->metastore->get('data-dictionary', $dict_id);
  }

  /**
   * Apply data types in the given dictionary fields to the given datastore.
   *
   * @param array $dictionary_fields
   *   Data dictionary fields.
   * @param string $datastore_table
   *   Mysql table name.
   */
  public function applyDictionary(array $dictionary_fields, string $datastore_table): void {
    $this->alterTableQueryFactory
      ->getQuery($datastore_table, $dictionary_fields)
      ->applyDataTypes();
  }

}
