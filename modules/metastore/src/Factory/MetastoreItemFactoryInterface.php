<?php

namespace Drupal\metastore\Factory;

use Contracts\FactoryInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\metastore\MetastoreItemInterface;

/**
 * Interface MetastoreItemFactoryInterface.
 *
 * Used for service dkan.metastore.metastore_item_factory. Decorate the service
 * to use different logic for producing a MetastoreItemInterface object from
 * just an identifier.
 */
interface MetastoreItemFactoryInterface extends FactoryInterface {

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityRepository $entityRepository
   *   Entity Repository service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   Entity Type Manager service.
   */
  public function __construct(EntityRepository $entityRepository, EntityTypeManager $entityTypeManager);

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
  public function getInstance(string $identifier, array $config = []): MetastoreItemInterface;

  /**
   * Wrap an arbitrary object as a metastore item interface compliant object.
   *
   * @param object $input
   *   Any object that can be wrapped as a metastore item. For instance, a node.
   *
   * @return \Drupal\metastore\MetastoreItemInterface
   *   A wrapper that implements MetastoreItemInterface.
   */
  public function wrap(object $input): MetastoreItemInterface;

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
