<?php

namespace Drupal\harvest\Entity;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\harvest\HarvestRunInterface;

class HarvestRunRepository {

  /**
   * Entity storage service for the harvest_run entity type.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private EntityStorageInterface $runStorage;

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
   * @param $plan_id
   *   The plan ID for which to destroy all records.
   *
   * @see \Drupal\common\Storage\DatabaseTableInterface::destruct()
   */
  public function destructForPlanId($plan_id) {
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
   * Unsets any structured values from $run_data, and then stores the remainder
   * as JSON in the data field.
   *
   * @param array $run_data
   *   Run data. Usually the result returned by Harvester::harvest().
   * @param string $plan_id
   *   The plan identifier.
   * @param string $run_id
   *   The run identifier, which is also a timestamp.
   *
   * @return string
   *   The run identifier.
   *
   * @see \Harvest\Harvester::harvest()
   *
   * @todo Eventually all the subsystems will be able to understand the entity
   *   rather than needing conversion to and from the array format.
   */
  public function storeRun(array $run_data, string $plan_id, string $run_id): string {
    $extract_status = $run_data['status']['extract'] ?? 'FAILURE';
    unset($run_data['status']['extract']);

    $extracted_uuids = $run_data['status']['extracted_items_ids'] ?? [];
    unset($run_data['status']['extracted_items_ids']);

    $orphan_uuids = $run_data['status']['orphan_ids'] ?? [];
    unset($run_data['status']['orphan_ids']);

    $load_new_uuids = $load_updated_uuids = $load_unchanged_uuids = [];
    foreach ($run_data['status']['load'] ?? [] as $uuid => $status) {
      switch ($status) {
        case 'NEW':
          $load_new_uuids[] = $uuid;
          unset($run_data['status']['load'][$uuid]);
          break;

        case 'UPDATED':
          $load_updated_uuids[] = $uuid;
          unset($run_data['status']['load'][$uuid]);
          break;

        case 'UNCHANGED':
          $load_unchanged_uuids[] = $uuid;
          unset($run_data['status']['load'][$uuid]);
      }
    }

    return $this->writeEntity([
      'id' => $run_id,
      'harvest_plan_id' => $plan_id,
      'data' => json_encode($run_data),
      'extract_status' => $extract_status,
      'extracted_uuid' => $extracted_uuids,
      'orphan_uuid' => $orphan_uuids,
      'load_new_uuid' => $load_new_uuids,
      'load_updated_uuid' => $load_updated_uuids,
      'load_unchanged_uuid' => $load_unchanged_uuids,
    ], $plan_id, $run_id);
  }

  /**
   * Store JSON-encoded run data.
   *
   * @param string $run_data
   *   JSON-encoded run data.
   * @param string $plan_id
   *   The harvest plan identifier.
   * @param string $run_id
   *   The harvest run identifier.
   *
   * @return string
   *   The run identifier.
   */
  public function storeRunJson(string $run_data, string $plan_id, string $run_id): string {
    return $this->storeRun(json_decode($run_data), $plan_id, $run_id);
  }

  /**
   * Retrieve the JSON-encoded data for the given plan and run IDs.
   *
   * @param string $plan_id
   *   The harvest plan identifier.
   * @param string $run_id
   *   The harvest run identifier.
   *
   * @return string|null
   *   JSON-encoded run result data, or NULL if none could be found.
   */
  public function retrieveRunJson(string $plan_id, string $run_id): ?string {
    if ($entity = $this->loadEntity($plan_id, $run_id)) {
      return json_encode($entity);
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
      /** @var HarvestRunInterface $entity */
      foreach ($this->runStorage->loadMultiple($ids) as $entity) {
        $runs[$entity->id()] = json_encode($entity->toResult());
      }
    }
    return $runs;
  }

  /**
   * Get all the harvet plan ids available in the harvest runs table.
   *
   * @return array
   *   All the harvest plan ids present in the harvest runs table, as both key
   *   and value.
   */
  public function getUniqueHarvestPlanIds(): array {
    return array_keys(
      $this->connection->select($this->entityTypeDefinition->getBaseTable(), 'hr')
        ->fields('hr', ['harvest_plan_id'])
        ->distinct()
        ->execute()
        ->fetchAllAssoc('harvest_plan_id')
    );
  }

  /**
   * Helper method to load a harvest_run entity given an ID and plan ID.
   *
   * @param string $plan_id
   *   Plan ID.
   * @param string $run_id
   *   Run ID, which is a timestamp.
   *
   * @return \Drupal\harvest\HarvestRunInterface|null
   *   The loaded entity or NULL if none could be loaded.
   */
  public function loadEntity(string $plan_id, string $run_id): ?HarvestRunInterface {
    if ($ids = $this->runStorage->getQuery()
      ->condition('id', $run_id)
      ->condition('harvest_plan_id', $plan_id)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute()
    ) {
      return $this->runStorage->load(reset($ids));
    }
    return NULL;
  }

  /**
   * Write a harvest_run entity, updating or saving as needed.
   *
   * @param array $run_data
   *   Structured data.
   * @param string $plan_id
   *   Harvest plan identifier.
   * @param string $run_id
   *   Harvest run identifier.
   *
   * @return string
   *   Harvest plan identifier for the entity that was written.
   */
  public function writeEntity(array $run_data, string $plan_id, string $run_id) {
    /** @var \Drupal\harvest\HarvestRunInterface $entity */
    $entity = $this->loadEntity($plan_id, $run_id);
    if ($entity) {
      // Modify entity.
      unset($run_data['id']);
      foreach ($run_data as $key => $value) {
        $entity->set($key, $value);
      }
    }
    else {
      $entity = $this->runStorage->create($run_data);
    }
    $entity->save();
    return $entity->id();
  }

}
