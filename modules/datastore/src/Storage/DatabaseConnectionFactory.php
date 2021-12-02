<?php

namespace Drupal\datastore\Storage;

use Drupal\Core\Database\Database;

/**
 * Creates a datastore DB connection with unbuffered queries.
 */
class DatabaseConnectionFactory {

  /**
   * Create a database connection factory object.
   */
  public function __construct() {
    $info = Database::getConnectionInfo();
    $datastoreInfo = $info['default'];
    $datastoreInfo['pdo'][\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = FALSE;
    Database::addConnectionInfo('datastore', 'default', $datastoreInfo);
  }

  /**
   * Gets the connection object for the specified database key and target.
   *
   * @return \Drupal\Core\Database\Connection
   *   New datastore connection object.
   */
  public function getConnection() {
    return Database::getConnection('default', 'datastore');
  }

}
