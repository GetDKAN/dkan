<?php

namespace Drupal\dkan_datastore\Storage;

use Drupal\Core\Database\Connection;
use Dkan\Datastore\Storage\Database\Query\Insert;
use Dkan\Datastore\Storage\IDatabase;
use Drupal\dkan_datastore\Query;

/**
 *
 */
class Database implements IDatabase {
  private $connection;

  /**
   *
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   *
   */
  public function tableExist($table_name) {
    $exists = $this->connection->schema()->tableExists($table_name);
    return $exists;
  }

  /**
   *
   */
  public function tableCreate($table_name, $schema) {
    db_create_table($table_name, $schema);
  }

  /**
   *
   */
  public function tableDrop($table_name) {
    $this->connection->schema()->dropTable($table_name);
  }

  /**
   *
   */
  public function count($table_name) {
    if ($this->tableExist($table_name)) {
      $query = db_select($table_name);
      return $query->countQuery()->execute()->fetchField();
    }
    throw new \Exception("Table {$table_name} does not exist.");
  }

  /**
   *
   */
  public function insert(Insert $query) {
    if ($this->tableExist($query->tableName)) {
      $q = db_insert($query->tableName);
      $q->fields($query->fields);
      foreach ($query->values as $values) {
        $q->values($values);
      }
      $q->execute();
    }
  }

  /**
   *
   */
  public function query(Query $query): array {
    $db_query = $this->connection->select($query->thing, 't');
    $db_query->fields('t', $query->properties);

    foreach ($query->conditions as $property => $value) {
      $db_query->condition($property, $value, "LIKE");
    }

    foreach ($query->sort['ASC'] as $property) {
      $db_query->orderBy($property);
    }

    foreach ($query->sort['DESC'] as $property) {
      $db_query->orderBy($property, 'DESC');
    }

    if ($query->limit) {
      if ($query->offset) {
        $db_query->range($query->offset, $query->limit);
      }
      else {
        $db_query->range(1, $query->limit);
      }
    }
    elseif ($query->offset) {
      $db_query->range($query->limit);
    }

    $result = $db_query->execute()->fetchAll();

    return $result;
  }

}
