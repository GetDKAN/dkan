<?php

namespace Drupal\dkan_harvest\Commands;

use Drush\Commands\DrushCommands;
use Drush\Style\DrushStyle;
use Drupal\dkan_harvest\Harvest;
use Drupal\dkan_harvest\DKANHarvest;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

class DkanHarvestCommands extends DrushCommands {

  protected $table;
  protected $output;

  function __construct() {
    $config = dkan_harvest_initialize_config();
    $this->Harvest = new Harvest($config);
    $this->DKANHarvest = new DKANHarvest();
    $this->output = new ConsoleOutput();
    $this->table = new Table($this->output);
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
    $items = $this->DKANHarvest->sourceList();
    foreach ($items['source_id'] as $item) {
      $rows[$item][] = $item;
    }
    $this->table
      ->setHeaders(array('source id'))
      ->setRows($rows);
    $this->table->render();
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
    $harvest->runId = 'cache';
    if ($this->Harvest->init($harvest)) {
      $this->Harvest->cache();
    }
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
    if ($this->Harvest->init($harvest)) {
      $items = $this->Harvest->extract();
      $items = $this->Harvest->transform($items);
      $results = $this->Harvest->load($items);
      $rows = [];
      foreach ($results as $bundle => $count) {
        $rows[] = [$bundle, $count['created'], $count['updated'], $count['skipped']];
      }
      $this->table
        ->setHeaders(['bundle', 'created', 'updated', 'skipped'])
        ->setRows($rows);
      $this->table->render();

    }
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
    $harvest->runId = 'revert';
    if ($this->Harvest->init($harvest)) {
      $this->Harvest->revert();
    }
  }
}

