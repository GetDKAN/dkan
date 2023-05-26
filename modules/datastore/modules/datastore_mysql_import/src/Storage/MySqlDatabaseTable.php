<?php

namespace Drupal\datastore_mysql_import\Storage;

use Drupal\common\Storage\Query;
use Drupal\common\Storage\SelectFactory;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\DatabaseExceptionWrapper;
use Drupal\datastore\Storage\DatabaseTable;

/**
 * MySQL import database table.
 */
class MySqlDatabaseTable extends DatabaseTable {

  /**
   * Create the table in the db if it does not yet exist.
   *
   * @throws \Exception
   *   Can throw any DB-related exception. Notably, can throw
   *   \Drupal\Core\Database\SchemaObjectExistsException if the table already
   *   exists when we try to create it.
   */
  protected function setTable() {
    // Never check for pre-existing table, never catch exceptions.
    if ($this->schema) {
      $this->tableCreate($this->getTableName(), $this->schema);
    }
    else {
      throw new \Exception('Could not instantiate the table due to a lack of schema.');
    }
  }

  /**
   * {@inheritDoc}
   *
   * Our subclass rearranges the DB config and creates a new session with
   * innodb_strict_mode turned OFF, so that we can handle arbitrarily wide
   * table schema.
   */
  protected function tableCreate($table_name, $schema) {
    // Store the DB stuff...
    $active_db = Database::setActiveConnection();
    $active_connection = $this->connection;

    // Swap out for our reconfigured DB.
    $options = Database::getConnectionInfo($active_db);
    // When Drupal opens the connection, it will set up the session with this
    // command to turn off innodb_strict_mode.
    $options['default']['init_commands']['wide_tables'] = 'SET SESSION innodb_strict_mode=OFF';

    Database::addConnectionInfo('dkan_wide_tables', 'default', $options['default']);
    Database::setActiveConnection('dkan_wide_tables');

    $this->connection = Database::getConnection();
    try {
      parent::tableCreate($table_name, $schema);
    }
    catch (\Throwable $e) {
      throw $e;
    }
    finally {
      // Always reset the connection, even if there was an exception.
      Database::setActiveConnection($active_db);
      $this->connection = $active_connection;
    }
  }

  /**
   * Run a query on the database table.
   *
   * @param \Drupal\common\Storage\Query $query
   *   Query object.
   * @param string $alias
   *   (Optional) alias for primary table.
   * @param bool $fetch
   *   Fetch the rows if true, just return the result statement if not.
   *
   * @return array|\Drupal\Core\Database\StatementInterface
   *   Array of results if $fetch is true, otherwise result of
   *   Select::execute() (prepared Statement object or null).
   */
  public function query(Query $query, string $alias = 't', $fetch = TRUE) {
    if (!$this->tableExist($this->getTableName())) {
      throw new \Exception('Could not instantiate the table due to a lack of schema.');
    }
    $query->collection = $this->getTableName();
    $selectFactory = new SelectFactory($this->connection, $alias);
    $db_query = $selectFactory->create($query);

    try {
      $result = $db_query->execute();
    }
    catch (DatabaseExceptionWrapper $e) {
      throw new \Exception($this->sanitizedErrorMessage($e->getMessage()));
    }

    return $fetch ? $result->fetchAll() : $result;
  }

}
