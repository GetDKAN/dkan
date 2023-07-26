<?php

namespace Drupal\common\Commands;

use Drupal\common\Util\JobStoreUtil;
use Drupal\Core\Database\Connection;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Style\SymfonyStyle;

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
      $this->convertToTable($this->io(), ['Deprecated', 'Current'], $renamed);
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
      $this->convertToTable($this->io(), ['Deprecated', 'Merged Into'], $result);
    }
    else {
      $this->writeln('No tables merged.');
    }
  }

  /**
   * Given key=>data array, display as table.
   *
   * @param \Symfony\Component\Console\Style\SymfonyStyle $io
   *   Style object.
   * @param $headers
   *   Headers, two values needed, first for key, second for value.
   * @param $data
   *   Key=>value array.
   */
  protected function convertToTable(SymfonyStyle $io, $headers, $data) {
    $display = [];
    foreach ($data as $key => $value) {
      $display[] = [$key, $value];
    }
    $io->table($headers, $display);
  }

}
