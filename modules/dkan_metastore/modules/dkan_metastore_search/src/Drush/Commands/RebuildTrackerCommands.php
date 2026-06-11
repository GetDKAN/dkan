<?php

namespace Drupal\dkan_metastore_search\Drush\Commands;

use Drush\Commands\DrushCommands;
use Drupal\search_api\Entity\Index;
use Drush\Attributes as CLI;

/**
 * Rebuild the search api tracker for the  index.
 */
class RebuildTrackerCommands extends DrushCommands {

  /**
   * Rebuild the search api tracker for the dkan index.
   */
  #[CLI\Command(name: 'dkan:metastore-search:rebuild-tracker', description: 'Rebuild the search api tracker for the dkan index.')]
  public function rebuildTracker() {
    $index = Index::load('dkan');
    $index->rebuildTracker();
  }

}
