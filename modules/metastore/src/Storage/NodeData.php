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
