<?php

namespace Drupal\common\Commands;

use Drupal\common\Util\JobStoreUtil;
use Drupal\Core\Database\Connection;
use Drush\Commands\DrushCommands;

/**
 * Drush commands providing utility common to DKAN's sub-modules.
 */
class JobStoreCommands extends DrushCommands {

  /**
   * Dataset information service.
   *
   * @var \Drupal\common\DatasetInfo
   */
  protected $datasetInfo;

  protected Connection $connection;

  /**
   * CommonCommands constructor.
   *
   * @param \Drupal\common\DatasetInfo $datasetInfo
   *   Dataset information service.
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
  public function renameDeprecatedJobstoreTables() {
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
