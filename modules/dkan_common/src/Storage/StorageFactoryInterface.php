<?php

namespace Drupal\common\Storage;

use Contracts\FactoryInterface;

/**
 * Interface for storage factories.
 *
 * This interface represents the way you should get a storage object from a
 * factory.
 *
 * @todo Remove dependency on \Contracts\FactoryInterface.
 */
interface StorageFactoryInterface extends FactoryInterface {

  /**
   * Construct or deliver a storage object.
   *
   * For example a MemoryStorage factory should return MemoryStorage objects.
   *
   * @param string $identifier
   *   Any string identifier. Generally, this identifier will be used to derive
   *   a database table name. Different storage types will use this identifier
   *   differently. Some accept a class name and use it to derive a database
   *   table name. Others will create a database table name by combining the
   *   identifier with another string.
   * @param array $config
   *   (optional) Configuration for the storage object.
   *
   * @return \Drupal\common\Storage\DatabaseTableInterface
   *   A storage object ready for use.
   */
  public function getInstance(string $identifier, array $config = []) : DatabaseTableInterface;

}
