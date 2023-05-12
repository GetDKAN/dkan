<?php

namespace Drupal\datastore_mysql_import\Storage;

use Drupal\datastore\Storage\DatabaseTableFactory;

/**
 * MySQL import database table.
 */
class MySqlDatabaseTableFactory extends DatabaseTableFactory {

  protected function getDatabaseTable($resource) {
    return new MySqlDatabaseTable($this->connection, $resource);
  }

}
