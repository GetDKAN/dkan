<?php

namespace Drupal\dkan_harvest\Drush;

use Harvest\ETL\Factory;
use Harvest\ResultInterpreter;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

use Drush\Commands\DrushCommands;

/**
 *
 */
class Commands extends DrushCommands {
  use Helper;

  /**
   * Lists avaialble harvests.
   *
   * @command dkan-harvest:list
   *
   * @usage dkan-harvest:list
   *   List available harvests.
   */
  public function index() {
    $rows = array_map(function($id) {
      return [$id];
    }, array_keys($this->getPlanStorage()->retrieveAll()));

    (new Table(new ConsoleOutput()))->setHeaders(['plan id'])->setRows($rows)->render();
  }

  /**
   * Register a new harvest.
   *
   * @command dkan-harvest:register
   */
  public function register($harvest_plan) {
    $plan = json_decode($harvest_plan);

    if ($plan == null) {
      $message = "The harvest plan is not valid json.";
    }
    else {
      try {
        Factory::validateHarvestPlan($plan);
        $this->getPlanStorage()->store($harvest_plan, $plan->identifier);
        $message = "Succesfully registered the {$plan->identifier} harvest.";
      } catch (\Exception $e) {
        $message = $e->getMessage();
      }
    }

    (new ConsoleOutput())->write($message . PHP_EOL);
  }

  /**
   * Deregister a harvest.
   *
   * @command dkan-harvest:deregister
   */
  public function deregister($id) {
    try {
      $this->revert($id);
      $this->getPlanStorage()->remove($id);
      $message = "Succesfully deregistered the {$id} harvest.";
    }
    catch(\Exception $e) {
      $message = $e->getMessage();
    }

    (new ConsoleOutput())->write($message . PHP_EOL);
  }

  /**
   * Runs harvest.
   *
   * @param string $id
   *   The harvest id.
   *
   * @command dkan-harvest:run
   *
   * @usage dkan-harvest:run
   *   Runs a harvest.
   */
  public function run($id) {
    $result = $this->getHarvester($id)->harvest();

    $this->getStorage($id, "run")->store(json_encode($result), time());

    $this->renderResult($result);
  }

  /**
   * Gives information about a previous harvest run.
   *
   * @param string $id
   *   The harvest id.
   * @param string $run_id
   *   The run's id
   *
   * @command dkan-harvest:info
   */
  public function info($id, $run_id = null) {
    $runs = $this->getStorage($id, 'run')->retrieveAll();

    if (!isset($run_id)) {
      $table = new Table(new ConsoleOutput());
      $table->setHeaders(["{$id} runs"]);
      foreach (array_keys($runs) as $run_id) {
        $table->addRow([$run_id]);
      }
      $table->render();
    }
    else {
      $result = json_decode($runs[$run_id]);
      print_r($result);
    }

  }

  /**
   * Reverts harvest.
   *
   * @param string $id
   *   The source to revert.
   *
   * @command dkan-harvest:revert
   *
   * @usage dkan-harvest:revert
   *   Removes harvested entities.
   */
  public function revert($id) {
    $result = $this->getHarvester($id)->revert();
    (new ConsoleOutput())->write("{$result} items reverted for the '{$id}' harvest plan." . PHP_EOL);
  }

  /**
   *
   */
  private function getHarvestPlan($id) {
    return json_decode($this->getPlanStorage()->retrieve($id));
  }
}
