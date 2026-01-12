<?php

namespace Drupal\harvest\Commands;

use Drupal\harvest\HarvestService;
use Drupal\harvest\HarvestUtility;
use Drupal\harvest\Load\Dataset;
use Drush\Commands\DrushCommands;
use Drupal\harvest\ETL\Extract\DataJson;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Harvest-related Drush commands.
 */
class HarvestCommands extends DrushCommands {
  use Helper;

  /**
   * Harvest.
   */
  protected HarvestService $harvestService;

  /**
   * Harvest utility service.
   */
  protected HarvestUtility $harvestUtility;

  /**
   * Constructor.
   */
  public function __construct(
    HarvestService $service,
    HarvestUtility $harvestUtility,
  ) {
    parent::__construct();
    $this->harvestService = $service;
    $this->harvestUtility = $harvestUtility;
  }

  /**
   * List available harvest plans.
   *
   * @command dkan:harvest:list
   *
   * @usage dkan:harvest:list
   *   List available harvests.
   *
   * @codeCoverageIgnore
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
    $this->logger()->notice('No harvest plans registered.');
  }

  /**
   * Register a new harvest plan.
   *
   * You may supply a full Harvest plan in JSON or provide configuration via
   * individual options. For a simple data.json harvest, pass only an
   * identifier and extract-uri. If the plan JSON is provided, all options will
   * be ignored.
   *
   * Harvest plans are validated against the schema at:
   * https://github.com/GetDKAN/harvest/blob/master/schema/schema.json
   *
   * @param string $plan_json
   *   Harvest plan configuration as JSON string. Example: '{"identifier":"example","extract":{"type":"\\Drupal\\harvest\\ETL\\Extract\\DataJson","uri":"https://source/data.json"},"transforms":[],"load":{"type":"\\Drupal\\harvest\\Load\\Dataset"}}'.
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
  public function register(
    string $plan_json = '',
    array $opts = [
      'identifier' => '',
      'extract-type' => DataJson::class,
      'extract-uri' => '',
      'transform' => [],
      'load-type' => Dataset::class,
    ],
  ) {
    try {
      $plan = $plan_json ? json_decode($plan_json) : $this->buildPlanFromOpts($opts);
      $identifier = $this->harvestService->registerHarvest($plan);
      $this->logger()->notice('Successfully registered the ' . $identifier . ' harvest.');
    }
    catch (\Exception $e) {
      $this->logger()->error($e->getMessage());
      $this->logger()->debug($e->getTraceAsString());
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
    // Filter the array, so subsequent plan schema validation will throw an
    // error if anything is missing.
    return (object) array_filter([
      'identifier' => $opts['identifier'] ?? NULL,
      'extract' => (object) array_filter([
        'type' => $opts['extract-type'] ?? NULL,
        'uri' => $opts['extract-uri'] ?? NULL,
      ]),
      'transforms' => $opts['transform'],
      'load' => (object) array_filter([
        'type' => $opts['load-type'],
      ]),
    ]);
  }

  /**
   * Deregister (delete) a harvest plan, optionally reverting it.
   *
   * @param string $plan_id
   *   The harvest plan ID to deregister.
   * @param array $options
   *   Options.
   *
   * @command dkan:harvest:deregister
   * @option revert Revert the harvest plan (remove all harvested datasets) before deregistering it.
   * @usage dkan:harvest:deregister --revert PLAN_ID
   *   Deregister the PLAN_ID plan, after reverting all the datasets
   *   associated with it.
   *
   * @codeCoverageIgnore
   */
  public function deregister($plan_id, array $options = ['revert' => FALSE]) {
    // Short circuit if the plan doesn't exist.
    try {
      $this->validateHarvestPlan($plan_id);
    }
    catch (\InvalidArgumentException $exception) {
      $this->logger()->error($exception->getMessage());
      return DrushCommands::EXIT_FAILURE;
    }

    // Are You Sure?
    $message = 'Are you sure you want to deregister ' . $plan_id;
    if ($options['revert'] ?? FALSE) {
      $message = 'Are you sure you want to revert and deregister ' . $plan_id;
    }
    if (!$this->io()->confirm($message)) {
      return DrushCommands::EXIT_FAILURE;
    }

    // Try to revert if the user wants to.
    if (
      ($options['revert'] ?? FALSE) &&
      ($this->revert($plan_id) === DrushCommands::EXIT_FAILURE)
    ) {
      return DrushCommands::EXIT_FAILURE;
    }

    // Do the deregister.
    if ($this->harvestService->deregisterHarvest($plan_id)) {
      $this->logger()->notice('Successfully deregistered the ' . $plan_id . ' harvest.');
      return DrushCommands::EXIT_SUCCESS;
    }

    $this->logger()->error('Could not deregister the ' . $plan_id . ' harvest.');
    return DrushCommands::EXIT_FAILURE;
  }

