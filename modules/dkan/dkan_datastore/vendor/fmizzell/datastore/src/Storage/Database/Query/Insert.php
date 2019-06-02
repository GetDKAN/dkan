<?php

namespace Dkan\Datastore\Storage\Database\Query;

class Insert {
  public $tableName;
  public $fields;
  public $values;

  public function __construct($table_name) {
    $this->tableName = $table_name;
  }

  public function fields($fields) {
    $this->fields = $fields;
  }

  public function values($values) {
    $this->values[] = $values;
  }

}