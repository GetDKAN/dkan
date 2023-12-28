<?php

namespace Drupal\harvest\Storage;

use Drupal\common\Storage\DrupalEntityDatabaseTableBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Shim implementation between DatabaseTableInterface and Drupal's Entity API.
 */
class HarvestHashesEntityDatabaseTable extends DrupalEntityDatabaseTableBase {

  protected static $entityType = 'harvest_hash';

  protected static $dataFieldName = '';

  private string $planId;

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
   *   A UUID node entity identifier. Must reference an existing data
   *   node. Must never be NULL.
   *
   * @return string
   *   Identifier.
   */
  public function store($data, string $id = NULL) : string {
    if (!$id) {
      throw new \InvalidArgumentException('ID can not be NULL.');
    }
    $decoded = json_decode($data, TRUE);
    // Coalesce to NULL because if these values are not present, there
    // should be an error when we write the entity.
    $harvest_plan_id = $decoded['harvest_plan_id'] ?? NULL;
    $hash = $decoded['hash'] ?? NULL;

    $entity = $this->loadEntity($id);
    if ($entity) {
      // Modify entity.
      $entity->set('harvest_plan_id', $harvest_plan_id);
      $entity->set('hash', $hash);
    }
    else {
      $entity = $this->entityStorage->create([
        'dataset_uuid' => $id,
        'harvest_plan_id' => $harvest_plan_id,
        'hash' => $hash,
      ]);
    }
    $entity->save();
    return $entity->get($this->primaryKey())->value;
  }

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
        $this->entityStorage->delete($chunked_ids);
      }
    }
  }

}
