<?php

namespace Drupal\harvest\Commands;

use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\harvest\HarvestUtility;
use Drupal\harvest\Load\Dataset;
use Drupal\harvest\HarvestService;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;
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
  protected HarvestService $harvestService;

  /**
   * Harvest utility service.
   *
   * @var \Drupal\harvest\HarvestUtility
   */
  protected HarvestUtility $harvestUtility;

  /**
   * Constructor.
   */
  public function __construct(
    HarvestService $service,
    LoggerChannelInterface $logger,
    HarvestUtility $harvestUtility
  ) {
    parent::__construct();
    // @todo passing via arguments doesn't seem play well with drush.services.yml
    $this->harvestService = $service;
    $this->logger = $logger;
    $this->harvestUtility = $harvestUtility;
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
    if ($rows) {
      (new Table(new ConsoleOutput()))->setHeaders(['plan id'])->setRows($rows)->render();
      return;
    }
    $this->logger->notice('No harvests registered.');
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
  protected function buildPlanFromOpts(mixed $opts) {
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
    $this->logger->warning(
      'If you deregister a harvest with published datasets, you will
       not be able to bulk revert the datasets connected to this harvest.');
    if ($this->io()->confirm("Deregister harvest {$id}")) {
      if ($this->harvestService->deregisterHarvest($id)) {
        $message = 'Successfully deregistered the ' . $id . ' harvest.';
      }
    }
    else {
      throw new UserAbortException();
    }

    $this->logger->notice($message);
  }

  /**
   * Run a harvest.
   *
   * @param string $plan_id
   *   The harvest id.
   *
   * @command dkan:harvest:run
   *
   * @usage dkan:harvest:run
   *   Runs a harvest.
   */
  public function run($plan_id) {
    $result = $this->harvestService->runHarvest($plan_id);
    $this->renderHarvestRunsInfo([$result]);
  }

  /**
   * Run all harvests.
   *
   * @option new Run only harvests which haven't run before.
   *
   * @command dkan:harvest:run-all
   *
   * @usage dkan:harvest:run-all
   *   Runs all harvests.
   */
  public function runAll($options = ['new' => FALSE]) {
    $plan_ids = $this->harvestService->getAllHarvestIds(FALSE);
    if ($options['new']) {
      $plan_ids = array_diff(
        $plan_ids, $this->harvestService->getAllHarvestIds(TRUE)
      );
    }
    $runs_info = [];
    foreach ($plan_ids as $plan_id) {
      $result = $this->harvestService->runHarvest($plan_id);
      $runs_info[] = $result;
      // Since run IDs are also one-second-resolution timestamps, we must wait
      // one second before running the next harvest.
      // @todo Remove this sleep when we've switched to a better system for
      //   timestamps.
      sleep(1);
    }
    $this->renderHarvestRunsInfo($runs_info);
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
    $this->validateHarvestPlan($harvestId);
    $runIds = $runId ? [$runId] : $this->harvestService->getRunIdsForHarvest($harvestId);

    foreach ($runIds as $id) {
      $run = $this->harvestService->getHarvestRunInfo($harvestId, $id);
      $runs[] = json_decode($run, TRUE);
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
    $this->validateHarvestPlan($harvestId);
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
    $this->validateHarvestPlan($harvestId);
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
    $this->validateHarvestPlan($harvestId);
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
    $this->validateHarvestPlan($harvestId);

    // No run_id provided, get the latest run_id.
    // Validate run_id.
    $allRunIds = $this->harvestService->getRunIdsForHarvest($harvestId);

    if (empty($allRunIds)) {
      $this->logger()->error('No Run IDs found for harvest id ' . $harvestId);
      return DrushCommands::EXIT_FAILURE;
    }

    if (empty($runId)) {
      // Get the last run_id from the array.
      $runId = end($allRunIds);
      reset($allRunIds);
    }

    if (array_search($runId, $allRunIds) === FALSE) {
      $this->logger()->error("Run ID $runId not found for harvest id $harvestId");
      return DrushCommands::EXIT_FAILURE;
    }

    $run = $this->harvestService->getHarvestRunInfo($harvestId, $runId);

    if (empty($run)) {
      $this->logger()->error("No status found for harvest id $harvestId and run id $runId");
      return DrushCommands::EXIT_FAILURE;
    }

    $this->renderStatusTable($harvestId, $runId, json_decode($run, TRUE));
    return DrushCommands::EXIT_SUCCESS;
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
    $this->validateHarvestPlan($harvestId);

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
   * Report and cleanup harvest data which may be cluttering your database.
   *
   * Will print a report. Add -y or --no-interaction to automatically perform
   * this cleanup.
   *
   * @command dkan:harvest:cleanup
   *
   * @return int
   *   Bash status code.
   *
   * @bootstrap full
   */
  public function harvestCleanup(): int {
    $logger = $this->logger();
    $orphaned = $this->harvestUtility->findOrphanedHarvestDataIds();
    if ($orphaned) {
      $logger->notice('Detected leftover harvest data for these plans: ' . implode(', ', $orphaned));
      if ($this->io()->confirm('Do you want to remove this data?', FALSE)) {
        $this->cleanupHarvestDataTables($orphaned);
      }
    }
    else {
      $logger->notice('No leftover harvest data detected.');
    }
    return DrushCommands::EXIT_SUCCESS;
  }

  /**
   * Perform the harvest data table cleanup.
   *
   * @param array $plan_ids
   *   An array of plan identifiers to clean up.
   */
  protected function cleanupHarvestDataTables(array $plan_ids) : void {
    foreach ($plan_ids as $plan_id) {
      $this->logger()->notice('Cleaning up: ' . $plan_id);
      $this->harvestUtility->destructOrphanTables($plan_id);
    }
  }

  /**
   * Throw error if Harvest ID does not exist.
   *
   * @param string $harvest_plan_id
   *   The Harvest ID.
   */
  private function validateHarvestPlan($harvest_plan_id) {
    if (!in_array($harvest_plan_id, $this->harvestService->getAllHarvestIds())) {
      throw new \InvalidArgumentException('Harvest id ' . $harvest_plan_id . ' not found.');
    }
  }

  /**
   * Update all harvest-related database tables to the latest version.
   *
   * This command is meant to aid in updating databases where the update hook
   * has already run, but the database still has old-style hash tables, with
   * names like harvest_PLANID_hash.
   *
   * This will move all harvest hash information to the updated schema,
   * including data which does not have a corresponding hash plan ID.
   *
   * Outdated tables will be removed.
   *
   * @command dkan:harvest:update
   *
   * @return int
   *   Bash status code.
   *
   * @bootstrap full
   */
  public function harvestUpdate(): int {
    $this->harvestUtility->harvestHashUpdate();
    $this->harvestUtility->harvestRunsUpdate();
    $this->logger()->success('Converted!');
    return DrushCommands::EXIT_SUCCESS;
  }

}
