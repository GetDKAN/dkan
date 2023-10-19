<?php

namespace Drupal\common\Storage;

use Contracts\HydratableInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provide a DKAN storage shim for Drupal Entity API.
 */
abstract class DrupalEntityDatabaseTableBase implements DatabaseTableInterface {

  /**
   * The entity type's ID.
   *
   * Override this with your own entity type ID.
   */
  protected static $entityType = '';

  /**
   * Data field which is where DKAN will put all the JSON data for this entity.
   */
  protected static $dataFieldName = 'data';

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Entity storage interface for the entity type we're wrapping.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected EntityStorageInterface $entityStorage;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityStorage = $entityTypeManager->getStorage(static::$entityType);
  }

  /**
   * {@inheritDoc}
   */
  public function retrieveAll(): array {
    $ids = $this->entityStorage->getQuery()
      ->accessCheck(FALSE)
      ->execute();
    if ($ids) {
      return $this->entityStorage->loadMultiple($ids);
    }
    return [];
  }

  /**
   * {@inheritDoc}
   */
  public function storeMultiple(array $data) {
    throw new \Exception(__METHOD__);
  }

  /**
   * {@inheritDoc}
   */
  public function count(): int {
    return $this->entityStorage->getQuery()
      ->count()
      ->execute();
  }

  /**
   * {@inheritDoc}
   */
  public function destruct() {
    // DKAN API wants us to destroy the table, but we can't/shouldn't do that
    // within Drupal's Entity API. So instead, we will delete all entities.
    $ids = $this->entityStorage->getQuery()
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

  /**
   * {@inheritDoc}
   */
  public function query(Query $query) {
    throw new \Exception(__METHOD__);
  }

  /**
   * {@inheritDoc}
   */
  public function primaryKey() {
    // Use the primary key defined in the entity definition.
    $definition = $this->entityTypeManager->getDefinition(static::$entityType);
    return ($definition->getKeys())['id'];
  }

  /**
   * {@inheritDoc}
   */
  public function setSchema(array $schema): void {
    throw new \Exception(__METHOD__);
  }

  /**
   * {@inheritDoc}
   */
  public function getSchema(): array {
    throw new \Exception(__METHOD__);
  }

  /**
   * {@inheritDoc}
   */
  public function remove(string $id) {
    $this->entityStorage->delete([$this->loadEntity($id)]);
  }

  /**
   * {@inheritDoc}
   */
  public function retrieve(string $id) {
    if ($entity = $this->loadEntity($id)) {
      if ($entity instanceof HydratableInterface) {
        return $entity;
      }
      return json_encode($entity);
    }
    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function store($data, string $id = NULL): string {
    $entity = $this->loadEntity($id);
    if ($entity) {
      // Modify entity.
      $entity->set(static::$dataFieldName, $data);
    }
    else {
      $entity = $this->entityStorage->create([
        $this->primaryKey() => $id,
        static::$dataFieldName => $data,
      ]);
    }
    $entity->save();
    return $entity->get($this->primaryKey())->value;
  }

  protected function loadEntity(string $id): ?EntityInterface {
    if ($ids = $this->entityStorage->getQuery()
      ->condition($this->primaryKey(), $id)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute()
    ) {
      return $this->entityStorage->load(reset($ids));
    }
    return NULL;
  }

}
