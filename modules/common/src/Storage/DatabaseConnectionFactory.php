<?php

namespace Drupal\common\Storage;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;

/**
 * Create database connection.
 *
 * @return \Drupal\Core\Database\Connection
 *   New connection object.
 */
class DatabaseConnectionFactory implements DatabaseConnectionFactoryInterface {

  /**
   * {@inheritdoc}
   */
  protected int $timeout;

  /**
   * {@inheritdoc}
   */
  protected string $target = 'default';

  /**
   * {@inheritdoc}
   */
  protected ?string $key = NULL;

  /**
   * Build database connection factory.
   *
   * Adds connection info for the connection being built.
   */
  public function __construct() {
    Database::addConnectionInfo($this->key, $this->target, $this->buildConnectionInfo());
  }

  /**
   * {@inheritdoc}
   */
  protected function buildConnectionInfo(): array {
    return [
      $this->target => Database::getConnectionInfo(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConnection(): Connection {
    $connection = Database::getConnection($this->target, $this->key);
    $this->doSetConnectionTimeout($connection);

    return $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function setConnectionTimeout(int $timeout): self {
    $this->timeout = $timeout;

    return $this;
  }


  /**
   * Set the timeout on the supplied connection object.
   *
   * @param \Drupal\Core\Database\Connection
   *   Database connection object.
   */
  protected function doSetConnectionTimeout(Connection $connection): void {
    if (isset($this->timeout)) {
      $connection->query('SET SESSION wait_timeout = ' . $this->timeout);
    }
  }

}
