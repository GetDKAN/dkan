<?php

namespace Drupal\dkan_datastore\Plugin\QueueWorker;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\dkan_common\Storage\DatabaseConnectionFactoryInterface;
use Drupal\dkan_datastore\DatastoreService;
use Drupal\dkan_metastore\Reference\ReferenceLookup;

/**
 * Processes resource import.
 *
 * @deprecated
 * @see \Drupal\dkan_datastore\Plugin\QueueWorker\ImportQueueWorker
 */
class Import extends ImportQueueWorker {

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   A config factory instance.
   * @param \Drupal\dkan_datastore\DatastoreService $datastore
   *   A DKAN datastore service instance.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   A logger channel factory instance.
   * @param \Drupal\dkan_metastore\Reference\ReferenceLookup $referenceLookup
   *   The reference lookup service.
   * @param \Drupal\dkan_common\Storage\DatabaseConnectionFactoryInterface $defaultConnectionFactory
   *   Default database connection factory.
   * @param \Drupal\dkan_common\Storage\DatabaseConnectionFactoryInterface $datastoreConnectionFactory
   *   Datastore database connection factory.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $configFactory,
    DatastoreService $datastore,
    LoggerChannelFactoryInterface $loggerFactory,
    ReferenceLookup $referenceLookup,
    DatabaseConnectionFactoryInterface $defaultConnectionFactory,
    DatabaseConnectionFactoryInterface $datastoreConnectionFactory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $configFactory, $datastore, $loggerFactory, $referenceLookup, $defaultConnectionFactory, $datastoreConnectionFactory);
    @trigger_error(__NAMESPACE__ . '\Import is deprecated. Use \Drupal\dkan_datastore\Plugin\QueueWorker\ImportQueueWorker instead.', E_USER_DEPRECATED);
  }

}
