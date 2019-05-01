<?php

namespace Drupal\dkan_harvest\Commands;

use Harvest\EtlWorkerFactory;
use Harvest\Harvester;
use Harvest\Storage\Storage;
use Sae\Sae;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

use Drupal\dkan_harvest\Log\Stdout;
use Drupal\dkan_harvest\Reverter;
use Drupal\dkan_harvest\Storage\File;
use Drupal\dkan_harvest\Storage\IdGenerator;

use Drush\Commands\DrushCommands;

/**
 *
 */
class DkanHarvestCommands extends DrushCommands {

  /**
   * Lists avaialble harvests.
   *
   * @command dkan-harvest:list
   *
   * @usage dkan-harvest:list
   *   List available harvests.
   */
  public function index() {
    $store = $this->getPlanStorage();
    $items = $store->retrieveAll();

    $rows = [];

    foreach (array_keys($items) as $id) {
      $rows[] = [$id];
    }

    $table = new Table(new ConsoleOutput());

    $table
      ->setHeaders(['plan id'])
      ->setRows($rows);

    $table->render();
  }

  /**
   * Register a new harvest.
   *
   * @command dkan-harvest:register
   */
  public function register($config) {
    $storage = $this->getPlanStorage();

    $schema_path = DRUPAL_ROOT . "/" . drupal_get_path("module", "dkan_harvest") . "/schema/schema.json";
    $schema = file_get_contents($schema_path);

    $engine = new Sae($storage, $schema);
    $engine->setIdGenerator(new IdGenerator($config));
    $engine->post($config);
  }

  /**
   * Deregister a harvest.
   *
   * @command dkan-harvest:deregister
   */
  public function deregister($id) {
    $storage = $this->getPlanStorage();

    $schema_path = DRUPAL_ROOT . "/" . drupal_get_path("module", "dkan_harvest") . "/schema/schema.json";
    $schema = file_get_contents($schema_path);

    $engine = new Sae($storage, $schema);
    $engine->delete($id);
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
    $harvest_plan = $this->getHarvestPlan($sourceId);
    $harvest_plan->runId = 'cache';

    $path = \Drupal::service('file_system')->realpath(file_default_scheme() . "://");
    $item_folder = "{$path}/dkan_harvest/{$sourceId}";
    $hash_folder = "{$path}/dkan_harvest/{$sourceId}-hash";
    $item_storage = new File($item_folder);
    $hash_storage = new File($hash_folder);

    $factory = new EtlWorkerFactory($harvest_plan, $item_storage, $hash_storage);

    /* @var $extract \Drupal\dkan_harvest\Extract\Extract */
    $extract = $factory->get('extract');
    $extract->setLogger(new Stdout(TRUE, $harvest_plan->sourceId, "cache"));
    $extract->cache();
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
    $harvest_plan = $this->getHarvestPlan($sourceId);

    $path = \Drupal::service('file_system')->realpath(file_default_scheme() . "://");
    $item_folder = "{$path}/dkan_harvest/{$sourceId}";
    $hash_folder = "{$path}/dkan_harvest/{$sourceId}-hash";
    $run_folder = "{$path}/dkan_harvest/{$sourceId}-run";

    $item_storage = new File($item_folder);
    $hash_storage = new File($hash_folder);
    $run_storage = new File($run_folder);

    $harvester = new Harvester($harvest_plan, $item_storage, $hash_storage, $run_storage);
    $harvester->setLogger(new Stdout(TRUE, $sourceId, "run"));

    $results = $harvester->harvest();

    $rows = [];
    $rows[] = [$results['created'], $results['updated'], $results['skipped']];

    $table = new Table(new ConsoleOutput());
    $table->setHeaders(['created', 'updated', 'skipped'])->setRows($rows);
    $table->render();
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
    $path = \Drupal::service('file_system')->realpath(file_default_scheme() . "://");
    $hash_folder = "{$path}/dkan_harvest/{$sourceId}-hash";
    $hash_storage = new File($hash_folder);

    $reverter = new Reverter($sourceId, $hash_storage);
    $count = $reverter->run();

    $output = new ConsoleOutput();
    $output->write("{$count} items reverted for the '{$sourceId}' harvest plan.");
  }

  /**
   *
   */
  private function getHarvestPlan($sourceId) {
    $storage = $this->getPlanStorage();
    $harvest_plan = $storage->retrieve($sourceId);
    return json_decode($harvest_plan);
  }

  /**
   *
   */
  private function getPlanStorage(): Storage {
    $path = \Drupal::service('file_system')->realpath(file_default_scheme() . "://");
    $folder = "{$path}/dkan_harvest/plans";
    $store = new File($folder);
    return $store;
  }

}
