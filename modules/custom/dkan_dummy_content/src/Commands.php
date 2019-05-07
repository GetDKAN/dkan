<?php

namespace Drupal\dkan_dummy_content;

use Harvest\Harvester;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Helper\Table;

use Drupal\dkan_harvest\Log\Stdout;
use Drupal\dkan_harvest\Reverter;
use Drupal\dkan_harvest\Storage\File;

use Drush\Commands\DrushCommands;

/**
 *
 */
class Commands extends DrushCommands {

  /**
   * Create dummy content.
   *
   * @command dkan-dummy-content:create
   */
  public function create() {

    $harvest_plan_file_path = drupal_get_path("module", "dkan_dummy_content") . "/harvest_plan.json";
    $harvest_plan_json = file_get_contents($harvest_plan_file_path);
    $harvest_plan = json_decode($harvest_plan_json);

    $sourceId = "dummy";
    $path = \Drupal::service('file_system')->realpath(file_default_scheme() . "://");
    $item_folder = "{$path}/dkan_harvest/{$sourceId}";
    $hash_folder = "{$path}/dkan_harvest/{$sourceId}-hash";
    $run_folder = "{$path}/dkan_harvest/{$sourceId}-run";

    $item_storage = new File($item_folder);
    $hash_storage = new File($hash_folder);
    $run_storage = new File($run_folder);

    $harvester = new Harvester($harvest_plan, $item_storage, $hash_storage, $run_storage);
    $harvester->setLogger(new Stdout(TRUE, "dummy", "run"));

    $results = $harvester->harvest();

    $rows = [];
    $rows[] = [$results['created'], $results['updated'], $results['skipped']];

    $table = new Table(new ConsoleOutput());
    $table->setHeaders(['created', 'updated', 'skipped'])->setRows($rows);
    $table->render();
  }

  /**
   * Remove dummy content.
   *
   * @command dkan-dummy-content:remove
   */
  public function remove() {
    $sourceId = "dummy";
    $path = \Drupal::service('file_system')->realpath(file_default_scheme() . "://");
    $hash_folder = "{$path}/dkan_harvest/{$sourceId}-hash";
    $hash_storage = new File($hash_folder);

    $reverter = new Reverter($sourceId, $hash_storage);
    $count = $reverter->run();

    $output = new ConsoleOutput();
    $output->write("{$count} items reverted for the 'dummy' harvest plan.");
  }

}
