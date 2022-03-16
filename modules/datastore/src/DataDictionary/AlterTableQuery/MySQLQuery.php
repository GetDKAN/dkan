<?php

namespace Drupal\datastore\DataDictionary\AlterTableQuery;

use Drupal\Core\Database\Connection;
use Drupal\datastore\DataDictionary\AlterTableQueryInterface;
use Drupal\datastore\DataDictionary\FrictionlessDateFormatConverterInterface;

/**
 * MySQL table alter query.
 */
class MySQLQuery implements AlterTableQueryInterface {

  /**
   * Build a MySQL table alter query.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \Drupal\datastore\DataDictionary\FrictionlessDateFormatConverterInterface $date_format_converter
   *   Frictionless date format converter.
   * @param string $datastore_table
   *   Datastore table.
   * @param array $dictionary_fields
   *   Data-dictionary fields.
   */
  public function __construct(
      Connection $connection,
      FrictionlessDateFormatConverterInterface $date_format_converter,
      string $datastore_table,
      array $dictionary_fields
  ) {
    $this->connection = $connection;
    $this->dateFormatConverter = $date_format_converter;
    $this->datastoreTable = $datastore_table;
    $this->dictionaryFields = $dictionary_fields;
  }

  /**
   * {@inheritdoc}
   */
  public function applyDataTypes(): void {
    $this->dictionaryFields = $this->filterForDatastoreFields($this->dictionaryFields, $this->datastoreTable);

    array_map([$this->connection, 'query'], $this->buildPreAlterCommands($this->dictionaryFields, $this->datastoreTable));
    $this->connection->query($this->buildAlterCommand($this->dictionaryFields, $this->datastoreTable));
  }

  /**
   * Filter out dictionary fields not found in the given table.
   *
   * @param array $dictionary_fields
   *   Data dictionary fields.
   * @param string $table
   *   Mysql table to filter against.
   *
   * @return array
   *   Filtered list of applicable data dictionary fields.
   */
  protected function filterForDatastoreFields(array $dictionary_fields, string $table): array {
    $table_cols = $this->getTableCols($table);

    return array_filter($dictionary_fields, fn ($fields) => in_array($fields['name'], $table_cols, TRUE));
  }

  /**
   * Get list of MySQL table field names.
   *
   * @param string $table
   *   Table name.
   *
   * @return string[]
   *   List of column names.
   */
  protected function getTableCols(string $table): array {
    return $this->connection->query("DESCRIBE {$table};")->fetchCol();
  }

  /**
   * Get MySQL data type equivalent of the given frictionless data type.
   *
   * @param string $frictionless_type
   *   Frictionless data type.
   * @param string $column
   *   MySQL table column to get type for.
   * @param string $table
   *   MySQL table to get type for.
   *
   * @return string
   *   MySQL data type.
   */
  protected function getType(string $frictionless_type, string $column, string $table): string {
    $args = [];
    if ($frictionless_type === 'number') {
      $args['size'] = $this->connection->query("SELECT MAX(LENGTH({$column})) FROM {$table};")->fetchField();
      $args['decimal'] = $this->connection->query("SELECT MAX(LENGTH(SUBSTRING_INDEX({$column}, '.', -1))) FROM {$table};")->fetchField();
    }

    return ([
      'string'    => (fn () => 'TEXT'),
      'number'    => (fn ($args) => "DOUBLE({$args['size']}, {$args['decimal']})"),
      'integer'   => (fn () => 'INT'),
      'date'      => (fn () => 'DATE'),
      'time'      => (fn () => 'TIME'),
      'datetime'  => (fn () => 'DATETIME'),
      'year'      => (fn () => 'YEAR'),
      'yearmonth' => (fn () => 'TINYTEXT'),
      'boolean'   => (fn () => 'BOOL'),
      'object'    => (fn () => 'TEXT'),
      'geopoint'  => (fn () => 'TEXT'),
      'geojson'   => (fn () => 'TEXT'),
      'array'     => (fn () => 'TEXT'),
      'duration'  => (fn () => 'TINYTEXT'),
    ])[$frictionless_type]($args);
  }

  /**
   * Build list of commands to prepare table for alter command.
   *
   * @param array $dictionary_fields
   *   Data dictionary fields.
   * @param string $table
   *   Mysql table name.
   *
   * @return string[]
   *   Prep commands list.
   */
  protected function buildPreAlterCommands(array $dictionary_fields, string $datastore_table): array {
    $pre_alter_cmds = [];

    foreach ($dictionary_fields as ['name' => $field, 'type' => $type, 'format' => $format]) {
      if ($type === 'date') {
        $mysql_date_format = $this->dateFormatConverter->convert($format);
        $pre_alter_cmds[] = "UPDATE {$datastore_table} SET {$field} = STR_TO_DATE({$field}, '{$mysql_date_format}');";
      }
    }

    return $pre_alter_cmds;
  }

  /**
   * Build alter command to modify table column data types.
   *
   * @param array $dictionary_fields
   *   Data dictionary fields.
   * @param string $datastore_table
   *   Mysql table name.
   *
   * @return string
   *   MySQL table alter command.
   */
  protected function buildAlterCommand(array $dictionary_fields, string $datastore_table): string {
    $modify_lines = [];

    foreach ($dictionary_fields as ['name' => $field, 'type' => $type]) {
      $column_type = $this->getType($type, $field, $datastore_table);
      $modify_lines[] = "MODIFY COLUMN {$field} {$column_type}";
    }

    return "ALTER TABLE {$datastore_table} " . implode(', ', $modify_lines) . ';';
  }

}
