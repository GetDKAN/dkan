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
   * Timeout for built database connections in seconds.
   *
   * @var int
   */
  protected int $timeout;

  /**
   * Database connection target name.
   *
   * @var string
   */
  protected string $target = 'default';

  /**
   * Database connection key.
   *
   * @var string
   */
  protected string $key = 'default';

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
    return Database::getConnectionInfo()[$this->target];
  }

  /**
   * {@inheritdoc}
   */
  public function getConnection(): Connection {
    $connection = Database::getConnection($this->target, $this->key);
    $this->prepareConnection($connection);

    return $connection;
  }

  /**
   * Prepare database connection.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection object.
   */
  protected function prepareConnection(Connection $connection): void {
    $this->doSetConnectionTimeout($connection);
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
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection object.
   */
  protected function doSetConnectionTimeout(Connection $connection): void {
    if (isset($this->timeout)) {
      $connection->query('SET SESSION wait_timeout = ' . $this->timeout);
    }
  }

}
