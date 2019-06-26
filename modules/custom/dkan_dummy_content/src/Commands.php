<?php

namespace Drupal\dkan_dummy_content;

use Drupal\dkan_harvest\Drush\Helper;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Helper\Table;
use Drupal\dkan_harvest\Storage\File;

use Drush\Commands\DrushCommands;

/**
 *
 */
class Commands extends DrushCommands {
  use Helper;

  /**
   * Create dummy content.
   *
   * @command dkan-dummy-content:create
   */
  public function create() {
    $harvester = $this->getHarvester("dummy");
    $result = $harvester->harvest();
    $this->renderResult($result);
  }

  /**
   * Remove dummy content.
   *
   * @command dkan-dummy-content:remove
   */
  public function remove() {
    $harvester = $this->getHarvester("dummy");
    $result = $harvester->revert();

    $count = $result;

    $output = new ConsoleOutput();
    $output->write("{$count} items reverted for the 'dummy' harvest plan.");
  }

  private function getHarvestPlan() {
    $module_path = DRUPAL_ROOT . "/" . drupal_get_path('module', 'dkan_dummy_content');

    $plan_path = $module_path . "/harvest_plan.json";
    $json = file_get_contents($plan_path);
    $plan = json_decode($json);

    $plan->extract->uri = "file://" . $module_path . $plan->extract->uri;

    return $plan;
  }

}
