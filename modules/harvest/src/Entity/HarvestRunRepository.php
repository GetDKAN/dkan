<?php

namespace Drupal\harvest\Entity;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\harvest\HarvestRunInterface;

class HarvestRunRepository {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private EntityTypeManagerInterface $entityTypeManager;

  private EntityStorageInterface $runStorage;

  public function __construct(
    EntityTypeManagerInterface $entityTypeManager
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->runStorage = $entityTypeManager->getStorage('harvest_run');
  }

  public function destructForPlanId($plan_id) {
    if ($ids = $this->retrieveAllRunIds($plan_id)) {
      $entities = $this->runStorage->loadMultiple($ids);
      foreach ($entities as $entity) {
        $entity->delete();
      }
    }
  }

  /**
   * Store JSON-encoded run data.
   *
   * @param string $run_data
   *   JSON-encoded run data.
   * @param string $plan_id
   *   The plan identifier.
   * @param string $run_id
   *   The run identifier, which is also a timestamp.
   *
   * @return string
   *   The run identifier, which is also a timestamp.
   */
  public function storeRun(string $run_data, string $plan_id, string $run_id): string {
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

  public function retrieveRun(string $plan_id, string $run_id): ?string {
    if ($entity = $this->loadEntity($plan_id, $run_id)) {
      return $entity->get('data')->getString();
    }
    return NULL;
  }

  public function retrieveAllRunIds(string $plan_id): array {
    return $this->runStorage->getQuery()
      ->condition('harvest_plan_id', $plan_id)
      ->sort('id', 'DESC')
      ->accessCheck(FALSE)
      ->execute();
  }

  public function retrieveAllRuns(string $plan_id): array {
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
