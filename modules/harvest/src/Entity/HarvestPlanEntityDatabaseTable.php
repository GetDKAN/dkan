<?php

namespace Drupal\harvest\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\common\Storage\DatabaseTableInterface;
use Drupal\common\Storage\Query;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class HarvestPlanEntityDatabaseTable implements DatabaseTableInterface {

  protected const ENTITY_TYPE = 'harvest_plan';

  protected const TABLE_NAME = 'harvest_plans';

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected EntityStorageInterface $entityStorage;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityStorage = $entityTypeManager->getStorage(self::ENTITY_TYPE);
  }

  public function retrieveAll(): array {
    $ids = $this->entityStorage->getQuery()
      ->accessCheck(FALSE)
      ->execute();
    if ($ids) {
      return $this->entityStorage->loadMultiple($ids);
    }
    return [];
  }

  public function storeMultiple(array $data) {
    // TODO: Implement storeMultiple() method.
  }

  public function count(): int {
    return $this->entityStorage->getQuery()
      ->count()
      ->execute();
  }

  public function destruct() {
    // DKAN API wants us to destroy the table, but we will delete all entities.
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

  public function query(Query $query) {
    throw new \Exception(__METHOD__);
  }

  public function primaryKey() {
    $definition = $this->entityTypeManager->getDefinition(self::ENTITY_TYPE);
    return ($definition->getKeys())['id'];
  }

  public function setSchema(array $schema): void {
    throw new \Exception(__METHOD__);
  }

  public function getSchema(): array {
    throw new \Exception(__METHOD__);
  }

  public function remove(string $id) {
    $this->entityStorage->delete([$id]);
  }

  public function retrieve(string $id) {
    $ids = $this->entityStorage->getQuery()
      ->condition($this->primaryKey(), $id)
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->execute();
    if ($ids) {
      return $this->entityStorage->load($ids);
    }
    return NULL;
  }

  public function store($data, string $id = NULL): string {
//    $entity = $this->entityTypeManager->createInstance()
    // Does this ID already exist?
    $count = $this->entityStorage->getQuery()
      ->condition($this->primaryKey(), $id)
      ->count()
      ->accessCheck(FALSE)
      ->execute();
    if ($count > 0) {


    }

  }

}
