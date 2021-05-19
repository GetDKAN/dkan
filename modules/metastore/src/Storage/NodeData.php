<?php

namespace Drupal\metastore\Storage;

use Drupal\Core\Entity\EntityTypeManager;

/**
 * Node Data.
 */
class NodeData extends Data implements MetastoreEntityStorageInterface {

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

}
