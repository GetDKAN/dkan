<?php

namespace Drupal\metastore_search\Commands;

use Drush\Commands\DrushCommands;
use Drupal\search_api\Entity\Index;

/**
 * Class DkanSearchCommands.
 *
 * @package Drupal\metastore_search\RebuildTrackerCommands
 */
class RebuildTrackerCommands extends DrushCommands {

  /**
   * Rebuild the search api tracker for the dkan index.
   *
   * @command dkan:metastore-search:rebuild-tracker
   */
  public function rebuildTracker() {
    $index = Index::load('dkan');
    $index->rebuildTracker();
  }

}
