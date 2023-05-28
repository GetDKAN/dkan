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

  /**
   * {@inheritDoc}
   *
   * @throws \Exception
   *   Can throw any DB-related exception. Notably, can throw
   *   \Drupal\Core\Database\SchemaObjectExistsException if the table already
   *   exists when we try to create it.
   */
  public function setTable(): void {
    // Never check for pre-existing table, never catch exceptions.
    if ($this->schema) {
      $this->tableCreate($this->getTableName(), $this->schema);
    }
    else {
      throw new \Exception('Could not instantiate the table due to a lack of schema.');
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
