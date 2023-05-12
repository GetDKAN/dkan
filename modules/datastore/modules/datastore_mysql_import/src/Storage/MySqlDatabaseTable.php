<?php

namespace Drupal\datastore_mysql_import\Storage;

use Drupal\Core\Database\SchemaObjectExistsException;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\Core\Database\Connection;

/**
 * Database storage object for MySQL imports.
 */
class MySqlDatabaseTable extends DatabaseTable implements MySqlDatabaseTableInterface {

  /**
   * {@inheritDoc}
   */
  protected function setTable() {
    if ($schema = $this->getSchema()) {
      try {
        $this->tableCreate($this->getTableName(), $schema);
      }
      catch (SchemaObjectExistsException $e) {
        throw new MySqlDatabaseTableExistsException($e->getMessage());
      }
    }
    else {
      throw new \Exception("Could not instantiate the table due to a lack of schema.");
    }
  }

}
