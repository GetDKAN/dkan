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
    parent::__construct($schemaId, $entityTypeManager);
    $this->bundle = 'data';
  }

}
