<?php

namespace Drupal\harvest;

use Contracts\FactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\common\LoggerTrait;
use Drupal\harvest\Entity\HarvestPlanRepository;
use Drupal\metastore\MetastoreService;
use Harvest\ETL\Factory;
use Harvest\Harvester;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Main DKAN Harvester service.
 *
 * Import groups of datasets from an external source, and manage existing
 * harvest plans and their dependent datasets.
 */
class HarvestService implements ContainerInjectionInterface {

  use LoggerTrait;
  use OrphanDatasetsProcessor;

  /**
   * Service to instantiate storage objects for Harvest plan storage.
   *
   * @var \Contracts\FactoryInterface
   *
   * @see \Drupal\harvest\Storage\DatabaseTableFactory
   */
  private $storeFactory;

  /**
   * DKAN metastore service.
   *
   * @var \Drupal\metastore\MetastoreService
   */
  private MetastoreService $metastore;

  /**
   * Harvest plan storage repository service.
   *
   * @var \Drupal\harvest\Entity\HarvestPlanRepository
   */
  private HarvestPlanRepository $harvestPlanRepository;

  /**
   * Create.
   *
   * @inheritdoc
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('dkan.harvest.storage.database_table'),
      $container->get('dkan.metastore.service'),
      $container->get('dkan.harvest.harvest_plan_repository')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(
    FactoryInterface $storeFactory,
    MetastoreService $metastore,
    HarvestPlanRepository $harvestPlansRepository
  ) {
    $this->storeFactory = $storeFactory;
    $this->metastore = $metastore;
    $this->harvestPlanRepository = $harvestPlansRepository;
  }

  /**
   * Get all available harvest identifiers.
   *
   * @return array
   *   All ids.
   */
  public function getAllHarvestIds() {
    // Some calling code is very particular about the output being an array,
    // both as a return value here and after json_encode(). Since the entity
    // query returns a keyed array, json_encode() will think it's an object. We
    // don't want that, so we use array_values().
    return array_values($this->harvestPlanRepository->getAllHarvestPlanIds());
  }

  /**
   * Return a harvest plan.
   *
   * @param string $plan_id
   *   The harvest plan id.
   *
   * @return string|null
   *   The harvest plan, if any, or NULL.
   *
   * @throws \Exception
   */
  public function getHarvestPlan($plan_id) {
    return $this->harvestPlanRepository->getPlanJson($plan_id);
  }

  /**
   * Return a harvest plan object.
   *
   * @param string $plan_id
   *   The harvest plan id.
   *
   * @return object
   *   The harvest plan, if any, or NULL.
   *
   * @throws \Exception
   */
  public function getHarvestPlanObject($plan_id): object {
    return $this->harvestPlanRepository->getPlan($plan_id);
  }

  /**
   * Register a new harvest plan.
   *
   * @param object $plan
   *   The plan object. Must contain an 'identifier' propoerty. See
   *   components.schemas.harvestPlan within
   *   modules/harvest/docs/openapi_spec.json for the schema of a plan.
   *
   * @return string
   *   Identifier.
   *
   * @throws \Exception
   *   Exceptions may be thrown if validation fails.
   */
  public function registerHarvest($plan) {
    $this->validateHarvestPlan($plan);
    return $this->harvestPlanRepository->storePlan($plan, $plan->identifier);
  }

  /**
   * Deregister harvest.
   *
   * @param string $plan_id
   *   Plan identifier.
   *
   * @return bool
   *   Whether this happened successfully.
   */
  public function deregisterHarvest(string $plan_id) {
    if (in_array($plan_id, $this->getAllHarvestIds())) {
      // Remove all the support tables for this plan id.
      foreach ([
        'harvest_' . $plan_id . '_items',
        'harvest_' . $plan_id . '_hashes',
        'harvest_' . $plan_id . '_runs',
      ] as $table_name) {
        /** @var \Drupal\common\Storage\DatabaseTableInterface $store */
        $store = $this->storeFactory->getInstance($table_name);
        $store->destruct();
      }
      // Remove the plan id from the harvest_plans table.
      return $this->harvestPlanRepository->remove($plan_id);
    }
    return FALSE;
  }

  /**
   * Public.
   *
   * @todo the destruct method should be part of some interface.
   */
  public function revertHarvest($id) {
    $run_store = $this->storeFactory->getInstance("harvest_{$id}_runs");
    if (!method_exists($run_store, 'destruct')) {
      throw new \Exception('Storage of class ' . get_class($run_store) . ' does not implement destruct method.');
    }
    $run_store->destruct();
    $harvester = $this->getHarvester($id);
    return $harvester->revert();
  }

  /**
   * Public.
   */
  public function runHarvest($id) {
    $harvester = $this->getHarvester($id);

    $result = $harvester->harvest();
    if (is_null($result['status']['extracted_items_ids'] ?? NULL)) {
      throw new \Exception('No items found to extract, review your harvest plan.');
    }
    $result['status']['orphan_ids'] = $this->getOrphanIdsFromResult($id, $result['status']['extracted_items_ids']);
    $this->processOrphanIds($result['status']['orphan_ids']);

    $run_store = $this->storeFactory->getInstance("harvest_{$id}_runs");
    $current_time = time();
    $run_store->store(json_encode($result), "{$current_time}");

    return $result;
  }

