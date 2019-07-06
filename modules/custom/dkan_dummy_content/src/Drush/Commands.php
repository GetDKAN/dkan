<?php

namespace Drupal\dkan_dummy_content\Drush;

use Drupal\Core\Site\Settings;
use Drupal\dkan_harvest\Drush\Helper;

use Symfony\Component\Console\Output\ConsoleOutput;

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
    $this->createDummyJson();
    $harvester = $this->getHarvester("dummy");
    $result = $harvester->harvest();
    $this->renderResult($result);
    print_r($result);
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

  private function createDummyJson() {
    $dummy_template = DRUPAL_ROOT . "/" . drupal_get_path('module', 'dkan_dummy_content') . "/dummy.template.json";
    $content = file_get_contents($dummy_template);
    $new = $this->detokenize($content);
    file_put_contents(DRUPAL_ROOT . "/" . drupal_get_path('module', 'dkan_dummy_content') . "/dummy.json", $new);
  }

  private function detokenize($content) {
    $absolute_module_path = DRUPAL_ROOT . "/" . drupal_get_path('module', 'dkan_dummy_content') . "/files";
    return str_replace("<!*path*!>", $absolute_module_path, $content);
  }

}
