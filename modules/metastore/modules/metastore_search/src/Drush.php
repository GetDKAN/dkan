<?php

namespace Drupal\metastore_search;

use Drush\Commands\DrushCommands;
use Drupal\search_api\Entity\Index;

/**
 * Class DkanSearchCommands.
 *
 * @package Drupal\metastore_search\Drush
 */
class Drush extends DrushCommands {

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
