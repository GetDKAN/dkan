<?php

namespace Drupal\dkan_harvest\Drush;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

use Drush\Commands\DrushCommands;

/**
 * @codeCoverageIgnore
 */
class Commands extends DrushCommands {

  /**
   *
   * @var \Drupal\dkan_harvest\Service\Factory
   */
  protected $harvestFactory;

  /**
   *
   * @var \Drupal\dkan_harvest\Service\Harvest
   */
  protected $harvestService;

  /**
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Commands constructor.
   */
  public function __construct() {
    // @todo passing via arguments doesn't seem play well with drush.services.yml
    $this->harvestFactory = \Drupal::service('dkan_harvest.factory');
    $this->harvestService = \Drupal::service('dkan_harvest.service');
    $this->logger = \Drupal::service('dkan_harvest.logger_channel');
  }

  /**
   * Lists available harvests.
   *
   * @command dkan-harvest:list
   *
   * @usage dkan-harvest:list
   *   List available harvests.
   */
  public function index() {
    // Each row needs to be an array for display.
    $rows = array_map(
      function ($id) {
        return [$id];
      },
      $this->harvestService->getAllHarvestIds()
      );
    (new Table(new ConsoleOutput()))->setHeaders(['plan id'])->setRows($rows)->render();
  }

  /**
   * Register a new harvest.
   *
   * @command dkan-harvest:register
   */
  public function register($harvest_plan) {
    try {
      $plan       = json_decode($harvest_plan);
      $identifier = $this->harvestService
        ->registerHarvest($plan);
      $this->logger->notice("Successfully registered the {$identifier} harvest.");
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
      $this->logger->debug($e->getTraceAsString());
    }
  }

  /**
   * Deregister a harvest.
   *
   * @command dkan-harvest:deregister
   */
  public function deregister($id) {
    try {
      if ($this->harvestService->deregisterHarvest($id)) {
        $message = "Successfully deregistered the {$id} harvest.";
      }
    }
    catch (\Exception $e) {
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
    $result = $this->harvestService
      ->runHarvest($id);

    $this->renderResult($result);
  }

  /**
   * Runs all pending harvests.
   *
   * @command dkan-harvest:run-all
   *
   * @usage dkan-harvest:run-all
   *   Runs all pending harvests.
   */
  public function runAll() {

    $ids = $this->harvestService
      ->getAllHarvestIds();;

    foreach ($ids as $id) {
      $this->run($id);
    }
  }

  /**
   * Gives information about a previous harvest run.
   *
   * @param string $id
   *   The harvest id.
   * @param string $run_id
   *   The run's id.
   *
   * @command dkan-harvest:info
   */
  public function info($id, $run_id = NULL) {

    if (!isset($run_id)) {
      $runs = $this->harvestService
        ->getAllHarvestRunInfo($id);
      $table = new Table(new ConsoleOutput());
      $table->setHeaders(["{$id} runs"]);
      foreach (array_keys($runs) as $run_id) {
        $table->addRow([$run_id]);
      }
      $table->render();
    }
    else {
      $run = $this->harvestService
        ->getHarvestRunInfo($id, $run_id);
      $result = json_decode($run);
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

    $result = $this->harvestService
      ->revertHarvest($id);

    (new ConsoleOutput())->write("{$result} items reverted for the '{$id}' harvest plan." . PHP_EOL);
  }

}
