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
}