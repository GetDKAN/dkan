<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueWorkerBase;

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
   * Inherited.
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('dkan.datastore.service'),
      $container->get('logger.factory'),
      $container->get('queue')
    );
  }

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\datastore\Service $datastore
   *   A DKAN datastore service instance.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $loggerFactory
   *   A logger channel factory instance.
   * @param \Drupal\Core\Queue\QueueFactory $queueFactory
   *   A database queue factory instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, DatastoreService $datastore, LoggerChannelFactory $loggerFactory, QueueFactory $queueFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->databaseQueue = $queueFactory->get($plugin_id);
    $this->datastore = $datastore;
    $this->setLoggerFactory($loggerFactory);
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
        if (isset($result)) {
          $queued = $this->processResult($result, $data, $queued);
        }
      }
    }
    catch (\Exception $e) {
      $this->log(RfcLogLevel::ERROR,
        "Import for {$data['identifier']} returned an error: {$e->getMessage()}");
    }
  }

  /**
   * Private.
   */
  private function processResult(Result $result, $data, $queued = FALSE) {
    $identifier = $data['identifier'];
    $version = $data['version'];
    $uid = "{$identifier}__{$version}";

    $level = RfcLogLevel::INFO;
    $message = "";
    $status = $result->getStatus();
    switch ($status) {
      case Result::STOPPED:
        if (!$queued) {
          $newQueueItemId = $this->requeue($data);
          $message = "Import for {$uid} is requeueing. (ID:{$newQueueItemId}).";
          $queued = TRUE;
        }
        break;

      case Result::IN_PROGRESS:
      case Result::ERROR:
        $level = RfcLogLevel::ERROR;
        $message = "Import for {$uid} returned an error: {$result->getError()}";
        break;

      case Result::DONE:
        $message = "Import for {$uid} completed.";
        break;
    }
    $this->log('dkan', $message, [], $level);
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
