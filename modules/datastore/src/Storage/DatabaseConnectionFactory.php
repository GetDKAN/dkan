<?php

namespace Drupal\datastore\Storage;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;

/**
 * Creates a datastore DB connection with unbuffered queries.
 */
class DatabaseConnectionFactory {

  /**
   * Connection info.
   *
   * @var array
   */
  protected $connectionInfo;

  /**
   * Create a database connection factory object.
   */
  public function __construct() {
    $info = Database::getConnectionInfo();
    $this->connectionInfo = $info['default'];
    $this->connectionInfo['pdo'][\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = FALSE;
  }

  /**
   * Add init command used when create a database connection.
   *
   * @param string $command
   *   Connection initialization command.
   */
  public function addInitCommand(string $command): void {
    $this->connectionInfo['init_commands'][] = $command;
  }

  /**
   * Gets the connection object for the specified database key and target.
   *
   * @return \Drupal\Core\Database\Connection
   *   New datastore connection object.
   */
  public function getConnection(): Connection {
    Database::addConnectionInfo('datastore', 'default', $this->connectionInfo);
    return Database::getConnection('default', 'datastore');
  }

}
