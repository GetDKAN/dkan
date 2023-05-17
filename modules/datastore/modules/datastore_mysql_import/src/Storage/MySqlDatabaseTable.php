<?php

namespace Drupal\datastore_mysql_import\Storage;

use Drupal\Core\Database\SchemaObjectExistsException;
use Drupal\datastore\Storage\DatabaseTable;

/**
 * MySQL import database table.
 */
class MySqlDatabaseTable extends DatabaseTable {

  /**
   * {@inheritDoc}
   *
   * For MySqlDatabaseTable, the table is valid if it exists in the DB and has
   * any rows at all. We assume that the LOAD DATA LOCAL INFILE style of import
   * is all-or-nothing: MySQL will either succeed and add the table, or will not
   * add the table if there is any error.
   */
  public function validate(): bool {
    if ($this->tableExist($this->getTableName())) {
      try {
        return $this->count(FALSE) > 0;
      }
      catch (SchemaObjectExistsException $e) {
        // No op.
      }
    }
    return FALSE;
  }

  /**
   * {@inheritDoc}
   *
   * @todo: Allow DatabaseTable::setTable() to follow this behavior and never
   *   catch throwables.
   */
  public function setTable(): void {
    // Never check for pre-existing table, never catch exceptions.
    if ($this->schema) {
      $this->tableCreate($this->getTableName(), $this->schema);
    }
    else {
      throw new \Exception("Could not instantiate the table due to a lack of schema.");
    }
  }

}
