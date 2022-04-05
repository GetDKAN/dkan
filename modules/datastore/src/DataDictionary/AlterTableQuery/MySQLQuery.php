<?php

namespace Drupal\datastore\DataDictionary\AlterTableQuery;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\StatementInterface;

use Drupal\datastore\DataDictionary\AlterTableQueryInterface;
use Drupal\datastore\DataDictionary\Exception\IncompatibleTypeException;
use Drupal\datastore\DataDictionary\DateFormatConverterInterface;

/**
 * MySQL table alter query.
 */
class MySQLQuery implements AlterTableQueryInterface {

  /**
   * Max total size of the MySQL decimal type.
   *
   * @var int
   */
  protected const DECIMAL_MAX_SIZE = 65;

  /**
   * Max decimal size of the MySQL decimal type.
   *
   * @var int
   */
  protected const DECIMAL_MAX_DECIMAL = 30;

  /**
   * Build a MySQL table alter query.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \Drupal\datastore\DataDictionary\DateFormatConverterInterface $date_format_converter
   *   Frictionless date format converter.
   * @param string $datastore_table
   *   Datastore table.
   * @param array $dictionary_fields
   *   Data-dictionary fields.
   */
  public function __construct(
      Connection $connection,
      DateFormatConverterInterface $date_format_converter,
      string $datastore_table,
      array $dictionary_fields
  ) {
    $this->connection = $connection;
    $this->dateFormatConverter = $date_format_converter;
    $this->datastoreTable = $this->connection->escapeTable($datastore_table);
    $this->dictionaryFields = $dictionary_fields;
  }

  /**
   * {@inheritdoc}
   */
  public function applyDataTypes(): void {
    $this->dictionaryFields = $this->filterForDatastoreFields($this->dictionaryFields, $this->datastoreTable);

    // Build and execute SQL commands to prepare for table alter.
    $pre_alter_commands = $this->buildPreAlterCommands($this->dictionaryFields, $this->datastoreTable);
    array_map(fn ($cmd) => $cmd->execute(), $pre_alter_commands);
    // Build and execute SQL command to perform table alter.
    $this->buildAlterCommand($this->dictionaryFields, $this->datastoreTable)->execute();
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
    return $this->connection->query("DESCRIBE {{$table}};")->fetchCol();
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
    // Build the MySQL type argument list for the given Frictionless data type.
    $args = $this->buildTypeArgs($frictionless_type, $column, $table);

    // Build full MySQL type for the given Frictionless data type.
    return ([
      'string'    => (fn () => 'TEXT'),
      'number'    => (fn ($args) => "DECIMAL({$args['size']}, {$args['decimal']})"),
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
   * Build MySQL type argument list for the given type.
   *
   * @param string $type
   *   Frictionless type.
   * @param string $column
   *   Column name.
   * @param string $table
   *   Table name.
   *
   * @throws Drupal\datastore\DataDictionary\Exception\IncompatibleTypeException
   *   When incompatible data is found in the table for the specified type.
   */
  protected function buildTypeArgs(string $type, string $column, string $table): array {
    $args = [];

    // If this field is a number field, build decimal and size arguments for
    // MySQL type.
    if ($type === 'number') {
      $non_decimals = $this->connection->query("SELECT MAX(LENGTH(TRIM(LEADING '-' FROM SUBSTRING_INDEX({$column}, '.', 1)))) FROM {{$table}};")->fetchField();
      $args['decimal'] = $this->connection->query("SELECT MAX(LENGTH(SUBSTRING_INDEX({$column}, '.', -1))) FROM {{$table}};")->fetchField();
      $args['size'] = $non_decimals + $args['decimal'];
      if ($args['size'] > self::DECIMAL_MAX_SIZE || $args['decimal'] > self::DECIMAL_MAX_DECIMAL) {
        throw new IncompatibleTypeException("Decimal values found in column too large for DECIMAL type; please use type 'string' for column '{$column}'");
      }
    }

    return $args;
  }

  /**
   * Build list of commands to prepare table for alter command.
   *
   * @param array $dict
   *   Data dictionary fields.
   * @param string $table
   *   Mysql table name.
   *
   * @return \Drupal\Core\Database\StatementInterface[]
   *   Prep command statements.
   */
  protected function buildPreAlterCommands(array $dict, string $table): array {
    $pre_alter_cmds = [];

    // Build pre-alter commands for each dictionary field.
    foreach ($dict as ['name' => $col, 'type' => $type, 'format' => $format]) {
      // If this field is a date field, and a valid format is provided; update
      // the format of the date fields to ISO-8601 before importing into MySQL.
      if ($type === 'date' && !empty($format) && $format !== 'default') {
        $mysql_date_format = $this->dateFormatConverter->convert($format);
        $pre_alter_cmds[] = $this->connection->update($table)->expression($col, "STR_TO_DATE({$col}, :date_format)", [
          ':date_format' => $mysql_date_format,
        ]);
      }
    }

    return $pre_alter_cmds;
  }

  /**
   * Build alter command to modify table column data types.
   *
   * @param array $dictionary_fields
   *   Data dictionary fields.
   * @param string $table
   *   Mysql table name.
   *
   * @return \Drupal\Core\Database\StatementInterface
   *   Prepared MySQL table alter command statement.
   */
  protected function buildAlterCommand(array $dictionary_fields, string $table): StatementInterface {
    $modify_lines = [];
    $args = [];

    foreach ($dictionary_fields as ['name' => $field, 'type' => $type]) {
      // Get MySQL type for column.
      $column_type = $this->getType($type, $field, $table);
      // Build modify line for alter command and add the appropriate arguments
      // to the args list.
      $modify_lines[] = "MODIFY COLUMN {$field} {$column_type}";
    }

    return $this->connection->prepareStatement("ALTER TABLE {{$table}} " . implode(', ', $modify_lines) . ';', $args);
  }

}
