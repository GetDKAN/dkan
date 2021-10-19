<?php

namespace Drupal\datastore\Storage;

class SqliteDatabaseTable extends DatabaseTable {

  /**
   * Set the schema using the existing database table.
   */
  protected function setSchemaFromTable() {
    $tableName = $this->getTableName();
    $fieldsInfo = $this->connection->query("PRAGMA table_info('{$tableName}')")
      ->fetchAll();

    $schema = $this->buildTableSchema($tableName, $fieldsInfo);
    $this->setSchema($schema);
  }

  /**
   * Get table schema.
   *
   * @todo Note that this will breakZ on PostgresSQL
   */
  protected function buildTableSchema($tableName, $fieldsInfo) {
    foreach ($fieldsInfo as $info) {
      $name = $info->name;
      $schema['fields'][$name] = $this->translateType(strtolower($info->type), $info);
      $schema['fields'][$name] = array_filter($schema['fields'][$name]);
    }
    return $schema ?? ['fields' => []];
  }

  /**
   *
   */
  public function translateType(string $type, $info = NULL) {
    // Clean up things like "int(10) unsigned".
    $driver = $this->connection->driver() ?? 'mysql';
    $db_type = strtolower($type);
    $map = array_flip(array_map('strtolower', $this->connection->schema()->getFieldTypeMap()));
    $length = NULL;

    $fullType = explode(':', ($map[$db_type] ?? 'varchar'));
    // Set type to serial if auto-increment, else use mapped type.
    $notNull = ($info->notnull == 1) ? TRUE : NULL;
    // Ignore size if "normal" or unset.
    $size = (isset($fullType[1]) && $fullType[1] != 'normal') ? $fullType[1] : NULL;

    return [
      'type' => $type,
      'length' => $length,
      'size' => $size,
      'not null' => $notNull,
      "{$driver}_type" => $db_type,
    ];
  }

}