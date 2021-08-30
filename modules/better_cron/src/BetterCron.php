<?php

namespace Drupal\better_cron;

use Drupal\Core\Cron;
use Drupal\Core\CronInterface;
use Drupal\Core\Queue\DelayableQueueInterface;
use Drupal\Core\Queue\DelayedRequeueException;
use Drupal\Core\Queue\RequeueException;
use Drupal\Core\Queue\SuspendQueueException;

/**
 * Cron service which allows for a separate queue time-limit and lease time.
 */
class BetterCron extends Cron implements CronInterface {

  /**
   * Default time-limit for a queue.
   *
   * @var string
   */
  protected const DEFAULT_QUEUE_CRON_TIME = 15;

  /**
   * Default lease time for a queue item.
   *
   * @var string
   */
  protected const DEFAULT_QUEUE_CRON_LEASE_TIME = 30;

  /**
   * {@inheritdoc}
   */
  protected function processQueues(): void {
    // Grab the defined cron queues.
    foreach ($this->queueManager->getDefinitions() as $queue_name => $info) {
      // Ensure the queue is annotated such as to be processed by cron.
      if (isset($info['cron'])) {
        // Make sure every queue exists. There is no harm in trying to recreate
        // an existing queue.
        $this->queueFactory->get($queue_name)->createQueue();

        // Fetch queue and queue worker for processing this queue instance.
        $queue = $this->queueFactory->get($queue_name);
        $queue_worker = $this->queueManager->createInstance($queue_name);

        // Calculate the end time based on the time-limit for this queue.
        $time_limit = $this->time->getCurrentTime() + ($info['cron']['time'] ?? static::DEFAULT_QUEUE_CRON_TIME);
        // Fetch the maximum lease time for items in this queue.
        $lease_time = $info['cron']['lease_time'] ?? static::DEFAULT_QUEUE_CRON_LEASE_TIME;

        // While the time limit has not been reached for this queue, and there
        // are still remaining queue items to be processed...
        while ($this->time->getCurrentTime() < $time_limit && ($item = $queue->claimItem($lease_time))) {
          try {
            $queue_worker->processItem($item->data);
            $queue->deleteItem($item);
          }
          catch (DelayedRequeueException $e) {
            // The worker requested the task not be immediately re-queued.
            // - If the queue doesn't support ::delayItem(), we should leave the
            // item's current expiry time alone.
            // - If the queue does support ::delayItem(), we should allow the
            // queue to update the item's expiry using the requested delay.
            if ($queue instanceof DelayableQueueInterface) {
              // This queue can handle a custom delay; use the duration provided
              // by the exception.
              $queue->delayItem($item, $e->getDelay());
            }
          }
          catch (RequeueException $e) {
            // The worker requested the task be immediately requeued.
            $queue->releaseItem($item);
          }
          catch (SuspendQueueException $e) {
            // If the worker indicates there is a problem with the whole queue,
            // release the item and skip to the next queue.
            $queue->releaseItem($item);

            watchdog_exception('cron', $e);

            // Skip to the next queue.
            continue 2;
          }
          catch (\Exception $e) {
            // In case of any other kind of exception, log it and leave the item
            // in the queue to be processed again later.
            watchdog_exception('cron', $e);
          }
        }
      }
    }
  }
}
