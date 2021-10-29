<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Utility\Error;

use Drupal\common\LoggerTrait;
use Drupal\datastore\Service as DatastoreService;

use Procrastinator\Result;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes resource import.
 *
 * @QueueWorker(
 *   id = "datastore_import",
 *   title = @Translation("Queue to process datastore import"),
 *   cron = {"time" = 60}
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
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A config factory instance.
   * @param \Drupal\datastore\Service $datastore
   *   A DKAN datastore service instance.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   A file system service instance.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   A logger channel factory instance.
   * @param \Drupal\Core\Queue\QueueFactory $queue_factory
   *   A database queue factory instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, DatastoreService $datastore, FileSystemInterface $file_system, LoggerChannelFactoryInterface $logger_factory, QueueFactory $queue_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->datastoreConfig = $config_factory->get('datastore.settings');
    $this->databaseQueue = $queue_factory->get($plugin_id);
    $this->datastore = $datastore;
    $this->fileSystem = $file_system;
    $this->setLoggerFactory($logger_factory, 'datastore');
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
      $container->get('file_system'),
      $container->get('logger.factory'),
      $container->get('queue')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    if (is_object($data) && isset($data->data)) {
      $data = $data->data;
    }

    try {
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
    catch (\Exception $e) {
      $error = Error::renderExceptionSafe($e);
      $this->error("Import for {$data['identifier']} returned an error: {$error}");
    }
  }

  /**
   * Private.
   */
  private function processResult(Result $result, $data, $queued = FALSE) {
    $identifier = $data['identifier'];
    $version = $data['version'];
    $uid = "{$identifier}__{$version}";

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
   * @todo: Clarify return value. Documentation suggests it should return ID.
   */
  protected function requeue(array $data) {
    return $this->databaseQueue->createItem($data);
  }

}
