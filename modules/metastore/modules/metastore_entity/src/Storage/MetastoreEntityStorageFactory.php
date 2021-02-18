<?php

namespace Drupal\metastore_entity\Storage;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\metastore\Storage\MetastoreStorageFactoryInterface;
use Drupal\metastore\Storage\MetastoreStorageInterface;

/**
 * Data factory.
 */
class MetastoreEntityStorageFactory implements MetastoreStorageFactoryInterface {

  /**
   * @var array
   */
  private $stores = [];

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  private $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getInstance(string $schemaId, array $config = []):MetastoreStorageInterface {
    if (!isset($this->stores[$schemaId])) {
      $instance = new MetastoreEntityStorage($schemaId, $this->entityTypeManager);
      $this->stores[$schemaId] = $instance;
    }
    return $this->stores[$schemaId];
  }
}
