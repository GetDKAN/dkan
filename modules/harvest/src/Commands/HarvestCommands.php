<?php

namespace Drupal\harvest\Commands;

use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\harvest\Service;
use Drush\Commands\DrushCommands;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class.
 *
 * @codeCoverageIgnore
 */
class HarvestCommands extends DrushCommands {
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
   * @param string $harvest_plan
   *   Harvest plan configuration as JSON, wrapped in single quotes,
   *   do not add spaces between elements.
   *
   * @command dkan:harvest:register
   * @usage dkan-harvest:register '{"identifier":"example","extract":{"type":"\\Harvest\\ETL\\Extract\\DataJson","uri":"https://source/data.json"},"transforms":[],"load":{"type":"\\Drupal\\harvest\\Load\\Dataset"}}'
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
   *
   * @usage dkan:harvest:run
   *   Runs a harvest.
   */
  public function run($id) {
    $result = $this->harvestService
      ->runHarvest($id);

    // Fetch run_id from the harvest service.
    $run_ids = $this->harvestService
      ->getAllHarvestRunInfo($id);

    $this->renderHarvestRunsInfo([
      [end($run_ids), $result],
    ]);
  }

  /**
   * Run all pending harvests.
   *
   * @command dkan:harvest:run-all
   *
   * @usage dkan:harvest:run-all
   *   Runs all pending harvests.
   */
  public function runAll() {

    $ids = $this->harvestService
      ->getAllHarvestIds();

    foreach ($ids as $id) {
      $this->run($id);
    }
  }

  /**
   * Give information about a previous harvest run.
   *
   * @param string $harvestId
   *   The harvest id.
   * @param string $runId
   *   The run's id.
   *
   * @command dkan:harvest:info
   */
  public function info($harvestId, $runId = NULL) {
    $this->validateHarvestId($harvestId);
    $runIds = $runId ? [$runId] : $this->harvestService->getAllHarvestRunInfo($harvestId);

    foreach ($runIds as $id) {
      $run = $this->harvestService->getHarvestRunInfo($harvestId, $id);
      $result = json_decode($run, TRUE);

      $runs[] = [$id, $result];
    }

    $this->renderHarvestRunsInfo($runs ?? []);
  }

  /**
   * Revert a harvest, i.e. remove all of its harvested entities.
   *
   * @param string $harvestId
   *   The source to revert.
   *
   * @command dkan:harvest:revert
   *
   * @usage dkan:harvest:revert
   *   Removes harvested entities.
   */
  public function revert($harvestId) {
    $this->validateHarvestId($harvestId);
    $result = $this->harvestService->revertHarvest($harvestId);
    (new ConsoleOutput())->write("{$result} items reverted for the '{$harvestId}' harvest plan." . PHP_EOL);
  }

  /**
   * Show status of of a particular harvest run.
   *
   * @param string $harvestId
   *   The id of the harvest source.
   * @param string $runId
   *   The run's id. Optional. Show the status for the latest run if not
   *   provided.
   *
   * @command dkan:harvest:status
   *
   * @usage dkan:harvest:status
   *   test 1599157120
   */
  public function status($harvestId, $runId = NULL) {
    $this->validateHarvestId($harvestId);

    // No run_id provided, get the latest run_id.
    // Validate run_id.
    $allRunIds = $this->harvestService->getAllHarvestRunInfo($harvestId);

    if (empty($allRunIds)) {
      (new ConsoleOutput())->writeln("<error>No Run IDs found for harvest id $harvestId</error>");
      return DrushCommands::EXIT_FAILURE;
    }

    if (empty($runId)) {
      // Get the last run_id from the array.
      $runId = end($allRunIds);
      reset($allRunIds);
    }

    if (array_search($runId, $allRunIds) === FALSE) {
      (new ConsoleOutput())->writeln("<error>Run ID $runId not found for harvest id $harvestId</error>");
      return DrushCommands::EXIT_FAILURE;
    }

    $run = $this->harvestService->getHarvestRunInfo($harvestId, $runId);

    if (empty($run)) {
      (new ConsoleOutput())->writeln("<error>No status found for harvest id $harvestId and run id $runId</error>");
      return DrushCommands::EXIT_FAILURE;
    }

    $this->renderStatusTable($harvestId, $runId, json_decode($run, TRUE));
  }

  /**
   * Orphan datasets from every run of a harvest.
   *
   * @param string $harvestId
   *   Harvest identifier.
   *
   * @return int
   *   Exit code.
   *
   * @command dkan:harvest:orphan-datasets
   * @alias dkan:harvest:orphan
   */
  public function orphanDatasets(string $harvestId) : int {
    $this->validateHarvestId($harvestId);

    try {
      $orphans = $this->harvestService->getOrphanIdsFromCompleteHarvest($harvestId);
      $this->harvestService->processOrphanIds($orphans);
      $this->logger()->notice("Orphaned ids from harvest {$harvestId}: " . implode(", ", $orphans));
      return DrushCommands::EXIT_SUCCESS;
    }
    catch (\Exception $e) {
      $this->logger()->error("Error in orphaning datasets of harvest %harvest: %error", [
        '%harvest' => $harvestId,
        '%error' => $e->getMessage(),
      ]);
      return DrushCommands::EXIT_FAILURE;
    }
  }

  /**
   * Throw error if Harvest ID does not exist.
   *
   * @param string $harvestId
   *   The Harvest ID.
   */
  private function validateHarvestId($harvestId) {
    if (!in_array($harvestId, $this->harvestService->getAllHarvestIds())) {
      $this->logger()->error("Harvest id {$harvestId} not found.");
      return DrushCommands::EXIT_FAILURE;
    }
  }

}
