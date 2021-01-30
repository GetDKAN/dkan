<?php

namespace Drupal\common\Commands;

use Drupal\common\DatasetInfo;
use Drush\Commands\DrushCommands;

/**
 * Drush commands providing utility common to DKAN's sub-modules.
 */
class CommonCommands extends DrushCommands {

  /**
   * Dataset information service.
   *
   * @var \Drupal\common\DatasetInfo
   */
  protected $datasetInfo;

  /**
   * CommonCommands constructor.
   *
   * @param \Drupal\common\DatasetInfo $datasetInfo
   *   Dataset information service.
   */
  public function __construct(DatasetInfo $datasetInfo) {
    parent::__construct();
    $this->datasetInfo = $datasetInfo;
  }

  /**
   * Display information about a dataset and its resource(s).
   *
   * @param string $uuid
   *   A dataset identifier.
   *
   * @usage dkan:dataset-info abcd-1234
   *   Display info about dataset abcd-1234 and its resource(s).
   *
   * @command dkan:dataset-info
   */
  public function datasetInfo(string $uuid) {
    print_r(json_encode($this->datasetInfo->gather($uuid), JSON_PRETTY_PRINT));
  }

}
