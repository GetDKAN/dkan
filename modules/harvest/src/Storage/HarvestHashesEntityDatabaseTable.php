<?php

namespace Drupal\harvest\Storage;

use Drupal\common\Storage\DrupalEntityDatabaseTableBase;

class HarvestHashesEntityDatabaseTable extends DrupalEntityDatabaseTableBase {

  protected static $entityType = 'harvest_hash';

  protected static $dataFieldName = '';

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

}
