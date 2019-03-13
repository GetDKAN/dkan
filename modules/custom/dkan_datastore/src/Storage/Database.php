<?php

namespace Drupal\dkan_datastore\Storage;

use Dkan\Datastore\Storage\Database\Query\Insert;
use Dkan\Datastore\Storage\IDatabase;

class Database implements IDatabase
{
  private $connection;

  public function __construct(\Drupal\Core\Database\Connection $connection) {
    $this->connection = $connection;
  }

  public function tableExist($table_name) {
    $exists = $this->connection->schema()->tableExists($table_name);
    return $exists;
  }

  public function tableCreate($table_name, $schema) {
    db_create_table($table_name, $schema);
  }

  public function tableDrop($table_name) {
    $this->connection->schema()->dropTable($table_name);
  }

  public function count($table_name) {
    if ($this->tableExist($table_name)) {
      $query = db_select($table_name);
      return $query->countQuery()->execute()->fetchField();
    }
    throw new \Exception("Table {$table_name} does not exist.");
  }

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

}