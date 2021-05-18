<?php

namespace Drupal\metastore\Storage;

use Drupal\Core\Entity\EntityTypeManager;

/**
 * Node Data.
 */
class MetastoreNodeStorage extends AbstractEntityStorage {

  /**
   * Constructor.
   */
  public function __construct(string $schemaId, EntityTypeManager $entityTypeManager) {
    // Set up bundle default value.
    $this->entityType = 'node';
    $this->bundle = 'data';
    $this->metadataField = 'field_json_metadata';
    $this->schemaField = 'field_data_type';

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
    return 'field_json_data';
  }

}
