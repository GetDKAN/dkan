<?php

namespace Drupal\metastore\Factory;

use Drupal\Core\Entity\EntityRepository;
use Drupal\metastore\NodeWrapper\Data;

/**
 * Class MetastoreDataNodeFactory.
 *
 * Build a MetastoreItemInterface object from a simple node.
 */
class MetastoreDataNodeFactory implements MetastoreItemFactoryInterface {

  /**
   * @var Drupal\Core\Entity\EntityRepository
   */
  private $entityRepository;

  /**
   * @param EntityRepository $nodeStorage
   */
  public function __construct(EntityRepository $entityRepository) {
    $this->entityRepository = $entityRepository;
  }

  /**
   * @param string $identifier
   * @param array $config
   * 
   * @return [type]
   */
  public function getInstance(string $identifier, array $config = []) {
    $dataNode = $this->entityRepository->loadEntityByUuid("node", $identifier);
    return new Data($dataNode);
  }

  /**
   * @param mixed $dataNode
   * 
   * @return [type]
   */
  public function wrap($dataNode) {
    return new Data($dataNode);
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
  public static function getMetadataField() {
    return 'field_json_metadata';
  }

}
