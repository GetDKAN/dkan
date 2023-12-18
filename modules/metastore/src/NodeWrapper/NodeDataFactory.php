<?php

namespace Drupal\metastore\NodeWrapper;

use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\metastore\Factory\MetastoreEntityItemFactoryInterface;

/**
 * Class NodeDataFactory.
 *
 * Build a MetastoreItemInterface object from a simple node.
 */
class NodeDataFactory implements MetastoreEntityItemFactoryInterface {

  /**
   * EntityRepository object.
   *
   * @var Drupal\Core\Entity\EntityRepository
   */
  private $entityRepository;

  /**
   * Entity Type Manager.
   *
   * @var Drupal\Core\Entity\EntityTypeManager
   */
  private $entityTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityRepository $entityRepository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   Entity Type Manager service.
   */
  public function __construct(EntityRepository $entityRepository, EntityTypeManager $entityTypeManager) {
    $this->entityRepository = $entityRepository;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Get a metastore node data object from an identifier.
   *
   * @param string $identifier
   *   Node uuid.
   * @param array $config
   *   Optional config from interface, not used.
   *
   * @return Data
   *   Metastore data node object.
   */
  public function getInstance(string $identifier, array $config = []) {
    $dataNode = $this->entityRepository->loadEntityByUuid("node", $identifier);
    return new Data($dataNode, $this->entityTypeManager);
  }

  /**
   * Create a metastore node data object from a node object.
   *
   * @param mixed $dataNode
   *   A data node.
   *
   * @return Data
   *   Metastore data node object.
   */
  public function wrap($dataNode) {
    return new Data($dataNode, $this->entityTypeManager);
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
  public static function getCacheTags() {
    return ['node_list:data'];
  }

  /**
   * {@inheritdoc}
   */
  public static function getMetadataField() {
    return 'field_json_metadata';
  }

}