  /**
   * Run a harvest.
   *
   * @param string $plan_id
   *   The harvest plan id.
   *
   * @command dkan:harvest:run
   *
   * @usage dkan:harvest:run
   *   Runs a harvest.
   *
   * @codeCoverageIgnore
   */
  public function run($plan_id) {
    $result = $this->harvestService->runHarvest($plan_id);
    $this->renderHarvestRunsInfo([$result]);
  }

  /**
   * Run all registered harvest plans.
   *
   * @option new Run only harvests which haven't run before.
   *
   * @command dkan:harvest:run-all
   *
   * @usage dkan:harvest:run-all
   *   Runs all harvests.
   *
   * @codeCoverageIgnore
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
    }
    $this->renderHarvestRunsInfo($runs_info);
  }

  /**
   * Show a harvest plan and information about its runs.
   *
   * @param string $harvestId
   *   The harvest plan id.
   * @param string $runId
   *   A harvest run ID. If not provided, all runs will be shown.
   *
   * @command dkan:harvest:info
   *
   * @codeCoverageIgnore
   */
  public function info($harvestId, $runId = NULL) {
    try {
      $this->validateHarvestPlan($harvestId);
    }
    catch (\InvalidArgumentException $exception) {
      $this->logger()->error($exception->getMessage());
      return DrushCommands::EXIT_FAILURE;
    }

    $plan = $this->harvestService->getHarvestPlanObject($harvestId);
    // Format and output the harvest plan JSON.
    $this->renderHarvestPlan($plan);
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
   *
   * @codeCoverageIgnore
   */
  public function revert($harvestId) {
    $this->validateHarvestPlan($harvestId);
    $result = $this->harvestService->revertHarvest($harvestId);
    $this->logger()->notice($result . ' items reverted for the \'' . $harvestId . '\' harvest plan.');
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
   *
   * @codeCoverageIgnore
   */
  public function archive($harvestId) {
    $this->archiveOrPublish($harvestId, 'archive');
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
   *
   * @codeCoverageIgnore
   */
  public function publish($harvestId) {
    $this->archiveOrPublish($harvestId, 'publish');
  }

  /**
   * Perform the act of archiving or publishing a harvest plan.
   *
   * @param string $plan_id
   *   The harvest id.
   * @param string $operation
   *   (optional) The operation to perform. Either 'archive' or 'publish.'
   *   Defaults to 'archive'.
   *
   * @codeCoverageIgnore
   */
  protected function archiveOrPublish($plan_id, $operation = 'archive') {
    $verb = 'Archived';
    if ($operation === 'publish') {
      $verb = 'Published';
    }
    $this->validateHarvestPlan($plan_id);
    $result = $this->harvestService->$operation($plan_id);
    if (empty($result)) {
      $this->logger()->notice('No items available to ' . $operation . ' for the \'' . $plan_id . '\' harvest plan.');
    }
    foreach ($result as $id) {
      $this->logger()->notice($verb . ' dataset ' . $id . ' from harvest \'' . $plan_id . '\'.');
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
   *
   * @codeCoverageIgnore
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
      $this->logger()->error('Run ID ' . $runId . ' not found for harvest id ' . $harvestId);
      return DrushCommands::EXIT_FAILURE;
    }

    $run = $this->harvestService->getHarvestRunInfo($harvestId, $runId);

    if (empty($run)) {
      $this->logger()->error('No status found for harvest id ' . $harvestId . ' and run id ' . $runId);
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
   *
   * @codeCoverageIgnore
   */
  public function orphanDatasets(string $harvestId) : int {
    $this->validateHarvestPlan($harvestId);

    try {
      $orphans = $this->harvestService->getOrphanIdsFromCompleteHarvest($harvestId);
      $this->harvestService->processOrphanIds($orphans);
      $this->logger()->notice('Orphaned ids from harvest ' . $harvestId . ': ' . implode(', ', $orphans));
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
   *
   * @codeCoverageIgnore
   */
  public function harvestCleanup(): int {
    $orphaned = $this->harvestUtility->findOrphanedHarvestDataIds();
    if ($orphaned) {
      $this->logger()->notice('Detected leftover harvest data for these plans: ' . implode(', ', $orphaned));
      if ($this->io()->confirm('Do you want to remove this data?', FALSE)) {
        $this->cleanupHarvestDataTables($orphaned);
      }
    }
    else {
      $this->logger()->notice('No leftover harvest data detected.');
    }
    return DrushCommands::EXIT_SUCCESS;
  }

  /**
   * Perform the harvest data table cleanup.
   *
   * @param array $plan_ids
   *   An array of plan identifiers to clean up.
   *
   * @codeCoverageIgnore
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
   *
   * @codeCoverageIgnore
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
   *
   * @codeCoverageIgnore
   */
  public function harvestUpdate(): int {
    $this->harvestUtility->harvestHashUpdate();
    $this->harvestUtility->harvestRunsUpdate();
    $this->logger()->success('Converted!');
    return DrushCommands::EXIT_SUCCESS;
  }

}
