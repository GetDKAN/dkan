<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;

use Drupal\common\LoggerTrait;
use Drupal\common\Storage\DatabaseConnectionFactoryInterface;
use Drupal\datastore\DatastoreService;
use Drupal\metastore\Reference\ReferenceLookup;

use Procrastinator\Result;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes resource import.
 *
 * @deprecated
 * @see \Drupal\datastore\Plugin\QueueWorker\ImportQueueWorker
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
   * @param \Drupal\datastore\DatastoreService $datastore
   *   A DKAN datastore service instance.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   A logger channel factory instance.
   * @param \Drupal\metastore\Reference\ReferenceLookup $referenceLookup
   *   The reference lookup service.
   * @param \Drupal\common\Storage\DatabaseConnectionFactoryInterface $defaultConnectionFactory
   *   Default database connection factory.
   * @param \Drupal\common\Storage\DatabaseConnectionFactoryInterface $datastoreConnectionFactory
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
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    @trigger_error(__NAMESPACE__ . '\Import is deprecated. Use \Drupal\datastore\Plugin\QueueWorker\ImportQueueWorker instead.', E_USER_DEPRECATED);
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
      $container->get('dkan.datastore.service'),
      $container->get('logger.factory'),
      $container->get('dkan.metastore.reference_lookup'),
      $container->get('dkan.common.database_connection_factory'),
      $container->get('dkan.datastore.database_connection_factory')
    );
  }

}
