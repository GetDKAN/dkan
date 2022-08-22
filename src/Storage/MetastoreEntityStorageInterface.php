<?php

namespace Drupal\dkan\Storage;

use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Storage interface specifically for using drupal entities.
 */
interface MetastoreEntityStorageInterface extends MetastoreStorageInterface {

  /**
   * Get entity storage.
   *
   * @return \Drupal\Core\Entity\EntityStorageInterface
   *   Entity storage.
   */
  public function getEntityStorage(): EntityStorageInterface;

}
