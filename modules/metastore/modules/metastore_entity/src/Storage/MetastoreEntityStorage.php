<?php

namespace Drupal\metastore_entity\Storage;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\metastore\Storage\AbstractEntityStorage;

/**
 * Node Data.
 */
class MetastoreEntityStorage extends AbstractEntityStorage {

  /**
   * NodeData constructor.
   */
  public function __construct(string $schemaId, EntityTypeManager $entityTypeManager) {
    $this->entityType = "metastore_item";
    $this->bundle = $schemaId;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityStorage = $this->entityTypeManager->getStorage($this->entityType);

    $this->bundleKey = $this->entityStorage->getEntityType()->getKey('bundle');
    $this->labelKey = $this->entityStorage->getEntityType()->getKey('label');
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function retrieveAll(): array {

    $entity_ids = $this->entityStorage->getQuery()
      ->condition('schema', $this->bundle)
      ->execute();

    $all = [];
    foreach ($entity_ids as $id) {
      $metastore_item = $this->entityStorage->load($id);
      if ($metastore_item->get('moderation_state')->getString() === 'published') {
        $all[] = $metastore_item->getMetaData();
      }
    }
    return $all;
  }

}
