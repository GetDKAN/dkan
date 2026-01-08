<?php

namespace Drupal\datastore\Storage;

/**
 * Database table storage class modified for SQLite.
 *
 * Currently only intended for use in unit tests; further testing
 * needed to determine whether could support a full data catalog.
 */
class SqliteDatabaseTable extends DatabaseTable {

  /**
   * {@inheritdoc}
   */
  protected function setSchemaFromTable() {
    $tableName = $this->getTableName();
    $fieldsInfo = $this->connection->query("PRAGMA table_info('{$tableName}')")
      ->fetchAll();

    $schema = $this->buildTableSchema($tableName, $fieldsInfo);
    $this->setSchema($schema);
  }

  /**
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  protected function translateType(string $describe_type, $extra = NULL) {
    // Clean up things like "int(10) unsigned".
    $driver = $this->connection->driver() ?? 'sqlite';
    $db_type = strtolower($describe_type);
    $map = array_flip(array_map('strtolower', $this->connection->schema()->getFieldTypeMap()));
    $length = NULL;

    $fullType = explode(':', ($map[$db_type] ?? 'varchar'));
    // Set type to serial if auto-increment, else use mapped type.
    $notNull = ($extra->notnull == 1) ? TRUE : NULL;
    // Ignore size if "normal" or unset.
    $size = (isset($fullType[1]) && $fullType[1] != 'normal') ? $fullType[1] : NULL;

    return [
      'type' => $describe_type,
      'length' => $length,
      'size' => $size,
      'not null' => $notNull,
      "{$driver}_type" => $db_type,
    ];
  }

}
