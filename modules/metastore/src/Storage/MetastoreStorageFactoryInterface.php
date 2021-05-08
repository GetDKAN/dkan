<?php

namespace Drupal\metastore\Storage;

use Contracts\FactoryInterface;

interface MetastoreStorageFactoryInterface extends FactoryInterface {

  /**
   * Override Contracts\FactoryInterface to return only Metastore storage.
   *
   * @param string $identifier
   *   A metastore item identifier
   * @param array $config
   *   Existing config.
   *
   * @return Drupal\metastore\Storage\MetastoreStorageInterface
   *   A metastore storage obeject.
   */
  public function getInstance(string $identifier, array $config = []):MetastoreStorageInterface;

  /**
   * Get the storage class this factory will use.
   *
   * @return string
   *   The metastore storage class used by this factory.
   */
  public static function getStorageClass();

}