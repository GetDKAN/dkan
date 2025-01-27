<?php

namespace Drupal\common\Storage;

use Drupal\Core\Database\Connection;

/**
 * Database connection factory that can set a connection timeout.
 *
 * This is the dkan.common.database_connection_factory service.
 *
 * @todo Services should not contain state, such as the timeout property here.
 *   We should have a way to set the timeout as an argument to getConnection().
 */
class DatabaseConnectionFactory extends AbstractDatabaseConnectionFactory {

  /**
   * Timeout for database connections in seconds.
   */
  protected int $timeout;

  /**
   * {@inheritDoc}
   */
  protected function prepareConnection(Connection $connection): void {
    if (isset($this->timeout)) {
      // @see https://github.com/GetDKAN/dkan/pull/3764
      $connection->query('SET SESSION wait_timeout = ' . $this->timeout);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setConnectionTimeout(int $timeout): self {
    $this->timeout = $timeout;

    return $this;
  }

}
