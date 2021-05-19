<?php

namespace Drupal\metastore_entity\Storage;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\metastore\Storage\MetastoreEntityStorageInterface;
use Drupal\metastore_entity\Entity\MetastoreItem;

/**
 * Node Data.
 */
class MetastoreEntityStorage implements MetastoreEntityStorageInterface {

  /**
   * MetastoreNodeStorage constructor.
   */
  public function __construct(string $schemaId, EntityTypeManager $entityTypeManager) {
    $this->entityType = "metastore_item";
    $this->bundle = $schemaId;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityStorage = $this->entityTypeManager->getStorage($this->entityType);

    $this->bundleKey = $this->entityStorage->getEntityType()->getKey('bundle');
    $this->labelKey = $this->entityStorage->getEntityType()->getKey('label');
  }

  public static function getEntityType() {
    return 'metastore_item';
  }

  public static function getBundles() { 

  }

  public static function getMetadataField() {
    return 'json_data';
  }

  public function getEntityLatestRevision(string $uuid) {

    $entity_id = $this->getEntityIdFromUuid($uuid);

    if ($entity_id) {
      $revision_id = $this->entityStorage->getLatestRevisionId($entity_id);
      return $this->entityStorage->loadRevision($revision_id);
    }

    return NULL;
  }

  public function publish(string $uuid): string {
    if ($this->schemaId !== 'dataset') {
      throw new \Exception("Publishing currently only implemented for datasets.");
    }

    $entity = $this->getEntityLatestRevision($uuid);

    if ($entity && $entity->get('moderation_state') !== 'published') {
      $entity->set('moderation_state', 'published');
      $entity->save();
      return $uuid;
    }

    throw new \Exception("No data with that identifier was found.");
  }

  public function remove(string $id) { }

  public function store($data, ?string $id = NULL): string { }

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
      if ($metastore_item->get('status') == TRUE) {
        $all[] = $metastore_item->getMetadata();
      }
    }
    return $all;
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function retrieve(string $uuid) : ?string {

    if ($this->getDefaultModerationState() === 'published') {
      $entity = $this->getEntityPublishedRevision($uuid);
    }
    else {
      $entity = $this->getEntityLatestRevision($uuid);
    }

    if ($entity && $entity instanceof MetastoreItem) {
      return $entity->get('json_data')->getString();
    }

    throw new \Exception("No data with that identifier was found.");
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function getEntityIdFromUuid(string $uuid) : ?int {

    $query = $this->entityStorage->getQuery()
      ->condition('uuid', $uuid)
      ->condition('schema', $this->bundle);

    $entity_ids = $query->execute();

    return $entity_ids ? (int) reset($entity_ids) : NULL;
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function retrievePublished(string $uuid) : ?string {
    $entity = $this->getEntityPublishedRevision($uuid);

    if ($entity && $entity->get('moderation_state')->getString() == 'published') {
      return $entity->get('json_data')->getString();
    }

    throw new \Exception("No data with that identifier was found.");
  }

}
