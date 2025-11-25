<?php

namespace Drupal\metastore\Storage;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Psr\Log\LoggerInterface;

/**
 * Node Data.
 */
class NodeData extends Data {

  /**
   * NodeData constructor.
   */
  public function __construct(
    string $schemaId,
    EntityTypeManagerInterface $entityTypeManager,
    ConfigFactoryInterface $config_factory,
    FileSystemInterface $file_system,
    LoggerInterface $loggerChannel,
  ) {
    $this->entityType = 'node';
    $this->bundle = 'data';
    $this->bundleKey = 'type';
    $this->labelKey = 'title';
    $this->schemaIdField = 'field_data_type';
    $this->metadataField = 'field_json_metadata';
    parent::__construct($schemaId, $entityTypeManager, $config_factory, $file_system, $loggerChannel);
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveContains(string $string, bool $caseSensitive = TRUE): array {

    $query = $this->listQueryBase()->condition($this->metadataField, $string, 'CONTAINS');
    if ($caseSensitive) {
      $query->addTag('case_sensitive');
    }
    $entityIds = $query->execute();

    return array_map(function ($entity) {
      return $entity->get($this->metadataField)->getString();
    }, $this->entityStorage->loadMultiple($entityIds));
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
