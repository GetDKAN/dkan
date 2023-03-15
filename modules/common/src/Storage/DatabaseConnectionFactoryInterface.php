<?php

namespace Drupal\common\Storage;

use Drupal\Core\Database\Connection;

/**
 * Database connection factory interface.
 */
interface DatabaseConnectionFactoryInterface {

  /**
   * Builds a database connection object.
   *
   * @return \Drupal\Core\Database\Connection
   *   Database connection object.
   */
  public function getConnection(): Connection;

  /**
   * Set timeout to use when the connection object is built.
   *
   * @param int $timeout
   *   Timeout for database connection.
   *
   * @return self
   *   Return `$this` for chaining.
   */
  public function setConnectionTimeout(int $timeout): self;

}
