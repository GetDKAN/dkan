<?php

namespace Drupal\metastore\Storage;

use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Node Data.
 */
class MetastoreDataNodeStorage extends AbstractEntityStorage implements MetastoreStorageInterface {

  /**
   * NodeData constructor.
   */
  public function __construct(string $schemaId, EntityTypeManager $entityTypeManager) {
    parent::__construct($schemaId, $entityTypeManager);
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

    if ($entity) {
      return $entity->get('field_json_metadata')->getString();
    }

    throw new \Exception("No data with that identifier was found.");
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function publish(string $uuid) : string {

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

  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function retrieveAll(): array {

    $entity_ids = $this->entityStorage->getQuery()
      ->condition('type', 'data')
      ->condition('field_data_type', $this->schemaId)
      ->execute();

    $all = [];
    foreach ($entity_ids as $nid) {
      $entity = $this->entityStorage->load($nid);
      if ($entity->get('moderation_state')->getString() === 'published') {
        $all[] = $entity->get('field_json_metadata')->getString();
      }
    }
    return $all;
  }


  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function retrievePublished(string $uuid) : ?string {
    $entity = $this->getEntityPublishedRevision($uuid);

    if ($entity && $entity->get('moderation_state')->getString() == 'published') {
      return $entity->get('field_json_metadata')->getString();
    }

    throw new \Exception("No data with that identifier was found.");
  }


  /**
   * Private.
   */
  private function updateExistingEntity(EditorialContentEntityBase $entity, $data) {
    $entity->field_data_type = $this->schemaId;
    $new_data = json_encode($data);
    $entity->field_json_metadata = $new_data;

    // Dkan publishing's default moderation state.
    $entity->set('moderation_state', $this->getDefaultModerationState());

    $entity->setRevisionLogMessage("Updated on " . $this->formattedTimestamp());
    $entity->setRevisionCreationTime(time());
    $entity->save();

    return $entity->uuid();
  }

  /**
   * Private.
   */
  private function createNewEntity($uuid, $data) {
    $title = isset($data->title) ? $data->title : $data->name;
    $entity = $this->entityStorage
      ->create(
        [
          $this->labelKey => $title,
          $this->bundleKey => 'data',
          'uuid' => $uuid,
          'field_data_type' => $this->schemaId,
          'field_json_metadata' => json_encode($data),
        ]
      );
    $entity->setRevisionLogMessage("Created on " . $this->formattedTimestamp());

    $entity->save();

    return $entity->uuid();
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}.
   */
  public function store($data, string $uuid = NULL): string {
    $data = json_decode($data);
    $data = $this->filterHtml($data);

    $uuid = (!$uuid && isset($data->identifier)) ? $data->identifier : $uuid;

    if ($uuid) {
      $entity = $this->getEntityLatestRevision($uuid);
    }

    if (isset($entity) && $entity instanceof EntityInterface) {
      return $this->updateExistingEntity($entity, $data);
    }
    // Create new entity.
    else {
      return $this->createNewEntity($uuid, $data);
    }
  }

}
