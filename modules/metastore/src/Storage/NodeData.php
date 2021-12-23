<?php

namespace Drupal\metastore\Storage;

use Drupal\Core\Entity\EntityTypeManager;

/**
 * Node Data.
 */
class NodeData extends Data {

  /**
   * NodeData constructor.
   */
  public function __construct(string $schemaId, EntityTypeManager $entityTypeManager) {
    $this->entityType = 'node';
    $this->bundle = 'data';
    $this->bundleKey = "type";
    $this->labelKey = "title";
    parent::__construct($schemaId, $entityTypeManager);
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveContains(string $string, bool $caseSensitive = TRUE): array {

    $query = $this->listQueryBase()->condition(static::getMetadataField(), $string, 'CONTAINS');
    if ($caseSensitive) {
      $query->addTag('case_sensitive');
    }
    $entityIds = $query->execute();

    return array_map(function ($entity) {
      return $entity->get(static::getMetadataField())->getString();
    }, $this->entityStorage->loadMultiple($entityIds));
  }

  /**
   * {@inheritdoc}
   */
  public static function getEntityType() {
    return 'node';
  }

  /**
   * {@inheritdoc}
   */
  public static function getBundles() {
    return ['data'];
  }

  /**
   * {@inheritdoc}
   */
  public static function getMetadataField() {
    return 'field_json_metadata';
  }

  /**
   * {@inheritdoc}
   */
  public static function getSchemaIdField() {
    return 'field_data_type';
  }

  /**
   * Retrieve by hash.
   *
   * @param string $hash
   *   The hash for the data.
   * @param string $schema_id
   *   The schema ID.
   *
   * @return string|null
   *   The uuid of the item with that hash.
   */
  public function retrieveByHash($hash, $schema_id) {
    $nodes = $this->getEntityStorage()->loadByProperties([
      $this->labelKey => $hash,
      'field_data_type' => $schema_id,
    ]);
    if ($node = reset($nodes)) {
      return $node->uuid();
    }
    return NULL;
  }

}
