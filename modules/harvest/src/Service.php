<?php

namespace Drupal\harvest;

use Contracts\BulkRetrieverInterface;
use Contracts\FactoryInterface;
use Contracts\StorerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\common\LoggerTrait;
use Drupal\metastore\Service as Metastore;
use Harvest\ETL\Factory;
use Harvest\Harvester as DkanHarvester;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service.
 */
class Service implements ContainerInjectionInterface {

  use LoggerTrait;
  use OrphanDatasetsProcessor;

  private $storeFactory;

  /**
   * DKAN metastore service.
   *
   * @var \Drupal\metastore\Service
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
  public function __construct(FactoryInterface $storeFactory, Metastore $metastore, EntityTypeManager $entityTypeManager) {
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
   * @param string $id
   *   Harvest identifier.
   *
   * @return array
   *   The uuids of the published datasets.
   */
  public function publish(string $id): array {

    $lastRunInfoObj = $this->getLastRunInfoObj($id);
    if (!isset($lastRunInfoObj->status->extracted_items_ids)) {
      return [];
    }

    return $this->publishHelper($id, $lastRunInfoObj->status);
  }

  /**
   * Private.
   */
  private function getLastRunInfoObj(string $harvestId) {
    $lastRunId = $this->getLastHarvestRunId($harvestId);
    $lastRunInfoJsonString = $this->getHarvestRunInfo($harvestId, $lastRunId);
    return json_decode($lastRunInfoJsonString);
  }

  /**
   * Private.
   */
  private function publishHelper(string $harvestId, $lastRunStatus): array {
    $publishedIdentifiers = [];

    foreach ($lastRunStatus->extracted_items_ids as $uuid) {
      try {
        if ($this->metastorePublishHelper($lastRunStatus, $uuid)) {
          $publishedIdentifiers[] = $uuid;
        }
      }
      catch (\Exception $e) {
        $this->error("Error publishing dataset {$uuid} in harvest {$harvestId}: {$e->getMessage()}");
      }
    }

    return $publishedIdentifiers;
  }

  /**
   * Private.
   */
  private function metastorePublishHelper($runInfoStatus, string $uuid): bool {
    return isset($runInfoStatus->load) &&
      $runInfoStatus->load->{$uuid} &&
      $runInfoStatus->load->{$uuid} != 'FAILURE' &&
      $this->metastore->publish('dataset', $uuid);
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
