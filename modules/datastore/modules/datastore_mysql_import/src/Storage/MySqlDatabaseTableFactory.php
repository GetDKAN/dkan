<?php

namespace Drupal\datastore_mysql_import\Storage;

use Drupal\datastore\Storage\DatabaseTableFactory;

/**
 * DatabaseTable data object factory.
 */
class MySqlDatabaseTableFactory extends DatabaseTableFactory {

  /**
   * Protected.
   */
  protected function getDatabaseTable($resource) {
    return new MySqlDatabaseTable($this->connection, $resource);
  }

}
