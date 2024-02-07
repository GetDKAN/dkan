<?php

namespace Drupal\harvest\Storage;

use Drupal\common\Storage\DrupalEntityDatabaseTableBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Shim between the harvest_hash entity type and DKAN db table interface.
 *
 * Some method implementations are stubbed out and throw a runtime exception,
 * so that future callers can implement them if needed.
 */
class HarvestHashesEntityDatabaseTable extends DrupalEntityDatabaseTableBase {

  protected string $entityType = 'harvest_hash';

  protected string $dataFieldName = '';

  protected string $planId;

  /**
   * Construct an entity shim.
   *
   * Luckily for us, we only ever need one of these 'tables' per plan id. This
   * means that if you want a 'table' for another plan, use the factory to
   * create it with the different plan id.
   *
   * @param string $planId
   *   Harvest plan identifier.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager service.
   */
  public function __construct(string $planId, EntityTypeManagerInterface $entityTypeManager) {
    $this->planId = $planId;
    parent::__construct($entityTypeManager);
  }

  /**
   * {@inheritDoc}
   *
   * @param $data
   *   Data is assumed to be a JSON-encoded object with these properties:
   *   - harvest_plan_id (The harvest name.)
   *   - hash
   * @param string|null $id
   *   A node entity UUID identifier.
   *
   * @return string
   *   Identifier.
   */
  public function store($data, string $id = NULL) : string {
    $decoded = json_decode($data, TRUE);
    // Coalesce to NULL because if these values are not present, there
    // should be an error when we write the entity.
    $harvest_plan_id = $decoded['harvest_plan_id'] ?? NULL;
    $hash = $decoded['hash'] ?? NULL;

    // Does the JSON plan id match our table plan id?
    if ($harvest_plan_id !== $this->planId) {
      throw new \InvalidArgumentException('Encoded JSON plan identifier: ' . $harvest_plan_id . ' must match table plan identifier: ' . $this->planId);
    }
    // Try to load the entity.
    $entity = $this->loadEntity($id);
    if ($entity) {
      // Modify existing entity.
      $entity->set('harvest_plan_id', $harvest_plan_id);
      $entity->set('hash', $hash);
    }
    else {
      // Create a new entity.
      $entity = $this->entityStorage->create([
        'dataset_uuid' => $id,
        'harvest_plan_id' => $harvest_plan_id,
        'hash' => $hash,
      ]);
    }
    $entity->save();
    return $entity->get($this->primaryKey())->getString();
  }

  /**
   * {@inheritDoc}
   *
   * @param string $id
   *   Dataset node UUID.
   *
   * @return \Contracts\HydratableInterface|false|string|void|null
   *   JSON-encoded result of query.
   */
  public function retrieve(string $id) {
    if ($ids = $this->entityStorage->getQuery()
      ->condition('harvest_plan_id', $this->planId)
      ->condition('dataset_uuid', $id)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute()
    ) {
      return json_encode($this->entityStorage->load(reset($ids)));
    }
  }

  /**
   * {@inheritDoc}
   */
  public function remove(string $id) {
    if ($ids = $this->entityStorage->getQuery()
      ->condition('harvest_plan_id', $this->planId)
      ->condition('dataset_uuid', $id)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute()
    ) {
      $entity_id = reset($ids);
      $this->entityStorage->delete([$this->loadEntity($entity_id)]);
    }
    return $id;
  }

  /**
   * {@inheritDoc}
   */
  public function retrieveAll(): array {
    // Some calling code is very particular about the output being an array,
    // both as a return value here and after json_encode(). Since the entity
    // query returns a keyed array, json_encode() will think it's an object. We
    // don't want that, so we use array_values().
    return array_values(
      $this->entityStorage->getQuery()
        ->condition('harvest_plan_id', $this->planId)
        ->accessCheck(FALSE)
        ->execute()
    );
  }

  /**
   * {@inheritDoc}
   */
  public function destruct() {
    // DKAN API wants us to destroy the table, but we can't/shouldn't do that
    // here. So instead, we will delete all entities for our plan ID.
    $ids = $this->entityStorage->getQuery()
      ->condition('harvest_plan_id', $this->planId)
      ->accessCheck(FALSE)
      ->execute();
    if ($ids) {
      // Limit the number of entities deleted at one time. This can prevent
      // problems with huge tables of fielded entities.
      foreach (array_chunk($ids, 100) as $chunked_ids) {
        $this->entityStorage->delete($this->entityStorage->loadMultiple($chunked_ids));
      }
    }
  }

  public function storeMultiple(array $data) {
    throw new \RuntimeException(__METHOD__);
  }

  public function count() : int {
    throw new \RuntimeException(__METHOD__);
  }

}
