<?php

namespace Drupal\harvest\Entity;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\harvest\HarvestRunInterface;

class HarvestRunRepository {

  /**
   * Entity storage service for the harvest_run entity type.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private EntityStorageInterface $runStorage;

  public function __construct(
    EntityTypeManagerInterface $entityTypeManager
  ) {
    $this->runStorage = $entityTypeManager->getStorage('harvest_run');
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
    if ($ids = $this->retrieveAllRunIds($plan_id)) {
      $entities = $this->runStorage->loadMultiple($ids);
      foreach ($entities as $entity) {
        $entity->delete();
      }
    }
  }

  /**
   * Store run data.
   *
   * @param mixed $run_data
   *   Run data. Usually the result returned by Harvester::harvest().
   * @param string $plan_id
   *   The plan identifier.
   * @param string $run_id
   *   The run identifier, which is also a timestamp.
   *
   * @return string
   *   The run identifier.
   */
  public function storeRun(mixed $run_data, string $plan_id, string $run_id): string {
    return $this->storeRunJson(json_encode($run_data), $plan_id, $run_id);
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
    $entity = $this->loadEntity($plan_id, $run_id);
    if ($entity) {
      // Modify entity.
      $entity->set('data', $run_data);
    }
    else {
      $entity = $this->runStorage->create([
        'id' => $run_id,
        'harvest_plan_id' => $plan_id,
        'data' => $run_data,
      ]);
    }
    $entity->save();
    return $entity->get('id')->getString();
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
      return $entity->get('data')->getString();
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
   *   JSON-encoded result strings, keyed by harvest run identifier.
   */
  public function retrieveAllRunsJson(string $plan_id): array {
    $runs = [];
    if ($ids = $this->retrieveAllRunIds($plan_id)) {
      foreach ($this->runStorage->loadMultiple($ids) as $entity) {
        $runs[$entity->id()] = $entity->get('data')->getString();
      }
    }
    return $runs;
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
  protected function loadEntity(string $plan_id, string $run_id): ?HarvestRunInterface {
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

}
