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

}
