<?php

namespace Drupal\dkan_metastore\NodeWrapper;

use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dkan_metastore\Exception\MissingObjectException;
use Drupal\dkan_metastore\Factory\MetastoreEntityItemFactoryInterface;
use Drupal\dkan_metastore\MetastoreItemInterface;

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
   * @return \Drupal\dkan_metastore\MetastoreItemInterface
   *   Metastore data node object.
   */
  public function getInstance(string $identifier, array $config = []): MetastoreItemInterface {
    return $this->wrap(
      $this->entityRepository->loadEntityByUuid(static::getEntityType(), $identifier)
    );
  }

  /**
   * Create a metastore node data object from a node object.
   *
   * @param mixed $input
   *   A data node.
   *
   * @return \Drupal\dkan_metastore\MetastoreItemInterface
   *   Metastore data node object.
   *
   * @throws \Drupal\dkan_metastore\Exception\MissingObjectException
   *   Thrown when the input is null or otherwise empty.
   */
  public function wrap($input): MetastoreItemInterface {
    // Check $input so we don't even have to start creating a new Data object.
    if ($input) {
      return new Data($input, $this->entityTypeManager);
    }
    throw new MissingObjectException();
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
    $tags = [];
    foreach (static::getBundles() as $bundle) {
      $tags[] = static::getEntityType() . '_list:' . $bundle;
    }
    return $tags;
  }

  /**
   * {@inheritdoc}
   */
  public static function getMetadataField() {
    return 'field_json_metadata';
  }

}
