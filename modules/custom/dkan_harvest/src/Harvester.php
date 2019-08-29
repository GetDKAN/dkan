<?php

namespace Drupal\dkan_harvest;

use Harvest\Harvester as DkanHarvester;
use Contracts\BulkRetrieverInterface;
use Contracts\FactoryInterface;
use Contracts\StorerInterface;
use Drupal\dkan_common\Service\JsonUtil;
use Harvest\ETL\Factory;

/**
 * Harvester.
 */
class Harvester {

  private $storeFactory;

  /**
   * Constructor.
   */
  public function __construct(FactoryInterface $storeFactory) {
    $this->storeFactory = $storeFactory;
  }

  /**
   * Get all available harvests.
   *
   * @return array
   *   All ids.
   */
  public function getAllHarvestIds() {
    $store = $this->storeFactory->getInstance("harvest_plans");

    if ($store instanceof BulkRetrieverInterface) {
      return array_keys($store->retrieveAll());
    }
    throw new \Exception("The store created by {get_class($this->storeFactory)} does not implement {BulkRetrieverInterface::class}");
  }

  /**
   * Register a new harvest plan.
   *
   * @param object $plan
   *   usually an \stdClass representation.
   *
   * @return string
   *   Identifier.
   *
   * @throws \Exception
   *   Exceptions may be thrown if validation fails.
   */
  public function registerHarvest($plan) {

    $this->validateHarvestPlan($plan);

    $store = $this->storeFactory->getInstance("harvest_plans");

    if ($store instanceof StorerInterface) {
      return $store->store(json_encode($plan), $plan->identifier);
    }
    throw new \Exception("The store created by {get_class($this->storeFactory)} does not implement {StorerInterface::class}");
  }

  /**
   * Deregister harvest.
   *
   * @param string $id
   *   Id.
   *
   * @return bool
   *   Boolean.
   */
  public function deregisterHarvest(string $id) {
    $this->revertHarvest($id);

    $plan_store = $this->storeFactory->getInstance("harvest_plans");

    return $plan_store->remove($id);
  }

  /**
   * Public.
   */
  public function revertHarvest($id) {
    $harvester = $this->getHarvester($id);
    return $harvester->revert();
  }

  /**
   * Public.
   */
  public function runHarvest($id) {
    $harvester = $this->getHarvester($id);

    $result = $harvester->harvest();

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
    return isset($allRuns[$runId]) ? $allRuns[$runId] : FALSE;
  }

  /**
   * Public.
   */
  public function getAllHarvestRunInfo($id) {
    $util = new JsonUtil();
    $run_store = $this->storeFactory->getInstance("harvest_{$id}_runs");
    return $util->decodeArrayOfJson(
          $run_store->retrieveAll()
    );
  }

  /**
   * Proxy to Etl Factory to validate harvest plan.
   *
   * @param object $plan
   *   Plan.
   *
   * @return bool
   *   Throws exceptions instead of false it seems.
   */
  public function validateHarvestPlan($plan) {
    return Factory::validateHarvestPlan($plan);
  }

  /**
   * Private.
   */
  private function getHarvester($id) {
    $plan_store = $this->storeFactory->getInstance("harvest_plans");
    $harvestPlan = json_decode($plan_store->retrieve($id));
    $item_store = $this->storeFactory->getInstance("harvest_{$id}_items");
    $hash_store = $this->storeFactory->getInstance("harvest_{$id}_hashes");
    return new DkanHarvester(new Factory($harvestPlan, $item_store, $hash_store));
  }

}
