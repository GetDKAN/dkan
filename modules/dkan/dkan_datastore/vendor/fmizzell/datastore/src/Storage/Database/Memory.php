<?php
namespace Dkan\Datastore\Storage\Database;

use Dkan\Datastore\Storage\Database\Query\Insert;
use Dkan\Datastore\Storage\IDatabase;

class Memory implements IDatabase {
  public $tables = [];

  public function tableExist($table_name) {
    return isset($this->tables[$table_name]);
  }

  public function tableCreate($table_name, $scema) {
    $this->tables[$table_name] = [];
  }

  public function tableDrop($table_name) {
    unset($this->tables[$table_name]);
  }

  public function count($table_name) {
    if ($this->tableExist($table_name)) {
      return count($this->tables[$table_name]);
    }
    throw new \Exception("Table {$table_name} does not exist.");
  }

  public function insert(Insert $query) {
    if ($this->tableExist($query->tableName)) {
      foreach ($query->values as $values) {
        $this->tables[$query->tableName][] = json_encode($values);
      }
    }
  }

}