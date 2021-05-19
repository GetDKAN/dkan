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
