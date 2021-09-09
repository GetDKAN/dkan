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
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityRepository $entityRepository
   *   Entity Repository service.
   */
  public function __construct(EntityRepository $entityRepository);

  /**
   * Return a metastore item.
   *
   * @param string $identifier
   *   Item ID, usually a UUID.
   * @param array $config
   *   User config; not usually used.
   *
   * @return \Drupal\metastore\MetastoreItemInterface
   *   A metastore item object.
   */
  public function getInstance(string $identifier, array $config = []);

  /**
   * Wrap an arbitrary object as a metastore item interface compliant object.
   *
   * @param mixed $input
   *   Any object that can be wrapped as a metastore item. For instance, a node.
   *
   * @return Drupal\metastore\MetastoreItemInterface
   *   A metastore item interface compliant object.
   */
  public function wrap($input);

  /**
   * Return list cache tags for metastore items.
   *
   * @return array
   *   An array of cache tags.
   *
   * @todo Make this schema-specific.
   */
  public static function getCacheTags();

}
