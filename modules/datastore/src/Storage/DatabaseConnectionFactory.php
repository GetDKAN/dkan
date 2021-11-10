<?php

namespace Drupal\datastore\Storage;

use Drupal\Core\Database\Database;

/**
 * Create second datastore endpoint at runtime with unbuffered queries.
 *
 * @return \Drupal\Core\Database\Connection
 *   New datastore connection object.
 */
class DatabaseConnectionFactory {

  /**
   * Gets the connection object for the specified database key and target.
   */
  public static function getConnection() {
    $info = Database::getConnectionInfo();
    $datastoreInfo = $info['default'];
    $datastoreInfo['pdo'][\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = FALSE;
    Database::addConnectionInfo('datastore', 'default', $datastoreInfo);
    return Database::getConnection('default', 'datastore');
  }

}
