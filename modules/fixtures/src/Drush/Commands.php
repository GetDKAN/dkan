<?php

namespace Drupal\fixtures\Drush;

use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Output\ConsoleOutput;
use Drupal\harvest\Drush\Helper;

/**
 * Class.
 */
class Commands extends DrushCommands {
  use Helper;

  /**
   * Create fixtures content.
   *
   * @command fixtures:create
   */
  public function create() {
    $this->createfixturesJson();
    $harvester = $this->getHarvester("fixtures");
    $result = $harvester->harvest();
    $this->renderResult($result);
    print_r($result);
  }

  /**
   * Remove fixtures content.
   *
   * @command fixtures:remove
   */
  public function remove() {
    $harvester = $this->getHarvester("fixtures");
    $result = $harvester->revert();

    $count = $result;

    $output = new ConsoleOutput();
    $output->write("{$count} items reverted for the 'fixtures' harvest plan.");
  }

  /**
   * Private.
   */
  private function getHarvestPlan() {
    $module_path = DRUPAL_ROOT . "/" . drupal_get_path('module', 'fixtures');

    $plan_path = $module_path . "/harvest_plan.json";
    $json = file_get_contents($plan_path);
    $plan = json_decode($json);

    $plan->extract->uri = "file://" . $module_path . $plan->extract->uri;

    return $plan;
  }

  /**
   * Private.
   */
  private function createfixturesJson() {
    $fixtures_template = DRUPAL_ROOT . "/" . drupal_get_path('module', 'fixtures') . "/fixtures.template.json";
    $content = file_get_contents($fixtures_template);
    $new = $this->detokenize($content);
    file_put_contents(DRUPAL_ROOT . "/" . drupal_get_path('module', 'fixtures') . "/fixtures.json", $new);
  }

  /**
   * Private.
   */
  private function detokenize($content) {
    $absolute_module_path = DRUPAL_ROOT . "/" . drupal_get_path('module', 'fixtures') . "/files";
    return str_replace("<!*path*!>", $absolute_module_path, $content);
  }

}
