<?php

namespace Drupal\metastore\NodeWrapper;

use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\metastore\Factory\MetastoreEntityItemFactoryInterface;
use Drupal\metastore\MetastoreItemInterface;

/**
 * Class NodeDataFactory.
 *
 * Build a MetastoreItemInterface object from a simple node.
 */
class NodeDataFactory implements MetastoreEntityItemFactoryInterface {

  /**
   * EntityRepository object.
   *
   * @var \Drupal\Core\Entity\EntityRepository
   */
  private $entityRepository;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityRepository $entityRepository
   *   The entity repository service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity Type Manager service.
   */
  public function __construct(EntityRepository $entityRepository, EntityTypeManagerInterface $entityTypeManager) {
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
   * @return \Drupal\metastore\MetastoreItemInterface
   *   Metastore data node object.
   */
  public function getInstance(string $identifier, array $config = []): MetastoreItemInterface {
    return $this->wrap(
      $this->entityRepository->loadEntityByUuid('node', $identifier)
    );
  }

  /**
   * Create a metastore node data object from a node object.
   *
   * @param mixed $input
   *   A data node.
   *
   * @return \Drupal\metastore\MetastoreItemInterface
   *   Metastore data node object.
   */
  public function wrap($input): MetastoreItemInterface {
    return new Data($input, $this->entityTypeManager);
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
