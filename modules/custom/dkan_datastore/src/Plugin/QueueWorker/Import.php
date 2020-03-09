<?php

namespace Drupal\dkan_datastore\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Logger\RfcLogLevel;
use Procrastinator\Result;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes resource import.
 *
 * @QueueWorker(
 *   id = "dkan_datastore_import",
 *   title = @Translation("Queue to process datastore import"),
 *   cron = {"time" = 60}
 * )
 */
class Import extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use \Drupal\Core\Logger\LoggerChannelTrait;


  private $container;

  /**
   * Inherited.
   *
   * {@inheritDoc}
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

    try {
      /** @var \Drupal\dkan_datastore\Service $datastore */
      $datastore = $this->container->get('dkan_datastore.service');

      $results = $datastore->import($data['uuid']);

      foreach ($results as $result) {
        $this->processResult($result, $data);
      }
    }
    catch (\Exception $e) {
      $this->log(RfcLogLevel::ERROR,
        "Import for {$data['uuid']} returned an error: {$e->getMessage()}");
    }
  }

  /**
   * Private.
   */
  private function processResult(Result $result, $data) {
    $level = RfcLogLevel::INFO;
    $message = "";
    $status = $result->getStatus();
    switch ($status) {
      case Result::STOPPED:
        $newQueueItemId = $this->requeue($data);
        $message = "Import for {$data['uuid']} is requeueing for iteration No. {$data['queue_iteration']}. (ID:{$newQueueItemId}).";
        break;

      case Result::IN_PROGRESS:
      case Result::ERROR:
        $level = RfcLogLevel::ERROR;
        $message = "Import for {$data['uuid']} returned an error: {$result->getError()}";
        break;

      case Result::DONE:
        $message = "Import for {$data['uuid']} completed.";
        break;
    }
    $this->log($level, $message);
  }

  /**
   * Log a datastore event.
   */
  protected function log($level, $message, array $context = []) {
    $this->getLogger($this->getPluginId())
      ->log($level, $message, $context);
  }

  /**
   * Requeues the job with extra state information.
   *
   * @param array $data
   *   Queue data.
   *
   * @return mixed
   *   Queue ID or false if unsuccessfull.
   *
   * @todo: Clarify return value. Documentation suggests it should return ID.
   */
  protected function requeue(array $data) {
    return $this->container->get('queue')
      ->get($this->getPluginId())
      ->createItem($data);
  }

}
