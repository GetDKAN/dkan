<?php

namespace Drupal\metastore\Factory;

use Contracts\FactoryInterface;
use Drupal\Core\Entity\EntityRepository;

/**
 * Interface MetastoreItemFactoryInterface.
 *
 * Used for service dkan.metastore.metastore_item_factory. Override the service
 * to use different logic for producing a MetastoreItemInterface object from 
 * just an indentifier.
 */
interface MetastoreItemFactoryInterface extends FactoryInterface {

  /**
   * @param EntityRepository $entityRepostitory
   */
  public function __construct(EntityRepository $entityRepository);

  /**
   * @param string $identifier
   *   Item ID, usually a UUID
   * @param array $config
   *   User config; not usually used.
   * 
   * @return Drupal\metastore\MetastoreItemInterface
   */
  public function getInstance(string $identifier, array $config = []);

  /**
   * @param mixed $input
   * 
   * @return Drupal\metastore\MetastoreItemInterface
   */
  public function wrap($input);

}
