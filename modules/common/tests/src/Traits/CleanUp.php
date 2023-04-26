<?php

namespace Drupal\Tests\common\Traits;

use Drupal\node\Entity\Node;
use FileFetcher\FileFetcher;

/**
 *
 */
trait CleanUp {

  /**
   *
   */
  private function removeHarvests() {
    /** @var \Drupal\harvest\Service $service */
    $service = \Drupal::service('dkan.harvest.service');
    foreach ($service->getAllHarvestIds() as $id) {
      $service->deregisterHarvest($id);
    }
  }

  /**
   *
   */
  private function removeAllNodes() {
    $nodes = Node::loadMultiple();
    foreach ($nodes as $node) {
      $node->delete();
    }
  }

  /**
   *
   */
  private function removeAllMappedFiles() {
    /** @var \Drupal\metastore\Storage\ResourceMapperDatabaseTable $file_mapper_table */
    $file_mapper_table = \Drupal::service('dkan.metastore.resource_mapper_database_table');
    foreach ($file_mapper_table->retrieveAll() as $id) {
      $file_mapper_table->remove($id);
    }
  }

  /**
   *
   */
  private function removeAllFileFetchingJobs() {
    /** @var \Drupal\common\Storage\JobStoreFactory $jobStoreFactory */
    $jobStoreFactory = \Drupal::service('dkan.common.job_store');

    /** @var \Drupal\common\Storage\JobStore $jobStore */
    $jobStore = $jobStoreFactory->getInstance(FileFetcher::class);
    foreach ($jobStore->retrieveAll() as $id) {
      $jobStore->remove($id);
    }
  }

  /**
   *
   */
  private function flushQueues() {
    $dkanQueues = ['orphan_reference_processor', 'datastore_import', 'resource_purger'];
    /** @var \Drupal\Core\Queue\QueueFactory $queueFactory */
    $queueFactory = \Drupal::service('queue');
    foreach ($dkanQueues as $queueName) {
      $queue = $queueFactory->get($queueName);
      $queue->deleteQueue();
    }
  }

  /**
   * Process the supplied queue list.
   *
   * @param string[] $relevant_queues
   *   A list of queues to process. Defaults to reasonable DKAN list.
   */
  protected function processQueues(array $relevant_queues = ['orphan_reference_processor', 'datastore_import', 'resource_purger']): void {
    /** @var \Drupal\Core\Queue\QueueFactory $queue_factory */
    $queue_factory = \Drupal::service('queue');
    $queue_worker_manager = \Drupal::service('plugin.manager.queue_worker');
    foreach ($relevant_queues as $queue_name) {
      $worker = $queue_worker_manager->createInstance($queue_name);
      $queue = $queue_factory->get($queue_name);
      while ($item = $queue->claimItem()) {
        $worker->processItem($item->data);
        $queue->deleteItem($item);
      }
    }
  }

  /**
   *
   */
  private function removeFiles() {

    $dirs = ['dkan-tmp', 'distribution', 'resources'];
    foreach ($dirs as $dir) {
      $path = DRUPAL_ROOT . "/sites/default/files/{$dir}";
      if (file_exists($path)) {
        `rm -rf {$path}`;
      }
    }
  }

  /**
   *
   */
  private function removeDatastoreTables() {
    /** @var \Drupal\Core\Database\Connection $connection */
    $connection = \Drupal::service('database');
    $tables = $connection->schema()->findTables("datastore_%");
    foreach ($tables as $table) {
      $connection->schema()->dropTable($table);
    }
  }

}
