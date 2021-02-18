<?php

namespace Drupal\metastore\Storage;

use Drupal\Core\Entity\EntityTypeManager;

/**
 * Node Data.
 */
class NodeStorage extends EntityStorage {

  /**
   * NodeData constructor.
   */
  public function __construct(string $schemaId, EntityTypeManager $entityTypeManager) {
    $this->entityType = 'node';
    parent::__construct($schemaId, $entityTypeManager);
    $this->bundle = 'data';
  }

}
