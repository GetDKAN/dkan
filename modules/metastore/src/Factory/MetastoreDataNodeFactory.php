<?php

namespace Drupal\metastore\Factory;

use Drupal\Core\Entity\EntityRepository;
use Drupal\metastore\MetastoreDataNode;

/**
 * Class MetastoreDataNodeFactory.
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
    return new MetastoreDataNode($dataNode);
  }

  /**
   * @param mixed $dataNode
   * 
   * @return [type]
   */
  public function wrap($dataNode) {
    return new MetastoreDataNode($dataNode);
  }

}
