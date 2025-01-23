<?php

namespace Drupal\common\Storage;

use Drupal\Core\Database\Connection;

/**
 * Database connection factory that can set a connection timeout.
 */
class DatabaseConnectionFactory extends AbstractDatabaseConnectionFactory implements DatabaseConnectionFactoryInterface {

  /**
   * Timeout for database connections in seconds.
   */
  protected int $timeout;

  /**
   * {@inheritDoc}
   */
  protected function prepareConnection(Connection $connection): void {
    if (isset($this->timeout)) {
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
