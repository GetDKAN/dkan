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
   * Perform all the tasks to bring a jobstore table into the present.
   *
   * @command dkan:jobstore-fixer
   */
  public function jobstoreFixer() {
    $this->renameDeprecatedTables();
    $this->mergeDuplicateTables();
  }

  /**
   * Rename deprecated jobstore tables to have non-deprecated names.
   *
   * @see \Drupal\common\Util\JobStoreUtil::renameDeprecatedJobstoreTables()
   */
  protected function renameDeprecatedTables() {
    $job_store_util = new JobStoreUtil($this->connection);
    // Rename deprecated tables.
    if ($renamed = $job_store_util->renameDeprecatedJobstoreTables()) {
      $this->writeln('RENAMED the following JobStore tables:');
      $this->io()->table(
        ['Deprecated', 'Current'],
        $job_store_util->keyedToList($renamed)
      );
    }
    else {
      $this->writeln('No tables renamed.');
    }
  }

  /**
   * Merge deprecated jobstore tables into non-deprecated ones.
   *
   * @see \Drupal\common\Util\JobStoreUtil::reconcileDuplicateJobstoreTable()
   */
  protected function mergeDuplicateTables() {
    $job_store_util = new JobStoreUtil($this->connection);
    // Merge duplicate deprecated tables.
    if ($result = $job_store_util->reconcileDuplicateJobstoreTables()) {
      $this->writeln('MERGED the following JobStore tables:');
      $this->io()->table(
        ['Deprecated', 'Merged Into'],
        $job_store_util->keyedToList($result)
      );
    }
    else {
      $this->writeln('No tables merged.');
    }
  }

}
