<?php

namespace Drupal\harvest\Entity;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\harvest\HarvestRunInterface;

/**
 * Convenient queries for harvest_run entities.
 *
 * Avoid using this repository from anywhere but HarvestService.
 *
 * @see \Drupal\harvest\HarvestService
 *
 * @internal
 */
class HarvestRunRepository {

  /**
   * Entity storage service for the harvest_run entity type.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected EntityStorageInterface $runStorage;

  /**
   * Database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  private Connection $connection;

  /**
   * Harvest run entity definition service.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  private EntityTypeInterface $entityTypeDefinition;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager service.
   */
  public function __construct(
    Connection $connection,
    EntityTypeManagerInterface $entityTypeManager
  ) {
    $this->connection = $connection;
    $this->runStorage = $entityTypeManager->getStorage('harvest_run');
    $this->entityTypeDefinition = $entityTypeManager->getDefinition('harvest_run');
  }

  /**
   * Destroy all the run records for a given harvest plan ID.
   *
   * Analogous to \Drupal\common\Storage\DatabaseTableInterface::destruct().
   *
   * @param string $plan_id
   *   The plan ID for which to destroy all records.
   *
   * @see \Drupal\common\Storage\DatabaseTableInterface::destruct()
   */
  public function destructForPlanId(string $plan_id) {
    if ($ids = $this->runStorage->getQuery()
      ->condition('harvest_plan_id', $plan_id)
      ->accessCheck(FALSE)
      ->execute()
    ) {
      foreach ($this->runStorage->loadMultiple($ids) as $entity) {
        $entity->delete();
      }
    }
  }

  /**
   * Store run data.
   *
   * Extracts and unsets any structured values from $run_data, and then stores
   * the remainder as JSON in the data field.
   *
   * @param array $run_data
   *   Run data. Usually the result returned by Harvester::harvest().
   * @param string $plan_id
   *   The plan identifier.
   * @param string $timestamp
   *   The run timestamp.
   *
   * @return string
   *   The run identifier.
   *
   * @see \Harvest\Harvester::harvest()
   *
   * @todo Eventually all the subsystems will be able to understand the entity
   *   rather than needing conversion to and from the array format.
   */
  public function storeRun(array $run_data, string $plan_id, string $timestamp): string {
    $field_values = [
      'timestamp' => (int) $timestamp,
      'harvest_plan_id' => $plan_id,
    ];
    $field_values['extract_status'] = $run_data['status']['extract'] ?? 'FAILURE';
    unset($run_data['status']['extract']);

    $field_values['extracted_uuid'] = $run_data['status']['extracted_items_ids'] ?? [];
    unset($run_data['status']['extracted_items_ids']);

    $field_values['orphan_uuid'] = $run_data['status']['orphan_ids'] ?? [];
    unset($run_data['status']['orphan_ids']);

    $field_values['load_new_uuid'] = [];
    $field_values['load_updated_uuid'] = [];
    $field_values['load_unchanged_uuid'] = [];

    foreach ($run_data['status']['load'] ?? [] as $uuid => $status) {
      switch ($status) {
        case 'NEW':
          $field_values['load_new_uuid'][] = $uuid;
          unset($run_data['status']['load'][$uuid]);
          break;

        case 'UPDATED':
          $field_values['load_updated_uuid'][] = $uuid;
          unset($run_data['status']['load'][$uuid]);
          break;

        case 'UNCHANGED':
          $field_values['load_unchanged_uuid'][] = $uuid;
          unset($run_data['status']['load'][$uuid]);
      }
    }

    // JSON encode remaining run data.
    $field_values['data'] = json_encode($run_data);

    return $this->writeEntity($field_values, $plan_id, $timestamp);
  }

  /**
   * Retrieve the JSON-encoded data for the given plan and run IDs.
   *
   * @param string $plan_id
   *   The harvest plan identifier.
   * @param string|null $timestamp
   *   The harvest run timestamp. No longer used.  Retained for BC.
   *
   * @return string|null
   *   JSON-encoded run result data, or NULL if none could be found.
   */
  public function retrieveRunJson(string $plan_id, $timestamp = NULL): ?string {
    $run_ids = $this->retrieveAllRunIds($plan_id);
    $run_id = reset($run_ids);
    if ($run_id) {
      $run_entity = $this->runStorage->load($run_id);
      return json_encode($run_entity->toResult());
    }
    return NULL;
  }

  /**
   * Retrieve all harvest run IDs for a given harvest plan.
   *
   * @param string $plan_id
   *   The harvest plan identifier.
   *
   * @return array
   *   All harvest run identifiers, keyed by identifier.
   */
  public function retrieveAllRunIds(string $plan_id): array {
    return $this->runStorage->getQuery()
      ->condition('harvest_plan_id', $plan_id)
      ->sort('id', 'DESC')
      ->accessCheck(FALSE)
      ->execute();
  }

