<?php

namespace Drupal\metastore\Storage;

use Contracts\FactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Data factory.
 */
class DataFactory implements FactoryInterface {

  private $stores = [];

  private $entityTypeManager;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getInstance(string $identifier, array $config = []) {
    if (!isset($this->stores[$identifier])) {
      $this->stores[$identifier] = new Data($identifier, $this->entityTypeManager);
    }
    return $this->stores[$identifier];
  }

}
