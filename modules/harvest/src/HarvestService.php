<?php

namespace Drupal\harvest;

use Contracts\BulkRetrieverInterface;
use Contracts\FactoryInterface;
use Contracts\StorerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\common\LoggerTrait;
use Drupal\metastore\MetastoreService;
use Harvest\ETL\Factory;
use Harvest\Harvester as DkanHarvester;
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
   */
  private $storeFactory;

  /**
   * DKAN metastore service.
   *
   * @var \Drupal\metastore\MetastoreService
   */
  private $metastore;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  private $entityTypeManager;

  /**
   * Create.
   *
   * @inheritdoc
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get("dkan.harvest.storage.database_table"),
      $container->get('dkan.metastore.service'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(FactoryInterface $storeFactory, MetastoreService $metastore, EntityTypeManager $entityTypeManager) {
    $this->storeFactory = $storeFactory;
    $this->metastore = $metastore;
    $this->entityTypeManager = $entityTypeManager;
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
    try {
      $this->revertHarvest($id);
    }
    catch (\Exception $e) {
    }

    $plan_store = $this->storeFactory->getInstance("harvest_plans");

    return $plan_store->remove($id);
  }

  /**
   * Public.
   *
   * @todo the destruct method should be part of some interface.
   */
  public function revertHarvest($id) {
    $run_store = $this->storeFactory->getInstance("harvest_{$id}_runs");
    if (!method_exists($run_store, "destruct")) {
      throw new \Exception("Storage of class " . get_class($run_store) . " does not implement destruct method.");
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
    if (is_null($result['status']['extracted_items_ids'])) {
      throw new \Exception("No items found to extract, review your harvest plan.");
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
   * Get a harvest's most recent run identifier, i.e. timestamp.
   *
   * @param string $id
   *   The harvest identifier.
   */
  private function getLastHarvestRunId(string $id) {
    $runs = $this->getAllHarvestRunInfo($id);
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
   *   Throws exceptions instead of false it seems.
   */
  public function validateHarvestPlan($plan) {
    return Factory::validateHarvestPlan($plan);
  }

  /**
   * Get a DKAN harvester instance.
   *
   * @param string $id
   *   Harvester ID.
   *
   * @return \Harvest\Harvester
   *   Harvester object.
   */
  private function getHarvester(string $id) {
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
