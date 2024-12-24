<?php

namespace Drupal\harvest\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\harvest\HarvestPlanInterface;

/**
 * Repository for various queries related to harvest plans.
 *
 * This is the service dkan.harvest.harvest_plan_repository.
 */
class HarvestPlanRepository {

  /**
   * Storage service for harvest_plan entities.
   */
  private EntityStorageInterface $planStorage;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->planStorage = $entityTypeManager->getStorage('harvest_plan');
  }

  /**
   * Get all the plan identifiers.
   *
   * @return string[]
   *   All plan identifiers.
   */
  public function getAllHarvestPlanIds(): array {
    return $this->planStorage->getQuery()
      ->accessCheck(FALSE)
      ->execute();
  }

  /**
   * Get the plan object for the plan identifier.
   *
   * @param string $plan_id
   *   Plan identifier.
   *
   * @return object|null
   *   Object form of the plan or null.
   */
  public function getPlan(string $plan_id): ?object {
    if ($entity = $this->loadEntity($plan_id)) {
      return $entity->getPlan();
    }
    return NULL;
  }

  /**
   * Retrieve a JSON-encoded plan.
   *
   * @param string $plan_id
   *   The plan ID to retrieve.
   *
   * @return string|null
   *   The plan record.
   *
   * @see \Drupal\harvest\Entity\HarvestPlan::jsonSerialize()
   *
   * @todo Move away from expecting a plan to be JSON-encoded.
   */
  public function getPlanJson(string $plan_id) {
    if ($entity = $this->loadEntity($plan_id)) {
      return json_encode($entity);
    }
    return NULL;
  }

  /**
   * Store a plan object for a plan identifier.
   *
   * @param object $plan
   *   The plan object. See components.schemas.harvestPlan within
   *   modules/harvest/docs/openapi_spec.json for the schema of a plan.
   * @param string $plan_id
   *   The plan identifier.
   *
   * @return string
   *   The plan id.
   */
  public function storePlan(object $plan, string $plan_id) {
    return $this->storePlanJson(json_encode($plan), $plan_id);
  }

  /**
   * Store a JSON-encoded plan for a plan identifier.
   *
   * @param string $plan_data
   *   JSON-encoded plan data.See components.schemas.harvestPlan within
   *    modules/harvest/docs/openapi_spec.json for the schema of a plan.
   * @param string $plan_id
   *   The plan identifier.
   *
   * @return string
   *   The plan id.
   *
   * @todo Move away from using JSON to store plans.
   */
  public function storePlanJson(string $plan_data, string $plan_id): string {
    $entity = $this->loadEntity($plan_id);
    if ($entity) {
      // Modify entity.
      $entity->set('data', $plan_data);
    }
    else {
      $entity = $this->planStorage->create([
        'id' => $plan_id,
        'data' => $plan_data,
      ]);
    }
    $entity->save();
    return $entity->get('id')->getString();
  }

  /**
   * Remove the record for the given plan identifier.
   *
   * @param string $plan_id
   *   The plan identifier.
   *
   * @return bool
   *   TRUE if the plan was removed successfully.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function remove(string $plan_id): bool {
    $this->planStorage->delete([$this->loadEntity($plan_id)]);
    return TRUE;
  }

  /**
   * Helper method to load a harvest_plan entity given an ID.
   *
   * @param string $plan_id
   *   Entity ID.
   *
   * @return \Drupal\harvest\HarvestPlanInterface|null
   *   The loaded entity or NULL if none could be loaded.
   */
  protected function loadEntity(string $plan_id): ?HarvestPlanInterface {
    if ($ids = $this->planStorage->getQuery()
      ->condition('id', $plan_id)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute()
    ) {
      return $this->planStorage->load(reset($ids));
    }
    return NULL;
  }

}
