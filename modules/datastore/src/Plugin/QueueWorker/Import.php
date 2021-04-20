<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\common\LoggerTrait;
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

  private $container;

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new Import($configuration, $plugin_id, $plugin_definition, $container);
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
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   A dependency injection container.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ContainerInterface $container) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->container = $container;
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

      /** @var \Drupal\datastore\Service $datastore */
      $datastore = $this->container->get('dkan.datastore.service');

      $results = $datastore->import($identifier, FALSE, $version);

      $queued = FALSE;
      foreach ($results as $result) {
        $queued = $this->processResult($result, $data, $queued);
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
    return $this->container->get('queue')
      ->get($this->getPluginId())
      ->createItem($data);
  }

}
