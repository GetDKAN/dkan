<?php

namespace Drupal\datastore_mysql_import\Storage;

use Drupal\datastore\Storage\DatabaseTable;

/**
 * Database storage object for MySQL imports.
 */
class MySqlDatabaseTable extends DatabaseTable {

  /**
   * {@inheritDoc}
   */
  protected function setTable() {
    // If the table exists already, we want to throw an exception. This allows
    // us to account for timed-out CSV imports.
    $table_name = $this->getTableName();
    if ($this->tableExist($table_name)) {
      throw new MySqlDatabaseTableExistsException('Table already exists: ' . $table_name);
    }
    parent::setTable();
  }

}
