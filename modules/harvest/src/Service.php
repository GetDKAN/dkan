<?php

namespace Drupal\harvest;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Harvest\Harvester as DkanHarvester;
use Contracts\BulkRetrieverInterface;
use Contracts\FactoryInterface;
use Contracts\StorerInterface;
use Drupal\metastore\Service as Metastore;
use Harvest\ETL\Factory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service.
 */
class Service implements ContainerInjectionInterface {

  private $storeFactory;

  /**
   * DKAN metastore service.
   *
   * @var \Drupal\metastore\Service
   */
  private $metastore;

  /**
   * Create.
   *
   * @inheritdoc
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get("dkan.harvest.storage.database_table"),
      $container->get('dkan.metastore.service')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(FactoryInterface $storeFactory, Metastore $metastore) {
    $this->storeFactory = $storeFactory;
    $this->metastore = $metastore;
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
      return $store->retrieveAll();
    }
    throw new \Exception("The store created by {get_class($this->storeFactory)} does not implement {BulkRetrieverInterface::class}");
  }

  /**
   * Return a harvest plan.
   *
   * @param string $plan_id
   *   The harvest plan id.
   *
   * @return mixed
   *   The harvest plan, if any, or NULL.
   *
   * @throws \Exception
   */
  public function getHarvestPlan($plan_id) {
    $store = $this->storeFactory->getInstance("harvest_plans");

    if ($store instanceof BulkRetrieverInterface) {
      return $store->retrieve($plan_id);
    }
    throw new \Exception("The store created by {get_class($this->storeFactory)} does not implement {RetrieverInterface::class}");
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
   *
   * @todo the destroy method should be part of some interface.
   */
  public function revertHarvest($id) {
    $run_store = $this->storeFactory->getInstance("harvest_{$id}_runs");
    if (!method_exists($run_store, "destroy")) {
      throw new \Exception("Storage of class " . get_class($run_store) . " does not implement destroy method.");
    }
    $run_store->destroy();
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

    $this->checkForRemovedDatasets($id, $result);

    return $result;
  }

  /**
   * Clean up any datasets removed since the previous harvest run.
   *
   * @param string $id
   *   Harvest identifier.
   * @param array $result
   *   Result array.
   */
  private function checkForRemovedDatasets(string $id, array $result) {
    $harvests = $this->getAllHarvestRunInfo($id);

    // Without a previous harvest run, there's nothing to clean up.
    if (count($harvests) <= 1)  {
      return;
    }

    // Get the latest and previously extracted items ids.
    $latestExtracted = $result['status']['extracted_items_ids'];

    array_pop($harvests);
    $previousRun = end($harvests);
    $previousInfo = json_decode($this->getHarvestRunInfo($id, $previousRun));
    $previouslyExtracted = $previousInfo->status->extracted_items_ids;

    $removed = array_diff($previouslyExtracted, $latestExtracted);
    print_r("\nremoved : \n");
    print_r($removed);
  }

  private function removeDatasets(array $uuids) {
    foreach ($uuids as $uuid) {

    }
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
   * Get a harvest's most recent run identifier, i.e. timestamp.
   *
   * @param string $id
   *   The harvest identifier.
   */
  private function getLastHarvestRunInfo(string $id) {
    $runs = $this->getAllHarvestRunInfo($id);
    rsort($runs);
    return reset($runs);
  }

  /**
   * Publish a harvest.
   *
   * @param string $id
   *   Harvest identifier.
   */
  public function publish(string $id) {
    $publishedIdentifiers = [];

    $lastRunId = $this->getLastHarvestRunInfo($id);
    $lastRunInfoJsonString = $this->getHarvestRunInfo($id, $lastRunId);
    $lastRunInfoObj = json_decode($lastRunInfoJsonString);

    if (!isset($lastRunInfoObj->status->extracted_items_ids)) {
      return $publishedIdentifiers;
    }

    foreach ($lastRunInfoObj->status->extracted_items_ids as $uuid) {
      if (isset($lastRunInfoObj->status->load) &&
        $lastRunInfoObj->status->load->{$uuid} &&
        $lastRunInfoObj->status->load->{$uuid} != "FAILURE") {
        $publishedIdentifiers[] = $this->metastore->publish('dataset', $uuid);
      }
    }

    return $publishedIdentifiers;
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
    return $this->getDkanHarvesterInstance($harvestPlan, $item_store, $hash_store);
  }

  /**
   * Protected.
   *
   * @codeCoverageIgnore
   */
  protected function getDkanHarvesterInstance($harvestPlan, $item_store, $hash_store) {
    return new DkanHarvester(new Factory($harvestPlan, $item_store, $hash_store));
  }

}
