<?php

namespace Drupal\common\Storage;

use Contracts\HydratableInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provide a DKAN storage shim for Drupal Entity API.
 *
 * How to use:
 * - Make a subclass which overrides $this->$entityType.
 * - Optionally, override $this->$dataFieldName as needed if your entity adopts
 *   the identifier + JSON blob schema pattern, but uses a different base field
 *   name for the data.
 * - Override any of the DatabaseTableInterface methods as needed for your own
 *   entity's use cases.
 * - Add or modify a storage factory that can create an instance of your new
 *   class within Drupal\common\Storage\StorageFactoryInterface::getInstance().
 *   This instance will then use the entity type as a backend.
 *
 * @todo Move all mountains necessary to remove this compatibility layer from
 *   DKAN, and just use Entity API for everything.
 *
 * @internal
 */
abstract class DrupalEntityDatabaseTableBase implements DatabaseTableInterface {

  /**
   * The entity type's ID.
   *
   * Override this with your own entity type ID.
   *
   * @var string
   */
  protected string $entityType = '';

  /**
   * Data field which is where DKAN will put all the JSON data for this entity.
   *
   * @var string
   */
  protected string $dataFieldName = 'data';

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

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityStorage = $entityTypeManager->getStorage($this->entityType);
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
        ->accessCheck(FALSE)
        ->execute()
    );
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
    if ($ids = $this->retrieveAll()) {
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
    throw new \RuntimeException(__METHOD__ . ' not yet implemented.');
  }

  /**
   * {@inheritDoc}
   */
  public function primaryKey() {
    // Use the primary key defined in the entity definition.
    $definition = $this->entityTypeManager->getDefinition($this->entityType);
    return ($definition->getKeys())['id'];
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
   * {@inheritDoc}
   */
  public function remove(string $id) {
    $this->entityStorage->delete([$this->loadEntity($id)]);
    return TRUE;
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
      $entity->set($this->dataFieldName, $data);
    }
    else {
      $entity = $this->entityStorage->create([
        $this->primaryKey() => $id,
        $this->dataFieldName => $data,
      ]);
    }
    $entity->save();
    return $entity->get($this->primaryKey())->getString();
  }

  /**
   * Helper method to load an entity given an ID.
   *
   * @param string $id
   *   Entity ID.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The loaded entity or NULL if none could be loaded.
   */
  protected function loadEntity(string $id): ?EntityInterface {
    if (!$id) {
      return NULL;
    }
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
