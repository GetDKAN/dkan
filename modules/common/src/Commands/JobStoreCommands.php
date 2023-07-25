<?php

namespace Drupal\common\Commands;

use Drupal\common\Util\JobStoreUtil;
use Drupal\Core\Database\Connection;
use Drush\Commands\DrushCommands;

/**
 * JobStore-related commands.
 *
 * @codeCoverageIgnore
 */
class JobStoreCommands extends DrushCommands {

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $connection;

  /**
   * JobStoreCommands constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   */
  public function __construct(Connection $connection) {
    parent::__construct();
    $this->connection = $connection;
  }

  /**
   * Rename jobstore tables to use non-deprecated table names.
   *
   * @usage dkan:common:fix-deprecated-jobstore
   *
   * @command dkan:common:fix-deprecated-jobstore
   */
  public function fixDeprecatedJobstoreTables() {
    $job_store_util = new JobStoreUtil($this->connection);
    if ($renamed = $job_store_util->renameDeprecatedJobstoreTables()) {
      $this->writeln('Renamed the following JobStore tables:');
      $display = [];
      foreach ($renamed as $deprecated => $current) {
        $display[] = [$deprecated, $current];
      }
      $this->io()->table(['Deprecated', 'Current'], $display);
    }
    else {
      $this->writeln('No tables changed.');
    }
  }

  /**
   * Reconcile duplicate tables.
   *
   * @usage dkan:common:reconcile-jobstore
   *
   * @command dkan:common:reconcile-jobstore
   */
  public function reconcileDuplicateJobstoreTables() {
//    $class_name = 'FileFetcher\FileFetcher';
    $class_name = 'Drupal\datastore\Plugin\QueueWorker\ImportJob';
    $job_store_util = new JobStoreUtil($this->connection);

    $result = $job_store_util->reconcileDuplicateJobstoreTable($class_name);


//    if ($duplicate = $job_store_util->getDuplicateJobstoreTables()) {
//      $display = [];
//      foreach ($duplicate as $deprecated => $current) {
//        $display[] = [$deprecated, $current];
//      }
//      $this->io()->table(['Deprecated', 'Current'], $display);
//    }
//    else {
//      $this->writeln('No tables changed.');
//    }
  }

}
