<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\common\Storage\DatabaseConnectionFactoryInterface;
use Drupal\common\Storage\ImportedItemInterface;
use Drupal\datastore\DatastoreService;
use Procrastinator\Result;
use Psr\Log\LoggerInterface;
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
class ImportQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * This queue worker's corresponding database queue instance.
   *
   * @var \Drupal\Core\Queue\DatabaseQueue
   */
  protected $databaseQueue;

  /**
   * DKAN datastore service instance.
   *
   * @var \Drupal\datastore\DatastoreService
   */
  protected $datastore;

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
   * Logger service.
   */
  protected LoggerInterface $logger;

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
   * @param \Psr\Log\LoggerInterface $loggerChannel
   *   A logger channel factory instance.
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
    LoggerInterface $loggerChannel,
    DatabaseConnectionFactoryInterface $defaultConnectionFactory,
    DatabaseConnectionFactoryInterface $datastoreConnectionFactory
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->datastore = $datastore;
    $this->datastoreConfig = $configFactory->get('datastore.settings');
    $this->databaseQueue = $datastore->getQueueFactory()->get($plugin_id);
    $this->fileSystem = $datastore->getResourceLocalizer()->getFileSystem();
    $this->logger = $loggerChannel;
    // Set the timeout for database connections to the queue lease time.
    // This ensures that database connections will remain open for the
    // duration of the time the queue is being processed.
    $timeout = (int) $plugin_definition['cron']['lease_time'];
    $defaultConnectionFactory->setConnectionTimeout($timeout);
    $datastoreConnectionFactory->setConnectionTimeout($timeout);
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
      $container->get('dkan.datastore.logger_channel'),
      $container->get('dkan.common.database_connection_factory'),
      $container->get('dkan.datastore.database_connection_factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    if (is_object($data) && isset($data->data)) {
      $data = $data->data;
    }

    // Can we short-circuit this task?
    if ($this->alreadyImported($data)) {
      $this->logger->notice('Dataset with identifier ' . $data['identifier'] . ' and version ' . $data['version'] . ' already exists and will not be re-imported.');
      return;
    }

    try {
      $this->importData($data);
    }
    catch (\Exception $e) {
      $this->logger->error('Import for ' . $data['identifier'] . ' returned an error: ' . $e->getMessage());
    }
  }

  /**
   * Determine whether the import has already occurred.
   *
   * This situation occurs when long processes have successfully occurred, but
   * databases or file transfers have timed out. In this case no more effort is
   * required, so the queue item should exit.
   *
   * @param mixed $data
   *   Data provided by queue system.
   *
   * @return bool
   *   TRUE if no more effort is required. FALSE otherwise.
   *
   * @todo Add more status logic as needed.
   */
  protected function alreadyImported(mixed $data): bool {
    try {
      $storage = $this->datastore->getStorage(
        $data['identifier'] ?? FALSE,
        $data['version'] ?? FALSE
      );
      if ($storage instanceof ImportedItemInterface) {
        return $storage->hasBeenImported();
      }
    }
    catch (\InvalidArgumentException) {
      // DatastoreService->getStorage() throws \InvalidArgumentException if no
      // storage could be found. That helpfully answers our question of whether
      // the storage has already been imported.
    }
    return FALSE;
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
    foreach ($results as $label => $result) {
      $queued = isset($result) && $this->processResult($result, $data, $queued, $label);
    }

    // Delete local resource file if enabled in datastore settings config.
    if ($this->datastoreConfig->get('delete_local_resource')) {
      $this->fileSystem->deleteRecursive('public://resources/' . $identifier . '_' . $version);
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
   * @param string $label
   *   A label to distinguish types of jobs in status messages.
   *
   * @return bool
   *   The updated value for $queued.
   */
  protected function processResult(Result $result, mixed $data, bool $queued = FALSE, string $label = 'Import') {
    $uid = $data['identifier'] . '__' . $data['version'];
    $status = $result->getStatus();
    switch ($status) {
      case Result::STOPPED:
        if (!$queued) {
          $newQueueItemId = $this->requeue($data);
          $this->logger->notice($label . ' for ' . $uid . ' is requeueing. (ID:' . $newQueueItemId . ').');
          $queued = TRUE;
        }
        break;

      case Result::IN_PROGRESS:
      case Result::ERROR:
        $this->logger->error($label . ' for ' . $uid . ' returned an error: ' . $result->getError());
        break;

      case Result::DONE:
        $this->logger->notice($label . ' for ' . $uid . ' completed.');
        break;
    }

    return $queued;
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
