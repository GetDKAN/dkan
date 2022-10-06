<?php

namespace Drupal\dkan\Storage;

/**
 * Node Data.
 */
class MetastoreNodeStorage extends MetastoreEntityStorageBase implements MetastoreEntityStorageInterface {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Represents the data type passed via the HTTP request url schema_id slug.
   *
   * @var string
   */
  protected $schemaId;

  /**
   * Entity storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|mixed|object
   */
  protected $entityStorage;

  /**
   * Entity label key.
   *
   * @var string
   */
  protected $labelKey = 'title';

  /**
   * Entity bundle key.
   *
   * @var string
   */
  protected $bundleKey = 'type';

  /**
   * Entity type.
   *
   * @var string
   */
  protected $entityType = 'node';

  /**
   * Entity bundle.
   *
   * @var string
   */
  protected $bundle = 'data';

  /**
   * The entity field or property used to store JSON metadata.
   *
   * @var string
   */
  protected $metadataField = 'field_json_metadata';

  /**
   * The entity field or property used to store the schema ID (e.g. "dataset").
   *
   * @var string
   */
  protected $schemaIdField = 'field_data_type';

  /**
   * {@inheritdoc}
   */
  protected $metastoreItemClass = MetastoreNodeItem::class;

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
   *
   * @todo This method is not consistent with others in this class, and
   * may not be needed at all. Fix or remove.
   */
  public function retrieveByHash($hash, $schema_id) {
    $nodes = $this->getEntityStorage()->loadByProperties([
      $this->labelKey => $hash,
      $this->schemaIdField => $schema_id,
    ]);
    if ($node = reset($nodes)) {
      return $node->uuid();
    }
    return NULL;
  }

}
