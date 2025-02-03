<?php

namespace Drupal\Tests\common\Traits;

use Drupal\node\Entity\Node;

/**
 * @deprecated Will be removed in a future version of DKAN.
 */
trait CleanUp {

  /**
   *
   */
  private function removeHarvests() {
    /** @var \Drupal\harvest\HarvestService $service */
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
    /** @var \Drupal\metastore\Storage\ResourceMapperDatabaseTable $filemappertable */
    $filemappertable = \Drupal::service('dkan.metastore.resource_mapper_database_table');
    foreach ($filemappertable->retrieveAll() as $id) {
      $filemappertable->remove($id);
    }
  }

  /**
   *
   */
  private function removeAllFileFetchingJobs() {
    /** @var \Drupal\common\Storage\FileFetcherJobStoreFactory $jobStoreFactory */
    $jobStoreFactory = \Drupal::service('dkan.common.filefetcher_job_store_factory');

    $fileFetcherJob = $jobStoreFactory->getInstance();
    foreach ($fileFetcherJob->retrieveAll() as $id) {
      $fileFetcherJob->remove($id);
    }
  }

  /**
   *
   */
  private function flushQueues() {
    $dkanQueues = [
      'localizer_import',
      'datastore_import',
      'orphan_reference_processor',
      'resource_purger',
    ];
    foreach ($dkanQueues as $queueName) {
      /** @var \Drupal\Core\Queue\QueueFactory $queueFactory */
      $queueFactory = \Drupal::service('queue');
      $queue = $queueFactory->get($queueName);
      $queue->deleteQueue();
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
