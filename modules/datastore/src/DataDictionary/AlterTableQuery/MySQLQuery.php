<?php

namespace Drupal\datastore\DataDictionary\AlterTableQuery;

use Drupal\Core\Database\Connection;
use Drupal\Core\Database\StatementInterface;

use Drupal\datastore\DataDictionary\AlterTableQueryInterface;
use Drupal\datastore\DataDictionary\IncompatibleTypeException;

use PDLT\ConverterInterface;

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
   * @param \PDLT\ConverterInterface $date_format_converter
   *   Strptime-to-MySQL date format converter.
   * @param string $datastore_table
   *   Datastore table.
   * @param array $dictionary_fields
   *   Data-dictionary fields.
   */
  public function __construct(
      Connection $connection,
      ConverterInterface $date_format_converter,
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
    $this->dictionaryFields = $this->mergeDatastoreFields($this->dictionaryFields, $this->datastoreTable);

    // Build and execute SQL commands to prepare for table alter.
    $pre_alter_commands = $this->buildPreAlterCommands($this->dictionaryFields, $this->datastoreTable);
    $this->connection->query('SET SESSION innodb_strict_mode=OFF');
    $sql_mode = $this->connection->query('SELECT @@sql_mode')->fetchField();
    $strict_mode = $this->connection->query('SELECT @@innodb_strict_mode')->fetchField();
    \Drupal::logger('datastore')->notice('b ' . $sql_mode . ' ' . $strict_mode);
    array_map(fn ($cmd) => $cmd->execute(), $pre_alter_commands);
    $this->connection->query('SET SESSION innodb_strict_mode=ON');
    // Build and execute SQL command to perform table alter.
    $this->buildAlterCommand($this->dictionaryFields, $this->datastoreTable)->execute();
  }

  /**
   * Remove dictionary fields not found in the given table and copy over names.
   *
   * @param array $dictionary_fields
   *   Data dictionary fields.
   * @param string $table
   *   MySQL table to filter against.
   *
   * @return array
   *   Filtered and updated list of applicable data dictionary fields.
   */
  protected function mergeDatastoreFields(array $dictionary_fields, string $table): array {
    $table_cols = $this->getTableColsAndComments($table);
    $column_names = array_keys($table_cols);

    // Filter out un-applicable dictionary fields.
    $filtered_dictionary_fields = array_filter($dictionary_fields, fn ($fields) => in_array($fields['name'], $column_names, TRUE));
    // Fill missing dictionary field titles.
    foreach ($table_cols as $column_name => $comment) {
      if (isset($filtered_dictionary_fields[$column_name])) {
        $filtered_dictionary_fields[$column_name]['title'] ??= $comment;
      }
    }

    return $filtered_dictionary_fields;
  }

  /**
   * Get list of MySQL table field details.
   *
   * @param string $table
   *   Table name.
   *
   * @return string[]
   *   List of column comments keyed by column names.
   */
  protected function getTableColsAndComments(string $table): array {
    return $this->connection->query("SHOW FULL COLUMNS FROM {{$table}};")->fetchAllKeyed(0, 8);
  }

  /**
   * Get MySQL equivalent of the given Frictionless "Table Schema" type.
   *
   * @param string $frictionless_type
   *   Frictionless "Table Schema" data type.
   * @param string $column
   *   MySQL table column to get type for.
   * @param string $table
   *   MySQL table to get type for.
   *
   * @return string
   *   MySQL data type.
   */
  protected function getType(string $frictionless_type, string $column, string $table): string {
    // Build the MySQL type argument list.
    $args = $this->buildTypeArgs($frictionless_type, $column, $table);

    // Build full MySQL type.
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
   * Build MySQL type arg list for the given Frictionless "Table Schema" type.
   *
   * @param string $type
   *   Frictionless "Table Schema" data type.
   * @param string $column
   *   Column name.
   * @param string $table
   *   Table name.
   *
   * @throws Drupal\datastore\DataDictionary\IncompatibleTypeException
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
        // Temporarily disable strict mode for date conversion.
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
   * @param array $fields
   *   Data dictionary fields.
   * @param string $table
   *   Mysql table name.
   *
   * @return \Drupal\Core\Database\StatementInterface
   *   Prepared MySQL table alter command statement.
   */
  protected function buildAlterCommand(array $fields, string $table): StatementInterface {
    $modify_lines = [];

    foreach ($fields as ['name' => $field, 'type' => $type, 'title' => $title]) {
      // Get MySQL type for column.
      $column_type = $this->getType($type, $field, $table);
      // Build modify line for alter command and add the appropriate arguments
      // to the args list.
      $modify_lines[] = "MODIFY COLUMN {$field} {$column_type} COMMENT '{$title}'";
    }

    return $this->connection->prepareStatement("ALTER TABLE {{$table}} " . implode(', ', $modify_lines) . ';', []);
  }

}
