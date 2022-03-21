<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;

use Drupal\datastore\DataDictionary\AlterTableQueryFactoryInterface;
use Drupal\metastore\Service as MetastoreService;

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
   * A logger channel for this plugin.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Datastore table query service.
   *
   * @var \Drupal\datastore\DataDictionary\AlterTableQueryFactoryInterface
   */
  protected $alterTableQueryFactory;

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
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    AlterTableQueryFactoryInterface $alter_table_query_factory,
    LoggerChannelFactoryInterface $logger_factory,
    MetastoreService $metastore
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger_factory->get('datastore');
    $this->metastore = $metastore;
    // Set the timeout for database connections to the queue lease time.
    // This ensures that database connections will remain open for the
    // duration of the time the queue is being processed.
    $timeout = (int) $plugin_definition['cron']['lease_time'];
    $this->alterTableQueryFactory = $alter_table_query_factory->setConnectionTimeout($timeout);
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
      $container->get('dkan.metastore.service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $dictionary = $this->metastore->get('data-dictionary', $data->dictionary_identifier);
    $dictionary_fields = $dictionary->{'$.data.fields'};

    try {
      $this->applyDictionary($dictionary_fields, $data->datastore_table);
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
    }
  }

  /**
   * Apply data types in columns specified in `$dict` to `$dataset`.
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
