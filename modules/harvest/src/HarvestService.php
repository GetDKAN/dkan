<?php

namespace Drupal\harvest;

use Contracts\FactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\harvest\Entity\HarvestPlanRepository;
use Drupal\harvest\Entity\HarvestRunRepository;
use Drupal\harvest\Storage\HarvestHashesDatabaseTableFactory;
use Drupal\metastore\MetastoreService;
use Drupal\harvest\ETL\Factory;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Main DKAN Harvester service.
 *
 * Import groups of datasets from an external source, and manage existing
 * harvest plans and their dependent datasets.
 */
class HarvestService implements ContainerInjectionInterface {

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
   * Harvest hash database table factory service.
   */
  private HarvestHashesDatabaseTableFactory $hashesStoreFactory;

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
   * Harvest run entity repository service.
   *
   * @var \Drupal\harvest\Entity\HarvestRunRepository
   */
  public HarvestRunRepository $runRepository;

  /**
   * DKAN logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  private LoggerInterface $logger;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('dkan.harvest.storage.database_table'),
      $container->get('dkan.harvest.storage.hashes_database_table'),
      $container->get('dkan.metastore.service'),
      $container->get('dkan.harvest.harvest_plan_repository'),
      $container->get('dkan.harvest.storage.harvest_run_repository'),
      $container->get('dkan.harvest.logger_channel')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(
    FactoryInterface $storeFactory,
    HarvestHashesDatabaseTableFactory $hashesStoreFactory,
    MetastoreService $metastore,
    HarvestPlanRepository $harvestPlansRepository,
    HarvestRunRepository $runRepository,
    LoggerInterface $loggerChannel,
  ) {
    $this->storeFactory = $storeFactory;
    $this->hashesStoreFactory = $hashesStoreFactory;
    $this->metastore = $metastore;
    $this->harvestPlanRepository = $harvestPlansRepository;
    $this->runRepository = $runRepository;
    $this->logger = $loggerChannel;
  }

  /**
   * Get harvest plan identifiers.
   *
   * @param bool $has_run_record
   *   If true, return only harvest IDs that have been run at least once.
   *
   * @return string[]
   *   Array of Harvest Plan IDs.
   */
  public function getAllHarvestIds(bool $has_run_record = FALSE): array {
    // Some calling code is very particular about the output being an array,
    // both as a return value here and after json_encode(). Since the entity
    // query returns a keyed array, json_encode() will think it's an object. We
    // don't want that, so we use array_values().
    return array_values(
      $has_run_record ?
        $this->runRepository->getUniqueHarvestPlanIds() :
        $this->harvestPlanRepository->getAllHarvestPlanIds()
    );
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
   * @return object|null
   *   The harvest plan, if any, or NULL.
   *
   * @throws \Exception
   */
  public function getHarvestPlanObject($plan_id): ?object {
    return $this->harvestPlanRepository->getPlan($plan_id);
  }

  /**
   * Register a new harvest plan or update an existing one.
   *
   * @param object $plan
   *   The plan object. Must contain an 'identifier' property. See
   *   modules/harvest/schema/schema.json for the schema of a harvest plan.
   *
   * @return string
   *   The identifier for the harvest plan.
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
    if (in_array($plan_id, $this->harvestPlanRepository->getAllHarvestPlanIds())) {
      // Remove all the support tables for this plan id.
      $this->storeFactory->getInstance('harvest_' . $plan_id . '_items')->destruct();
      $this->hashesStoreFactory->getInstance($plan_id)->destruct();
      $this->runRepository->destructForPlanId($plan_id);
      // Remove the plan id from the harvest_plans table.
      return $this->harvestPlanRepository->remove($plan_id);
    }
    return FALSE;
  }

  /**
   * Public.
   */
  public function revertHarvest($id) {
    $this->runRepository->destructForPlanId($id);
    $harvester = $this->getHarvester($id);
    return $harvester->revert();
  }

  /**
   * Public.
   */
  public function runHarvest($plan_id) {
    $harvester = $this->getHarvester($plan_id);

    $timestamp = (string) time();
    $result = $harvester->harvest();

    if (empty($result['status']['extracted_items_ids'])) {
      throw new \Exception('No items found to extract, review your harvest plan.');
    }
    $result['status']['orphan_ids'] =
      $this->getOrphanIdsFromResult($plan_id, $result['status']['extracted_items_ids']);
    $this->processOrphanIds($result['status']['orphan_ids']);
    // For legacy reasons, the identifier is the timestamp.
    $result['identifier'] = $timestamp;
    $this->runRepository->storeRun($result, $plan_id, $timestamp);

    return $result;
  }

  /**
   * Get Harvest Run Info.
   *
   * @param string $plan_id
   *   The harvest plan ID.
   * @param string $timestamp
   *   The timestamp of the harvest_run.
   *
   * @return bool|string
   *   JSON-encoded run information for the given run, or FALSE if no matching
   *   runID is found.
   */
  public function getHarvestRunInfo(string $plan_id, string $timestamp): bool|string {
    if ($info = $this->runRepository->retrieveRunJson($plan_id, $timestamp)) {
      return $info;
    }
    return FALSE;
  }

  /**
   * Get the results of a harvest run.
   *
   * @param string $plan_id
   *   Harvest plan ID.
   * @param string|null $timestamp
   *   Harvest run timestamp.
   *
   * @return array
   *   Array of status info from the run.
   */
  public function getHarvestRunResult(string $plan_id, ?string $timestamp = NULL): array {
    if (!is_null($timestamp)) {
      // This one has to keep using the loadEntity method as it may be looking
      // up something other than the most recent run.
      $entity = $this->runRepository->loadEntity($plan_id, $timestamp);
    }
    else {
      $entity = $this->runRepository->loadRunByPlan($plan_id);
    }

    return (!empty($entity)) ? $entity->toResult() : [];
  }

  /**
   * Retrieve all run results for a given plan.
   *
   * @param string $plan_id
   *   The harvest plan identifier.
   *
   * @return array
   *   JSON-encoded result arrays, keyed by harvest run identifier.
   *
   * @deprecated Gather run IDs from getRunIdsForHarvest() and access specific
   *   information based on those IDs.
   *
   * @see self::getRunIdsForHarvest()
   * @see self::getHarvestRunInfo()
   */
  public function getAllHarvestRunInfo(string $plan_id): array {
    return $this->runRepository->retrieveAllRunsJson($plan_id);
  }

  /**
   * Retrieve harvest run IDs for a given harvest plan.
   *
   * @param string $plan_id
   *   The harvest plan identifier.
   *
   * @return array
   *   Harvest run identifiers, keyed by identifier.
   */
  public function getRunIdsForHarvest(string $plan_id): array {
    return $this->runRepository->retrieveAllRunIds($plan_id);
  }

  /**
   * Get a harvest's most recent run id. Passthrough for HarvestRunRepository.
   *
   * @param string $plan_id
   *   The harvest plan identifier.
   *
   * @return string
   *   The entity id of the most recent harvest run.
   *
   * @deprecated in dkan:2.19.11 and is removed from dkan:3.0.0 Use runStorage::load().
   */
  public function getLastHarvestRunId(string $plan_id): string {
    return $this->runRepository->getLastHarvestRunId($plan_id);
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

    $lastRunId = $this->runRepository->getLastHarvestRunId($harvestId);
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
   *   Whether status change action was successful.
   */
  protected function setDatasetStatus($runInfoStatus, string $datasetId, string $method): bool {
    try {
      return isset($runInfoStatus->load) &&
        $runInfoStatus->load->{$datasetId} &&
        $runInfoStatus->load->{$datasetId} != 'FAILURE' &&
        $this->metastore->$method('dataset', $datasetId);
    }
    catch (\Exception $e) {
      $this->logger->error("Error applying method {$method} to dataset {$datasetId}: {$e->getMessage()}");
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
   *
   * @throws \Exception
   *   Thrown on validation failure.
   *
   * @see \Drupal\harvest\ETL\Factory::validateHarvestPlan()
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
   * @return \Drupal\harvest\Harvester
   *   Harvester object.
   */
  private function getHarvester(string $plan_id): Harvester {
    return $this->getDkanHarvesterInstance(
      $this->harvestPlanRepository->getPlan($plan_id),
      $this->storeFactory->getInstance('harvest_' . $plan_id . '_items'),
      $this->hashesStoreFactory->getInstance($plan_id)
    );
  }

  /**
   * Get the harvester from the harvester library.
   */
  protected function getDkanHarvesterInstance($harvestPlan, $item_store, $hash_store): Harvester {
    return new Harvester(new Factory($harvestPlan, $item_store, $hash_store));
  }

}
