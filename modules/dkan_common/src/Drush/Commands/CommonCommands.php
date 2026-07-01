<?php

namespace Drupal\dkan_common\Drush\Commands;

use Drupal\dkan_common\DatasetInfo;
use Drush\Commands\DrushCommands;
use Drush\Commands\AutowireTrait;
use Drush\Attributes as CLI;

/**
 * Drush commands providing utility common to DKAN's sub-modules.
 */
final class CommonCommands extends DrushCommands {

  use AutowireTrait;

  public function __construct(protected DatasetInfo $datasetInfo) {
    parent::__construct();
  }

  /**
   * Display information about a dataset and its resource(s).
   */
  #[CLI\Command(name: 'dkan:dataset-info', description: 'Display information about a dataset and its resource(s).')]
  public function datasetInfo(string $uuid) {
    return json_encode($this->datasetInfo->gather($uuid), JSON_PRETTY_PRINT);
  }

}
