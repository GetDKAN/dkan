<?php

namespace Drupal\datastore_mysql_import\DataDictionary\AlterTableQuery;

use Drupal\Core\Database\Database;
use Drupal\datastore\DataDictionary\AlterTableQuery\MySQLQuery;
use Drupal\datastore\DataDictionary\AlterTableQueryInterface;

/**
 * MySQL table alter query for wide tables.
 *
 * This class overrides the execute() method so that we can create a special
 * database session with innodb_strict_mode turned off.
 */
class NoStrictMySQLQuery extends MySQLQuery implements AlterTableQueryInterface {

  /**
   * {@inheritDoc}
   */
  public function execute(): void {
    // Keep track of DB configuration.
    $active_db = Database::setActiveConnection();
    $active_connection = $this->connection;

    // Get the config so we can modify it.
    $options = Database::getConnectionInfo($active_db);
    // When Drupal opens the connection, it will run init_commands and set up
    // the session to turn off innodb_strict_mode.
    $options['default']['init_commands']['wide_tables'] = 'SET SESSION innodb_strict_mode=OFF';

    // Activate our bespoke session so we can call our parent method.
    Database::addConnectionInfo('alter_dkan_strict_off', 'default', $options['default']);
    Database::setActiveConnection('alter_dkan_strict_off');
    $this->connection = Database::getConnection();

    // Special config active, execute.
    try {
      parent::execute();
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

}
