<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Database\Connection;

use Drupal\common\LoggerTrait;
use Drupal\datastore\Service as DatastoreService;
use Drupal\metastore\Reference\ReferenceLookup;
use Procrastinator\Result;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes resource import.
 *
 * @QueueWorker(
 *   id = "datastore_import",
 *   title = @Translation("Queue to process datastore import"),
 *   cron = {
 *     "time" = 180,
 *     "lease_time" = 10800
 *   }
 * )
 */
class Import extends QueueWorkerBase implements ContainerFactoryPluginInterface {
  use LoggerTrait;

  /**
   * This queue worker's corresponding database queue instance.
   *
   * @var \Drupal\Core\Queue\DatabaseQueue
   */
  protected $databaseQueue;

  /**
   * DKAN datastore service instance.
   *
   * @var \Drupal\datastore\Service
   */
  protected $datastore;

  /**
   * Reference lookup service.
   *
   * @var \Drupal\metastore\Reference\ReferenceLookup
   */
  protected $referenceLookup;

  /**
   * Datastore config settings.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $datastoreConfig;

  /**
   * File system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

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
   * @param \Drupal\datastore\Service $datastore
   *   A DKAN datastore service instance.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   A logger channel factory instance.
   * @param \Drupal\metastore\Reference\ReferenceLookup $referenceLookup
   *   The reference lookup service.
   * @param \Drupal\Core\Database\Connection $defaultConnection
   *   An instance of the default database connection.
   * @param \Drupal\Core\Database\Connection $datastoreConnection
   *   An instance of the datastore database connection.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    ConfigFactoryInterface $configFactory,
    DatastoreService $datastore,
    LoggerChannelFactoryInterface $loggerFactory,
    ReferenceLookup $referenceLookup,
    Connection $defaultConnection,
    Connection $datastoreConnection
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->datastore = $datastore;
    $this->referenceLookup = $referenceLookup;
    $this->datastoreConfig = $configFactory->get('datastore.settings');
    $this->databaseQueue = $datastore->getQueueFactory()->get($plugin_id);
    $this->fileSystem = $datastore->getResourceLocalizer()->getFileSystem();
    $this->setLoggerFactory($loggerFactory, 'datastore');
    // Set the timeout for database connections to the queue lease time.
    // This ensures that database connections will remain open for the
    // duration of the time the queue is being processed.
    $timeout = (int) $plugin_definition['cron']['lease_time'];
    $this->setConnectionTimeout($datastoreConnection, $timeout);
    $this->setConnectionTimeout($defaultConnection, $timeout);
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
      $container->get('database'),
      $container->get('dkan.datastore.database')
    );
  }

  /**
   * Set the wait_timeout for the given database connection.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection instance.
   * @param int $timeout
   *   Wait timeout in seconds.
   */
  protected function setConnectionTimeout(Connection $connection, int $timeout): void {
    $command = 'SET SESSION wait_timeout = ' . $timeout;
    $connection->query($command);
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    if (is_object($data) && isset($data->data)) {
      $data = $data->data;
    }

    try {
      $this->importData($data);
    }
    catch (\Exception $e) {
      $this->error("Import for {$data['identifier']} returned an error: {$e->getMessage()}");
    }
  }

  /**
   * Perform the actual data import.
   *
   * @param array $data
   *   Resource identifier information.
   */
  protected function importData(array $data) {
    $identifier = $data['identifier'];
    $version = $data['version'];
    $results = $this->datastore->import($identifier, FALSE, $version);

    $queued = FALSE;
    foreach ($results as $result) {
      $queued = isset($result) ? $this->processResult($result, $data, $queued) : FALSE;
    }

    // Delete local resource file if enabled in datastore settings config.
    if ($this->datastoreConfig->get('delete_local_resource')) {
      $this->fileSystem->deleteRecursive("public://resources/{$identifier}_{$version}");
    }
  }

  /**
   * Process the result of the import operation.
   *
   * @param \Procrastinator\Result $result
   *   The result object.
   * @param mixed $data
   *   The resource data for import.
   * @param bool $queued
   *   Whether the import job is currently queued.
   *
   * @return bool
   *   The updated value for $queued.
   */
  protected function processResult(Result $result, $data, $queued = FALSE) {
    $uid = "{$data['identifier']}__{$data['version']}";
    $status = $result->getStatus();
    switch ($status) {
      case Result::STOPPED:
        if (!$queued) {
          $newQueueItemId = $this->requeue($data);
          $this->notice("Import for {$uid} is requeueing. (ID:{$newQueueItemId}).");
          $queued = TRUE;
        }
        break;

      case Result::IN_PROGRESS:
      case Result::ERROR:
        $this->error("Import for {$uid} returned an error: {$result->getError()}");
        break;

      case Result::DONE:
        $this->notice("Import for {$uid} completed.");
        $this->invalidateCacheTags("{$uid}__source");
        break;
    }

    return $queued;
  }

  /**
   * Invalidate all appropriate cache tags for this resource.
   *
   * @param mixed $resourceId
   *   A resource ID.
   */
  protected function invalidateCacheTags($resourceId) {
    $this->referenceLookup->invalidateReferencerCacheTags('distribution', $resourceId, 'downloadURL');
  }

  /**
   * Requeues the job with extra state information.
   *
   * @param array $data
   *   Queue data.
   *
   * @return mixed
   *   Queue ID or false if unsuccessful.
   *
   * @todo Clarify return value. Documentation suggests it should return ID.
   */
  protected function requeue(array $data) {
    return $this->databaseQueue->createItem($data);
  }

}
