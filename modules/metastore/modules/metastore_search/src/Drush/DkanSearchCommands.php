<?php

namespace Drupal\metadata_search\Drush;

use Drush\Commands\DrushCommands;
use Drupal\search_api\Entity\Index;

/**
 * Class DkanSearchCommands.
 *
 * @package Drupal\metadata_search\Drush
 */
class DkanSearchCommands extends DrushCommands {

  /**
   * Rebuild the search api tracker for the dkan index.
   *
   * @command metadata-search:rebuild-tracker
   */
  public function rebuildTracker() {
    $index = Index::load('dkan');
    $index->rebuildTracker();
  }

}
