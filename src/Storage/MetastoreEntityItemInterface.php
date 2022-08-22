<?php

namespace Drupal\dkan\Storage;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\dkan\MetastoreItemInterface;

interface MetastoreEntityItemInterface extends MetastoreItemInterface {

  /**
   * Build a MetastoreItem object around a Drupal entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Drupal content entity, such as a node.
   *
   * @return \Drupal\dkan\MetastoreItemInterface
   *   A MetastoreItem object.
   */
  public static function create(ContentEntityInterface $entity): MetastoreItemInterface;

  /**
   * Get the Drupal entity this item is built from.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   A Drupal content entity object.
   */
  public function entity(): ContentEntityInterface;

}