  /**
   * Retrieve all run results for a given plan.
   *
   * @param string $plan_id
   *   The harvest plan identifier.
   *
   * @return array
   *   JSON-encoded result arrays, keyed by harvest run identifier.
   */
  public function retrieveAllRunsJson(string $plan_id): array {
    $runs = [];
    if ($ids = $this->retrieveAllRunIds($plan_id)) {
      /** @var \Drupal\harvest\HarvestRunInterface $entity */
      foreach ($this->runStorage->loadMultiple($ids) as $entity) {
        $runs[$entity->id()] = json_encode($entity->toResult());
      }
    }
    return $runs;
  }

  /**
   * Get all the harvest plan ids available in the harvest runs table.
   *
   * @return array
   *   All the harvest plan ids present in the harvest runs table, as both key
   *   and value.
   */
  public function getUniqueHarvestPlanIds(): array {
    return array_keys(
      $this->connection
        ->select($this->entityTypeDefinition->getBaseTable(), 'hr')
        ->fields('hr', ['harvest_plan_id'])
        ->distinct()
        ->execute()
        ->fetchAllAssoc('harvest_plan_id')
    );
  }

  /**
   * Get the extracted UUIDs from the given harvest run.
   *
   * @param string $planId
   *   The harvest plan ID.  deprecated: no longer needed but kept for BC.
   * @param string $runId
   *   The harvest_run entity id.
   *
   * @return string[]
   *   Array of UUIDs, keyed by UUID. Note that these are UUIDs by convention;
   *   they could be any string value.
   */
  public function getExtractedUuids(string $planId, string $runId): array {
    $extracted = [];
    if ($entity = $this->runStorage->load($runId)) {
      foreach ($entity->get('extracted_uuid')->getValue() as $field) {
        $uuid = $field['value'];
        $extracted[$uuid] = $uuid;
      }
    }
    return $extracted;
  }

  /**
   * Helper method to load a harvest_run entity given an Plan ID and timestamp.
   *
   * @param string $plan_id
   *   Plan ID.
   * @param string $timestamp
   *   The timestamp for the run. Formerly the id.
   *
   * @return \Drupal\harvest\HarvestRunInterface|\Drupal\Core\Entity\EntityInterface|null
   *   The loaded entity or NULL if none could be loaded.
   */
  public function loadEntity(string $plan_id, string $timestamp): ?HarvestRunInterface {
    if ($ids = $this->runStorage->getQuery()
      ->condition('timestamp', $timestamp)
      ->condition('harvest_plan_id', $plan_id)
      ->sort('id', 'DESC')
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute()
    ) {
      return $this->runStorage->load(reset($ids));
    }
    return NULL;
  }

  /**
   * Helper method to load the most recent harvest_run entity given a plan ID.
   *
   * @param string $harvest_plan_id
   *   Plan ID.
   *
   * @return \Drupal\harvest\HarvestRunInterface|null
   *   The loaded harvest_run entity or NULL if none could be loaded.
   */
  public function loadRunByPlan($harvest_plan_id): ?HarvestRunInterface {
    $run_id = $this->getLastHarvestRunId($harvest_plan_id);
    return ($run_id) ? $this->runStorage->load($run_id) : NULL;
  }

  /**
   * Get a harvest's most recent run identifier.
   *
   * Since the run record id is a timestamp, we can sort on the id.
   *
   * @param string $plan_id
   *   The harvest plan identifier.
   *
   * @return string
   *   The entity id of the most recent harvest run.
   */
  public function getLastHarvestRunId(string $plan_id): string {
    $run_ids = $this->retrieveAllRunIds($plan_id);
    return reset($run_ids);
  }

  /**
   * Write a harvest_run entity, updating or saving as needed.
   *
   * @param array $field_values
   *   Structured data ready to send to entity_storage->create().
   * @param string $plan_id
   *   Harvest plan identifier.
   * @param mixed $timestamp
   *   Harvest run timestamp.
   *
   * @return string
   *   Harvest run id.
   */
  public function writeEntity(array $field_values, string $plan_id, mixed $timestamp) {
    $timestamp = (int) $timestamp;
    /** @var \Drupal\harvest\HarvestRunInterface $entity */
    $entity = $this->loadEntity($plan_id, $timestamp);
    if ($entity) {
      // Modify entity.
      foreach ($field_values as $key => $value) {
        $entity->set($key, $value);
      }
      $field_values['id'] = $entity->id();
    }
    else {
      // Create new entity.
      $entity = $this->runStorage->create($field_values);
    }
    $entity->save();
    return $entity->id();
  }

}
