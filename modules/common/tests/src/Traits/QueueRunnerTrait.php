<?php

namespace Drupal\Tests\common\Traits;

use Drupal\Core\Queue\QueueFactoryInterface;
use Drupal\Core\Queue\QueueWorkerManagerInterface;

/**
 * Adds a predictable way to run queues in a specific order.
 */
trait QueueRunnerTrait {

  /**
   * Run queues in a predictable order.
   *
   * @param string[] $relevantQueues
   *   Names of queues to run, in order.
   * @param \Drupal\Core\Queue\QueueWorkerManagerInterface|null $queue_worker_manager
   *   (Optional) The plugin.manager.queue_worker service.
   * @param \Drupal\Core\Queue\QueueFactoryInterface|null $queue_factory
   *   (Optional) The queue service.
   */
  public function runQueues(
    array $relevantQueues = [],
    QueueWorkerManagerInterface $queue_worker_manager = NULL,
    QueueFactoryInterface $queue_factory = NULL
  ): void {
    if (empty($queue_worker_manager)) {
      $queue_worker_manager = \Drupal::service('plugin.manager.queue_worker');
    }
    if (empty($queue_factory)) {
      $queue_factory = \Drupal::service('queue');
    }
    foreach ($relevantQueues as $queueName) {
      $worker = $queue_worker_manager->createInstance($queueName);
      $queue = $queue_factory->get($queueName);
      while ($item = $queue->claimItem()) {
        $worker->processItem($item->data);
        $queue->deleteItem($item);
      }
    }
  }

}
