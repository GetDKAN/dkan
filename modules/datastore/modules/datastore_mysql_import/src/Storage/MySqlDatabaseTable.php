<?php

namespace Drupal\datastore_mysql_import\Storage;

use Drupal\Core\Database\Database;
use Drupal\Core\Database\SchemaObjectExistsException;
use Drupal\datastore\Storage\DatabaseTable;

/**
 * MySQL import database table.
 *
 * This table implementation turns innodb_strict_mode off when creating tables.
 * Turning off strict mode turns errors into warnings, allowing us to bend the
 * rules on number of columns per table, and column name width.
 *
 * @see https://dev.mysql.com/doc/refman/5.7/en/innodb-parameters.html#sysvar_innodb_strict_mode
 */
class MySqlDatabaseTable extends DatabaseTable {

  /**
   * {@inheritDoc}
   */
  protected function tableCreate(string $table_name, array $schema): void {
    // Keep track of DB configuration.
    $active_db = Database::setActiveConnection();
    $active_connection = $this->connection;

    // Get the config so we can modify it.
    $options = Database::getConnectionInfo($active_db);
    // When Drupal opens the connection, it will run init_commands and set up
    // the session to turn off innodb_strict_mode.
    $options['default']['init_commands']['wide_tables'] = 'SET SESSION innodb_strict_mode=OFF';

    // Activate our bespoke session so we can call parent::tableCreate().
    Database::addConnectionInfo('dkan_strict_off', 'default', $options['default']);
    Database::setActiveConnection('dkan_strict_off');
    $this->connection = Database::getConnection();

    // Special config active, let's create the table.
    try {
      parent::tableCreate($table_name, $schema);
    }
    catch (\Throwable $e) {
      throw $e;
    }
    finally {
      // Always try to reset the connection, even if there was an exception.
      Database::setActiveConnection($active_db);
      $this->connection = $active_connection;
    }
  }

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