  /**
   * Get Harvest Run Info.
   *
   * @return mixed
   *   FALSE if no matching runID is found.
   */
  public function getHarvestRunInfo($id, $runId) {
    $allRuns = $this->getAllHarvestRunInfo($id);
    $found = array_search($runId, $allRuns);

    if ($found !== FALSE) {
      $run_store = $this->storeFactory->getInstance("harvest_{$id}_runs");
      return $run_store->retrieve($runId);
    }

    return FALSE;
  }

  /**
   * Public.
   */
  public function getAllHarvestRunInfo($id) {
    $run_store = $this->storeFactory->getInstance("harvest_{$id}_runs");
    $runs = $run_store->retrieveAll();
    return $runs;
  }

  /**
   * Get a harvest's most recent run identifier.
   *
   * Since the run record id is a timestamp, we can sort on the id.
   *
   * @param string $planId
   *   The harvest identifier.
   *
   * @return string
   *   The most recent harvest run record identifier.
   */
  public function getLastHarvestRunId(string $planId) {
    $runs = $this->getAllHarvestRunInfo($planId);
    rsort($runs);
    return reset($runs);
  }

  /**
   * Publish a harvest.
   *
   * @param string $harvestId
   *   Harvest identifier.
   *
   * @return array
   *   The uuids of the datasets to publish.
   */
  public function publish(string $harvestId): array {
    return $this->bulkUpdateStatus($harvestId, 'publish');

  }

  /**
   * Archive a harvest.
   *
   * @param string $harvestId
   *   Harvest identifier.
   *
   * @return array
   *   The uuids of the published datasets.
   */
  public function archive(string $harvestId): array {
    return $this->bulkUpdateStatus($harvestId, 'archive');
  }

  /**
   * Archive a harvest.
   *
   * @param string $harvestId
   *   Harvest identifier.
   * @param string $method
   *   Metastore update status method - "archive" or "publish" available.
   *
   * @return array
   *   The uuids of the published datasets.
   */
  protected function bulkUpdateStatus(string $harvestId, string $method): array {
    if (!in_array($method, ['archive', 'publish'])) {
      throw new \OutOfRangeException("Method {$method} does not exist");
    }

    $lastRunId = $this->getLastHarvestRunId($harvestId);
    $lastRunInfo = json_decode($this->getHarvestRunInfo($harvestId, $lastRunId));
    $status = $lastRunInfo->status ?? NULL;
    if (!isset($status->extracted_items_ids)) {
      return [];
    }

    $updated = [];
    foreach ($status->extracted_items_ids as $datasetId) {
      // $this->publishHarvestedDataset() will return true if $datasetId
      // could be successfully published.
      $updated[] = $this->setDatasetStatus($status, $datasetId, $method) ? $datasetId : NULL;
    }

    return array_values(array_filter($updated));
  }

  /**
   * Use metastore service to publish a harvested item.
   *
   * @param object $runInfoStatus
   *   Status object with run information.
   * @param string $datasetId
   *   ID to DKAN dataset.
   * @param string $method
   *   Metastore update status method - "archive" or "publish" available.
   *
   * @return bool
   *   Whether or not publish action was successful.
   */
  protected function setDatasetStatus($runInfoStatus, string $datasetId, string $method): bool {
    try {
      return isset($runInfoStatus->load) &&
        $runInfoStatus->load->{$datasetId} &&
        $runInfoStatus->load->{$datasetId} != 'FAILURE' &&
        $this->metastore->$method('dataset', $datasetId);
    }
    catch (\Exception $e) {
      $this->error("Error applying method {$method} to dataset {$datasetId}: {$e->getMessage()}");
      return FALSE;
    }
  }

  /**
   * Proxy to Etl Factory to validate harvest plan.
   *
   * @param object $plan
   *   Plan.
   *
   * @return bool
   *   TRUE if harvest plan validates. Throws exception otherwise.
   */
  public function validateHarvestPlan($plan): bool {
    return Factory::validateHarvestPlan($plan);
  }

  /**
   * Get a DKAN harvester instance.
   *
   * @param string $plan_id
   *   Harvester ID.
   *
   * @return \Harvest\Harvester
   *   Harvester object.
   */
  private function getHarvester(string $plan_id): Harvester {
    $harvest_plan = $this->harvestPlanRepository->getPlan($plan_id);
    $item_store = $this->storeFactory->getInstance("harvest_{$plan_id}_items");
    $hash_store = $this->storeFactory->getInstance("harvest_{$plan_id}_hashes");
    return $this->getDkanHarvesterInstance($harvest_plan, $item_store, $hash_store);
  }

  /**
   * Get the harvester from the harvester library.
   */
  protected function getDkanHarvesterInstance($harvestPlan, $item_store, $hash_store): Harvester {
    return new Harvester(new Factory($harvestPlan, $item_store, $hash_store));
  }

}
