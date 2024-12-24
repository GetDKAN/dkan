<?php

namespace Drupal\metastore\Storage;

use Contracts\FactoryInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Psr\Log\LoggerInterface;

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
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * DKAN logger channel service.
   */
  private LoggerInterface $logger;

  /**
   * Constructor.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    ConfigFactoryInterface $config_factory,
    LoggerInterface $loggerChannel,
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->configFactory = $config_factory;
    $this->logger = $loggerChannel;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getInstance(string $identifier, array $config = []) {
    if (!isset($this->stores[$identifier])) {
      $entity_type = $this->getEntityTypeBySchema($identifier);

      $instance = match ($entity_type) {
        default => $this->createNodeInstance($identifier),
      };

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
    return $mapping[$schema_id] ?? 'node';
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
    return new NodeData(
      $identifier,
      $this->entityTypeManager,
      $this->configFactory,
      $this->logger
    );
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
