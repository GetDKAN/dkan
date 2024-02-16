<?php

namespace Drupal\harvest\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Repository for various queries related to harvest plans.
 *
 * This is the service dkan.harvest.harvest_plan_repository.
 */
class HarvestPlanRepository {

  /**
   * Storage service for harvest_plans entities.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private EntityStorageInterface $planStorage;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager service.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager
  ) {
    $this->planStorage = $entityTypeManager->getStorage('harvest_plan');
  }

  /**
   * Get all the plan identifiers.
   *
   * This is the plan name.
   *
   * @return array
   *   All plan identifiers.
   */
  public function retrieveAll(): array {
    // Some calling code is very particular about the output being an array,
    // both as a return value here and after json_encode(). Since the entity
    // query returns a keyed array, json_encode() will think it's an object. We
    // don't want that, so we use array_values().
    return array_values(
      $this->planStorage->getQuery()
        ->accessCheck(FALSE)
        ->execute()
    );
  }

  /**
   * Retrieve a JSON-encoded plan.
   *
   * @param string $plan_id
   *   The plan ID to retrieve.
   *
   * @return string
   *   The plan record.
   *
   * @see \Drupal\harvest\Entity\HarvestPlan::jsonSerialize()
   */
  public function retrieve(string $plan_id) {
    if ($entity = $this->loadEntity($plan_id)) {
      return json_encode($entity);
    }
    return NULL;
  }

  /**
   * Store a JSON-encoded plan for a plan identifier.
   *
   * @param string $plan_data
   *   JSON-encoded plan data.
   * @param string $plan_id
   *   The plan identifier.
   *
   * @return string
   *   The plan id.
   */
  public function store(string $plan_data, string $plan_id): string {
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
   *   Whether the
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function remove(string $plan_id): bool {
    $this->planStorage->delete([$this->loadEntity($plan_id)]);
    return TRUE;
  }

  /**
   * Helper method to load an entity given an ID.
   *
   * @param string $plan_id
   *   Entity ID.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The loaded entity or NULL if none could be loaded.
   */
  protected function loadEntity(string $plan_id): ?EntityInterface {
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
