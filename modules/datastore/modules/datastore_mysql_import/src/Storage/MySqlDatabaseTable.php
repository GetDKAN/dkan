<?php

namespace Drupal\datastore_mysql_import\Storage;

use Drupal\common\Storage\Query;
use Drupal\common\Storage\SelectFactory;
use Drupal\Core\Database\DatabaseExceptionWrapper;
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

}
