<?php

namespace Drupal\harvest\Commands;

use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\harvest\Load\Dataset;
use Drupal\harvest\HarvestService;
use Drush\Commands\DrushCommands;
use Harvest\ETL\Extract\DataJson;
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
   * @var \Drupal\harvest\HarvestService
   */
  protected $harvestService;

  /**
   * Constructor.
   */
  public function __construct(HarvestService $service, LoggerChannelInterface $logger) {
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
   * You may supply a full Harvest plan in JSON or provide configuration via
   * individual options. For a simple data.json harvest, pass only an
   * identifier and extract-uri.
   *
   * Harvest plans are validated against the schema at:
   * https://github.com/GetDKAN/harvest/blob/master/schema/schema.json
   *
   * @param string $plan_json
   *   Harvest plan configuration as JSON string. Example: '{"identifier":"example","extract":{"type":"\\Harvest\\ETL\\Extract\\DataJson","uri":"https://source/data.json"},"transforms":[],"load":{"type":"\\Drupal\\harvest\\Load\\Dataset"}}'.
   * @param array $opts
   *   Options array.
   *
   * @option identifier Identifier
   * @option extract-type Extract type
   * @option extract-uri Extract URI
   * @option transform A transform class to apply. You may pass multiple transforms.
   * @option load-type Load class
   *
   * @command dkan:harvest:register
   *
   * @usage dkan:harvest:register --identifier=myHarvestId --extract-uri=http://example.com/data.json
   */
  public function register(string $plan_json = '', array $opts = [
    'identifier' => '',
    'extract-type' => DataJson::class,
    'extract-uri' => '',
    'transform' => [],
    'load-type' => Dataset::class,
  ]) {
    try {
      $plan = $plan_json ? json_decode($plan_json) : $this->buildPlanFromOpts($opts);
      $identifier = $this->harvestService->registerHarvest($plan);
      $this->logger->notice('Successfully registered the ' . $identifier . ' harvest.');
    }
    catch (\Exception $e) {
      $this->logger->error($e->getMessage());
      $this->logger->debug($e->getTraceAsString());
    }
  }

  /**
   * Build a harvest plan object based on the options from register.
   *
   * @param mixed $opts
   *   Options array from register method.
   *
   * @return object
   *   A harvest plan PHP object.
   */
  protected function buildPlanFromOpts($opts) {
    return (object) [
      'identifier' => $opts['identifier'],
      'extract' => (object) [
        'type' => $opts['extract-type'] ?: NULL,
        'uri' => $opts['extract-uri'] ?: NULL,
      ],
      'transforms' => $opts['transform'],
      'load' => (object) [
        'type' => $opts['load-type'],
      ],
    ];
  }

  /**
   * Deregister a harvest.
   *
   * @command dkan:harvest:deregister
   */
  public function deregister($id) {
    $message = 'Could not deregister the ' . $id . ' harvest.';
    try {
      if ($this->harvestService->deregisterHarvest($id)) {
        $message = 'Successfully deregistered the ' . $id . ' harvest.';
      }
    }
    catch (\Exception $e) {
      $message = $e->getMessage();
    }

    $this->logger->notice($message);
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
   * Archive all harvested datasets for a single harvest.
   *
   * @param string $harvestId
   *   The source to archive harvests for.
   *
   * @command dkan:harvest:archive
   *
   * @usage dkan:harvest:archive
   *   Archives harvested entities.
   */
  public function archive($harvestId) {
    $this->validateHarvestId($harvestId);
    $result = $this->harvestService->archive($harvestId);
    if (empty($result)) {
      (new ConsoleOutput())->write("No items available to archive for the '{$harvestId}' harvest plan." . PHP_EOL);
    }
    foreach ($result as $id) {
      (new ConsoleOutput())->write("Archived dataset {$id} from harvest '{$harvestId}'." . PHP_EOL);
    }
  }

  /**
   * Archive all harvested datasets for a single harvest.
   *
   * @param string $harvestId
   *   The source to archive harvests for.
   *
   * @command dkan:harvest:publish
   *
   * @usage dkan:harvest:publish
   *   Publishes harvested entities.
   */
  public function publish($harvestId) {
    $this->validateHarvestId($harvestId);
    $result = $this->harvestService->publish($harvestId);
    if (empty($result)) {
      (new ConsoleOutput())->write("No items available to publish for the '{$harvestId}' harvest plan." . PHP_EOL);
    }
    foreach ($result as $id) {
      (new ConsoleOutput())->write("Published dataset {$id} from harvest '{$harvestId}'." . PHP_EOL);
    }
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
      $this->logger()->notice("Orphaned ids from harvest {$harvestId}: " . implode(', ', $orphans));
      return DrushCommands::EXIT_SUCCESS;
    }
    catch (\Exception $e) {
      $this->logger()->error('Error in orphaning datasets of harvest %harvest: %error', [
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
