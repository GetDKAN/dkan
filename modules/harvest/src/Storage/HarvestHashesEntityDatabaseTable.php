<?php

namespace Drupal\harvest\Storage;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\common\Storage\DatabaseTableInterface;
use Drupal\common\Storage\Query;
use Drupal\harvest\HarvestHashInterface;

/**
 * Shim between the harvest_hash entity type and DKAN db table interface.
 *
 * Some method implementations are stubbed out and throw a runtime exception,
 * so that future callers can implement them if needed.
 *
 * Note: Our way of storing this data means that we can't have more than one
 * harvest plan ID per Data node.
 *
 * @see \Drupal\harvest\Entity\HarvestHash
 *
 * @todo Remove this in a refactor of the harvester.
 *
 * @internal
 */
class HarvestHashesEntityDatabaseTable implements DatabaseTableInterface {

  /**
   * Entity type we're dealing with.
   */
  protected const ENTITY_TYPE = 'harvest_hash';

  /**
   * The plan ID for this 'table'.
   *
   * All queries will use this plan ID to limit the results.
   */
  protected string $planId;

  /**
   * Entity type manager service.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Entity storage interface for the entity type we're wrapping.
   */
  protected EntityStorageInterface $entityStorage;

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
   *
   * @see \Drupal\harvest\Storage\HarvestHashesDatabaseTableFactory
   */
  public function __construct(string $planId, EntityTypeManagerInterface $entityTypeManager) {
    $this->planId = $planId;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityStorage = $entityTypeManager->getStorage(static::ENTITY_TYPE);
  }

  /**
   * {@inheritDoc}
   *
   * @param string $data
   *   Data is assumed to be a JSON-encoded object with these properties:
   *   - harvest_plan_id (The harvest name).
   *   - hash.
   * @param string|null $id
   *   A node entity UUID identifier.
   *
   * @return string
   *   Identifier.
   */
  public function store($data, ?string $id = NULL) : string {
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
        'data_uuid' => $id,
        'harvest_plan_id' => $harvest_plan_id,
        'hash' => $hash,
      ]);
    }
    $entity->save();
    return $entity->get('data_uuid')->getString();
  }

  /**
   * {@inheritDoc}
   *
   * @param string $id
   *   Dataset node UUID.
   *
   * @return \Procrastinator\HydratableInterface
   *   JSON-encoded result of query.
   */
  public function retrieve(string $id) {
    if ($ids = $this->entityStorage->getQuery()
      ->condition('harvest_plan_id', $this->planId)
      ->condition('data_uuid', $id)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute()
    ) {
      return json_encode($this->entityStorage->load(reset($ids)));
    }
    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function remove(string $id) {
    if ($ids = $this->entityStorage->getQuery()
      ->condition('harvest_plan_id', $this->planId)
      ->condition('data_uuid', $id)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute()
    ) {
      $entity_id = reset($ids);
      $this->entityStorage->delete([
        $this->entityStorage->load($entity_id),
      ]);
    }
    return $id;
  }

  /**
   * {@inheritDoc}
   */
  public function retrieveAll(): array {
    $data_uuids = [];
    $entities = $this->entityStorage->loadMultiple(
      $this->entityStorage->getQuery()
        ->condition('harvest_plan_id', $this->planId)
        ->accessCheck(FALSE)
        ->execute()
    );
    /** @var \Drupal\harvest\HarvestHashInterface  $entity*/
    foreach ($entities as $entity) {
      $uuid = $entity->get('data_uuid')->getString();
      $data_uuids[$uuid] = $uuid;
    }
    // Some calling code is very particular about the output being an array,
    // both as a return value here and after json_encode(). Since we're using a
    // keyed array, json_encode() will think it's an object. We don't want that,
    // so we use array_values() to yield an indexed array.
    return array_values($data_uuids);
  }

  /**
   * {@inheritDoc}
   */
  public function destruct() {
    // DKAN API wants us to destroy the table, but we can't/shouldn't do that
    // here. So instead, we will delete all entities for our plan ID.
    if ($ids = $this->entityStorage->getQuery()
      ->condition('harvest_plan_id', $this->planId)
      ->accessCheck(FALSE)
      ->execute()
    ) {
      // Limit the number of entities deleted at one time. This can prevent
      // problems with huge tables of fielded entities.
      foreach (array_chunk($ids, 100) as $chunked_ids) {
        $this->entityStorage->delete($this->entityStorage->loadMultiple($chunked_ids));
      }
    }
  }

  /**
   * {@inheritDoc}
   */
  public function count(): int {
    return $this->entityStorage->getQuery()
      ->condition('harvest_plan_id', $this->planId)
      ->count()
      ->accessCheck(FALSE)
      ->execute();
  }

  /**
   * {@inheritDoc}
   */
  public function primaryKey() {
    // The primary key for entity API is 'id'. But the primary key for the
    // database table interface is 'data_uuid'. This is mostly arbitrary for our
    // purposes because we're not actually subclassing AbstractDatabaseTable.
    return 'data_uuid';
  }

  /**
   * {@inheritDoc}
   */
  public function storeMultiple(array $data) {
    throw new \RuntimeException(__METHOD__ . ' not yet implemented.');
  }

  /**
   * {@inheritDoc}
   */
  public function query(Query $query) {
    throw new \RuntimeException(__METHOD__ . ' not yet implemented.');
  }

  /**
   * {@inheritDoc}
   */
  public function setSchema(array $schema): void {
    throw new \RuntimeException(__METHOD__ . ' not yet implemented.');
  }

  /**
   * {@inheritDoc}
   */
  public function getSchema(): array {
    throw new \RuntimeException(__METHOD__ . ' not yet implemented.');
  }

  /**
   * Helper method to load an entity given an ID.
   *
   * @param string $data_uuid
   *   Entity ID.
   *
   * @return \Drupal\harvest\HarvestHashInterface|null
   *   The loaded entity or NULL if none could be loaded.
   */
  protected function loadEntity(string $data_uuid): ?HarvestHashInterface {
    if (!$data_uuid) {
      return NULL;
    }
    if ($ids = $this->entityStorage->getQuery()
      ->condition('data_uuid', $data_uuid)
      ->condition('harvest_plan_id', $this->planId)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute()
    ) {
      return $this->entityStorage->load(reset($ids));
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getTableName() {
    throw new \RuntimeException(__METHOD__ . ' not yet implemented.');
  }

}
