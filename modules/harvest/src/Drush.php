<?php

namespace Drupal\harvest;

use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\harvest\Drush\Helper;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class.
 *
 * @codeCoverageIgnore
 */
class Drush extends DrushCommands {
  use Helper;

  /**
   * Harvest.
   *
   * @var \Drupal\harvest\Service
   */
  protected $harvestService;

  /**
   * Logger.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructor.
   */
  public function __construct(Service $service, LoggerChannelInterface $logger) {
    parent::__construct();
    // @todo passing via arguments doesn't seem play well with drush.services.yml
    $this->harvestService = $service;
    $this->logger = $logger;
  }

  /**
   * List available harvests.
   *
   * @command dkan:harvest:list
   * @aliases dkan-harvest:list
   * @deprecated dkan-harvest:list is deprecated and will be removed in a future Dkan release. Use dkan:harvest:list instead.
   *
   * @usage dkan:harvest:list
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
   * @command dkan:harvest:register
   * @aliases dkan-harvest:register
   * @deprecated dkan-harvest:register is deprecated and will be removed in a future Dkan release. Use dkan:harvest:register instead.
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
   * @command dkan:harvest:deregister
   * @aliases dkan-harvest:deregister
   * @deprecated dkan-harvest:deregister is deprecated and will be removed in a future Dkan release. Use dkan:harvest:deregister instead.
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
   * Run a harvest.
   *
   * @param string $id
   *   The harvest id.
   *
   * @command dkan:harvest:run
   * @aliases dkan-harvest:run
   * @deprecated dkan-harvest:run is deprecated and will be removed in a future Dkan release. Use dkan:harvest:run instead.
   *
   * @usage dkan:harvest:run
   *   Runs a harvest.
   */
  public function run($id) {
    $result = $this->harvestService
      ->runHarvest($id);

    $this->renderResult($result);
  }

  /**
   * Run all pending harvests.
   *
   * @command dkan:harvest:run-all
   * @aliases dkan-harvest:run-all
   * @deprecated dkan-harvest:run-all is deprecated and will be removed in a future Dkan release. Use dkan:harvest:run-all instead.
   *
   * @usage dkan:harvest:run-all
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
   * Give information about a previous harvest run.
   *
   * @param string $id
   *   The harvest id.
   * @param string $run_id
   *   The run's id.
   *
   * @command dkan:harvest:info
   * @aliases dkan-harvest:info
   * @deprecated dkan-harvest:info is deprecated and will be removed in a future Dkan release. Use dkan:harvest:info instead.
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
   * Revert a harvest, i.e. remove all of its harvested entities.
   *
   * @param string $id
   *   The source to revert.
   *
   * @command dkan:harvest:revert
   * @aliases dkan-harvest:revert
   * @deprecated dkan-harvest:revert is deprecated and will be removed in a future Dkan release. Use dkan:harvest:revert instead.
   *
   * @usage dkan:harvest:revert
   *   Removes harvested entities.
   */
  public function revert($id) {

    $result = $this->harvestService
      ->revertHarvest($id);

    (new ConsoleOutput())->write("{$result} items reverted for the '{$id}' harvest plan." . PHP_EOL);
  }

  /**
   * Show status of of a particular harvest run.
   *
   * @param string $harvest_id
   *   The id of the harvest source.
   * @param string $run_id
   *   The run's id. Optional. Show the status for the latest run if not
   *   provided.
   *
   * @command dkan:harvest:status
   *
   * @usage dkan:harvest:status
   *   test 1599157120
   */
  public function status($harvest_id, $run_id = NULL) {
    // Validate the harvest id.
    $harvest_id_all = $this->harvestService->getAllHarvestIds();

    if (array_search($harvest_id, $harvest_id_all) === FALSE) {
      (new ConsoleOutput())->writeln("<error>harvest id $harvest_id not found.</error>");
      return DrushCommands::EXIT_FAILURE;
    }

    // No run_id provided, get the latest run_id.
    // Validate run_id.
    $run_id_all = $this->harvestService->getAllHarvestRunInfo($harvest_id);

    if (empty($run_id_all)) {
      (new ConsoleOutput())->writeln("<error>No Run IDs found for harvest id $harvest_id</error>");
      return DrushCommands::EXIT_FAILURE;
    }

    if (empty($run_id)) {
      $run_id = $run_id_all[0];
    }

    if (array_search($run_id, $run_id_all) === FALSE) {
      (new ConsoleOutput())->writeln("<error>Run ID $run_id not found for harvest id $harvest_id</error>");
      return DrushCommands::EXIT_FAILURE;
    }

    $run = $this->harvestService
      ->getHarvestRunInfo($harvest_id, $run_id);

    if (empty($run)) {
      (new ConsoleOutput())->writeln("<error>No status found for harvest id $harvest_id and run id $run_id</error>");
      return DrushCommands::EXIT_FAILURE;
    }

    $run = json_decode($run, TRUE);

    $this->renderStatusTable($harvest_id, $run_id, $run);
  }

}
