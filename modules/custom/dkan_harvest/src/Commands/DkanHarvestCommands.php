<?php

namespace Drupal\dkan_harvest\Commands;

use Drush\Commands\DrushCommands;
use Drush\Style\DrushStyle;
use Drupal\dkan_harvest\Harvest;
use Drupal\dkan_harvest\DKANHarvest;

class DkanHarvestCommands extends DrushCommands {

  function __construct() {
		$config = dkan_harvest_initialize_config();
    $this->Harvest = new Harvest($config);
    $this->DKANHarvest = new DKANHarvest();
  }

  /**
   * Lists avaialble harvests.
   *
   * @command dkan-harvest:list
   *
   * @usage dkan-harvest:list
   *   List available harvests.
   */
  public function list() {
		return $this->DKANHarvest->sourceList();
  }

  /**
   * Caches harvest.
	 *
   * @param string $sourceId
   *   The source to cache.
   *
   * @command dkan-harvest:cache
   *
   * @usage dkan-harvest:cache
   *   Cache harvest source.
   */
  public function cache($sourceId) {
		$harvest = $this->DKANHarvest->sourceRead($sourceId);
		$this->Harvest->init($harvest);
    $this->Harvest->cache();
  }

  /**
   * Runs harvest.
	 *
   * @param string $sourceId
   *   The source to run.
   *
   * @command dkan-harvest:run
   *
   * @usage dkan-harvest:run
   *   Runs a harvest from the harvest source.
   */
  public function run($sourceId) {
		$harvest = $this->DKANHarvest->sourceRead($sourceId);
    $harvest->runId = $this->DKANHarvest->runCreate($sourceId);
		$this->Harvest->init($harvest);
    $items = $this->Harvest->extract();
    $items = $this->Harvest->transform($items);
    $this->Harvest->load($items);
  }

  /**
   * Reverts harvest.
	 *
   * @param string $sourceId
   *   The source to revert.
   *
   * @command dkan-harvest:revert
   *
   * @usage dkan-harvest:revert
   *   Removes harvested entities.
   */
  public function revert($sourceId) {
		$harvest = $this->DKANHarvest->sourceRead($sourceId);
    //$harvest->runId = $this->DKANHarvest->runCreate($sourceId);
		$this->Harvest->init($harvest);
    $this->Harvest->revert();
  }
}

