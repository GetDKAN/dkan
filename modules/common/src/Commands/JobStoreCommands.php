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

  protected function renameDeprecatedTables() {
    $job_store_util = new JobStoreUtil($this->connection);
    // Rename deprecated tables.
    if ($renamed = $job_store_util->renameDeprecatedJobstoreTables()) {
      $this->writeln('RENAMED the following JobStore tables:');
      $display = [];
      foreach ($renamed as $deprecated => $current) {
        $display[] = [$deprecated, $current];
      }
      $this->io()->table(['Deprecated', 'Current'], $display);
    }
    else {
      $this->writeln('No tables renamed.');
    }
  }

  protected function mergeDuplicateTables() {
    $job_store_util = new JobStoreUtil($this->connection);
    // Merge duplicate deprecated tables.
    if ($result = $job_store_util->reconcileDuplicateJobstoreTables()) {
      $this->writeln('MERGED the following JobStore tables:');
      $display = [];
      foreach ($result as $deprecated => $current) {
        $display[] = [$deprecated, $current];
      }
      $this->io()->table(['Deprecated', 'Merged Into'], $display);
    }
    else {
      $this->writeln('No tables merged.');
    }
  }

}
