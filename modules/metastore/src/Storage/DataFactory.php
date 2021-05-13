<?php

namespace Drupal\metastore\Storage;

use Contracts\FactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Data factory.
 */
class DataFactory implements FactoryInterface {

  /**
   * Array of storage engines.
   *
   * @var array
   */
  private $stores = [];

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
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
      $entity_type = $this->getEntityTypeBySchema($identifier);

      switch ($entity_type) {
        case 'node':
        default:
          $instance = $this->createNodeInstance($identifier);
          break;
      }

      $this->stores[$identifier] = $instance;
    }
    return $this->stores[$identifier];
  }

  /**
   * Gets entity type by schema id.
   *
   * @param string $schema_id
   *   Schema id.
   *
   * @return string
   *   Entity type
   */
  private function getEntityTypeBySchema(string $schema_id) : string {
    // @todo Should be configurable. Different from site to site.
    $mapping = [
      'dataset' => 'node',
    ];
    return isset($mapping[$schema_id]) ? $mapping[$schema_id] : 'node';
  }

  /**
   * Create node instance.
   *
   * @param string $identifier
   *   Schema id.
   *
   * @return \Drupal\metastore\Storage\NodeData
   *   Storage object.
   */
  protected function createNodeInstance(string $identifier) {
    return new NodeData($identifier, $this->entityTypeManager);
  }

  /**
   * Get the storage class name for this factory.
   *
   * @return string
   *   Qualified storage class name.
   */
  public static function getStorageClass() {
    return NodeData::class;
  }

}
