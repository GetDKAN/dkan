<?php

namespace Drupal\common\Storage;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;

/**
 * A factory for making database connections with special connection info.
 *
 * This class is meant to be subclassed, and that the subclass is probably a
 * factory service for a given module's special connection needs.
 *
 * - Override $this->key and/or $this->target to specify a new set of
 *   connection info.
 * - Call getConnection() to get a new connection from the key/target
 *   specified.
 * - Override buildConnectionInfo() to add special connection info to the new
 *   target.
 * - Override prepareConnection() to modify any connection created by the
 *   factory.
 *
 * It is assumed that the database target we are creating is not already
 * set up in settings.php, other than default/default.
 */
abstract class AbstractDatabaseConnectionFactory implements DatabaseConnectionFactoryInterface {

  /**
   * Database connection info key.
   */
  protected string $key = 'default';

  /**
   * Database connection info target.
   */
  protected string $target = 'default';

  /**
   * Build database connection factory.
   *
   * Adds connection info for the connection being built.
   */
  public function __construct() {
    $source_key = 'default';
    $source_target = 'default';
    // Add a new connection key/target based on default/default if the key or
    // the target have been overridden.
    if ($source_key !== $this->key || $source_target !== $this->target) {
      Database::addConnectionInfo(
        $this->key,
        $this->target,
        $this->buildConnectionInfo($source_key, $source_target)
      );
    }
  }

  /**
   * Specify database connection info for our configuration.
   *
   * Override this method in your subclass and call the parent method to modify
   * the connection info.
   *
   * This method is called once when the service is initialized.
   *
   * @param string $source_key
   *   Source key to copy.
   * @param string $source_target
   *   Source target to copy.
   *
   * @return array
   *   Database connection info array, which will be added at our key/target.
   */
  protected function buildConnectionInfo(string $source_key = 'default', string $source_target = 'default'): array {
    return Database::getConnectionInfo($source_key)[$source_target];
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
   * Modify the given database connection instance.
   *
   * This method is called when a new connection object is created by the
   * factory service.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The connection instance to modify.
   */
  abstract protected function prepareConnection(Connection $connection): void;

}
