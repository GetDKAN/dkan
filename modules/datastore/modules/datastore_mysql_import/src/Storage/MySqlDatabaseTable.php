<?php

namespace Drupal\datastore_mysql_import\Storage;

use Drupal\common\Storage\ImportedItemInterface;
use Drupal\Core\Database\Database;
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
class MySqlDatabaseTable extends DatabaseTable implements ImportedItemInterface {

  /**
   * Whether strict mode is disabled for this table.
   */
  protected bool $strictModeDisabled = FALSE;

  /**
   * Set whether strict mode is disabled for this table.
   *
   * @param bool $disable
   *   TRUE to disable strict mode, FALSE to enable it.
   */
  public function setStrictModeDisabled(bool $disable): void {
    $this->strictModeDisabled = $disable;
  }

  /**
   * Check if strict mode is disabled for this table.
   *
   * @return bool
   *   TRUE if strict mode is disabled, FALSE otherwise.
   */
  public function isStrictModeDisabled(): bool {
    return $this->strictModeDisabled;
  }

  /**
   * Disable strict mode for this table.
   *
   * This method sets up a new database connection with the appropriate
   * configuration to disable strict mode, allowing us to create tables with
   * wide schemas.
   *
   * @param string $active_db
   *   The name of the active database connection.
   */
  protected function disableStrictMode(string $active_db): void {
    // Get the config so we can modify it.
    $options = Database::getConnectionInfo($active_db);
    // When Drupal opens the connection, it will run init_commands and set up
    // the session to turn off innodb_strict_mode.
    $options['default']['init_commands']['wide_tables'] = 'SET SESSION innodb_strict_mode=OFF';

    // Activate our bespoke session so we can call parent::tableCreate().
    Database::addConnectionInfo('dkan_strict_off', 'default', $options['default']);
    Database::setActiveConnection('dkan_strict_off');
    $this->connection = Database::getConnection();
  }

  /**
   * {@inheritDoc}
   *
   * Our subclass rearranges the DB config and creates a new session with
   * innodb_strict_mode turned OFF, so that we can handle arbitrarily wide
   * table schema.
   */
  protected function tableCreate($table_name, $schema) {
    // Keep track of DB configuration.
    $active_db = Database::setActiveConnection();
    $active_connection = $this->connection;

    if ($this->strictModeDisabled) {
      // If strict mode is to be disabled, we need to set up a new connection
      // with the appropriate settings.
      $this->disableStrictMode($active_db);
    }
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
   * For datastore_mysql_import, at this point we only check if the table exists
   * in the database and has more than 0 rows. This is because the importer is
   * assumed to have used LOAD DATA LOCAL INFILE to import the data in one step.
   *
   * @see \Drupal\datastore_mysql_import\Service\MysqlImport::getSqlStatement
   */
  public function hasBeenImported(): bool {
    if ($this->tableExist($this->getTableName())) {
      return $this->count() > 0;
    }
    return FALSE;
  }

}
