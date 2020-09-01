<?php

namespace Drupal\Tests\common\Traits;

use Drupal\node\Entity\Node;
use FileFetcher\FileFetcher;

trait CleanUp {

  private function removeAllNodes() {
    $nodes = Node::loadMultiple();
    foreach ($nodes as $node) {
      $node->delete();
    }
  }

  private function removeAllMappedFiles() {
    /* @var $filemappertable \Drupal\metastore\Storage\ResourceMapperDatabaseTable */
    $filemappertable = \Drupal::service('dkan.metastore.resource_mapper_database_table');
    foreach ($filemappertable->retrieveAll() as $id) {
      $filemappertable->remove($id);
    }
  }

  private function removeAllFileFetchingJobs() {
    /* @var $jobStoreFactory \Drupal\common\Storage\JobStoreFactory */
    $jobStoreFactory = \Drupal::service('dkan.common.job_store');

    /* @var $jobStore \Drupal\common\Storage\JobStore */
    $jobStore = $jobStoreFactory->getInstance(FileFetcher::class);
    foreach ($jobStore->retrieveAll() as $id) {
      $jobStore->remove($id);
    }
  }

  private function flushQueues() {
    $dkanQueues = ['orphan_reference_processor', 'datastore_import'];
    foreach ($dkanQueues as $queueName) {
      /* @var $queueFactory \Drupal\Core\Queue\QueueFactory */
      $queueFactory = \Drupal::service('queue');
      $queue = $queueFactory->get($queueName);
      $queue->deleteQueue();
    }
  }

  private function removeFiles() {

    $dirs = ['dkan-tmp', 'distribution', 'resources'];
    foreach ($dirs as $dir) {
      $path = DRUPAL_ROOT . "/sites/default/files/{$dir}";
      if (file_exists($path)) {
        `rm -rf {$path}`;
      }
    }
  }

  private function removeDatastoreTables() {
    /* @var $connection \Drupal\Core\Database\Connection */
    $connection = \Drupal::service('database');
    $tables = $connection->schema()->findTables("datastore_%");
    foreach($tables as $table) {
      $connection->schema()->dropTable($table);
    }
  }
}
