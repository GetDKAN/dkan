<?php

namespace Drupal\datastore\DataDictionary\AlterTableQuery;

use Drupal\Core\Database\StatementInterface;

use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\datastore\DataDictionary\AlterTableQueryBase;
use Drupal\datastore\DataDictionary\AlterTableQueryInterface;
use Drupal\datastore\DataDictionary\IncompatibleTypeException;

/**
 * MySQL table alter query.
 */
class MySQLQuery extends AlterTableQueryBase implements AlterTableQueryInterface {

  /**
   * Date time specific types.
   *
   * @var string[]
   */
  protected const DATE_TIME_TYPES = [
    'DATE',
    'TIME',
    'DATETIME',
  ];

  /**
   * Default type to use for fields if no data-dictionary type is specified.
   *
   * @var string
   */
  protected const DEFAULT_FIELD_TYPE = 'TEXT';

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
   * Default index field length.
   *
   * @var int
   */
  protected const DEFAULT_INDEX_FIELD_LENGTH = 50;

  /**
   * MySQL string field types.
   *
   * @var string[]
   */
  protected const STRING_FIELD_TYPES = [
    'CHAR',
    'VARCHAR',
    'TEXT',
    'TINYTEXT',
    'BINARY',
    'VARBINARY',
    'BLOB',
  ];

  /**
   * Mapping between frictionless types and SQL types.
   *
   * Frictionless type is key, SQL is value.
   *
   * @var string[]
   */
  protected static $frictionlessTypes = [
    'string' => 'TEXT',
    'number' => 'DECIMAL',
    'integer' => 'INT',
    'date' => 'DATE',
    'time' => 'TIME',
    'datetime' => 'DATETIME',
    'year' => 'YEAR',
    'yearmonth' => 'TINYTEXT',
    'boolean' => 'BOOL',
    'object' => 'TEXT',
    'geopoint' => 'TEXT',
    'geojson' => 'TEXT',
    'array' => 'TEXT',
    'duration' => 'TINYTEXT',
  ];

  /**
   * {@inheritdoc}
   */
  public function doExecute(): void {
    // Sanitize field names to match database field names.
    $this->fields = $this->sanitizeFields($this->fields);
    // Filter out fields which are not present in the database table.
    $this->fields = $this->mergeFields($this->fields, $this->table);

    // Sanitize index field names to match database field names.
    $this->indexes = $this->sanitizeIndexes($this->indexes);
    // Filter out indexes with fields which are not present in the table.
    $this->indexes = $this->mergeIndexes($this->indexes, $this->table);

    // Build and execute SQL commands to prepare for table alter.
    $pre_alter_commands = $this->buildPreAlterCommands($this->fields, $this->table);
    array_map(fn ($cmd) => $cmd->execute(), $pre_alter_commands);
    // Build SQL command to perform table alter.
    $command = $this->buildAlterCommand($this->table, $this->fields, $this->indexes);
    // Execute alter command.
    $command->execute();
  }

  /**
   * Sanitize field names.
   *
   * @param array $fields
   *   Query fields.
   *
   * @return array
   *   Query fields list with sanitized field names.
   */
  protected function sanitizeFields(array $fields): array {
    // Iterate through field list...
    foreach (array_keys($fields) as $key) {
      // Create reference to index field name.
      $field_name = &$fields[$key]['name'];
      // Sanitize field name.
      $field_name = ImportJob::sanitizeHeader($field_name);
    }

    return $fields;
  }

  /**
   * Sanitize index field names.
   *
   * @param array $indexes
   *   Query indexes.
   *
   * @return array
   *   Query indexes list with sanitized index field names.
   */
  protected function sanitizeIndexes(array $indexes): array {
    // Iterate through index list...
    foreach (array_keys($indexes) as $index_key) {
      // Create reference to index field list.
      $index_fields = &$indexes[$index_key]['fields'];

      // Iterate through index field list...
      foreach (array_keys($index_fields) as $field_key) {
        // Create reference to index field name.
        $field_name = &$index_fields[$field_key]['name'];
        // Sanitize field name.
        $field_name = ImportJob::sanitizeHeader($field_name);
      }
    }

    return $indexes;
  }

  /**
   * Remove query fields not found in the given table and copy over names.
   *
   * @param array $fields
   *   Query fields.
   * @param string $table
   *   MySQL table to filter against.
   *
   * @return array
   *   Filtered and updated list of applicable query fields.
   */
  protected function mergeFields(array $fields, string $table): array {
    $table_cols = $this->getTableColsAndComments($table);
    $column_names = array_keys($table_cols);

    // Filter out un-applicable query fields.
    $filtered_fields = array_filter($fields, fn ($fields) => in_array($fields['name'], $column_names, TRUE));
    // Fill missing field titles.
    foreach ($table_cols as $column_name => $comment) {
      if (isset($filtered_fields[$column_name])) {
        $filtered_fields[$column_name]['title'] = $filtered_fields[$column_name]['title'] ?: $comment;
      }
    }

    return $filtered_fields;
  }

  /**
   * Remove query indexes with fields not found in the given table and copy over names.
   *
   * @param array $indexes
   *   Query indexes.
   * @param string $table
   *   MySQL table to filter against.
   *
   * @return array
   *   Filtered list of applicable query indexes.
   */
  protected function mergeIndexes(array $indexes, string $table): array {
    $table_cols = $this->getTableColsAndComments($table);
    $column_names = array_keys($table_cols);

    // Filter out un-applicable query indexes.
    $indexes = array_filter($indexes, function ($index) use ($column_names) {
      $fields = array_column($index['fields'], 'name');
      return empty(array_diff($fields, $column_names));
    });

    return $indexes;
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
   * Build full MySQL equivalent of the given Frictionless "Table Schema" type.
   *
   * @param string $frictionless_type
   *   Frictionless "Table Schema" data type.
   * @param string $column
   *   MySQL table column to get type for.
   * @param string $table
   *   MySQL table to get type for.
   *
   * @return string
   *   Full MySQL data type.
   */
  protected function getFieldType(string $frictionless_type, string $column, string $table): string {
    // Determine MySQL base type.
    $base_mysql_type = $this->getBaseType($frictionless_type);
    // Build the MySQL type argument list.
    $args = $this->buildTypeArgs($base_mysql_type, $column, $table);
    $args_str = !empty($args) ? '(' . implode(', ', $args) . ')' : '';

    // Build full MySQL type.
    return $base_mysql_type . $args_str;
  }

  /**
   * Get base MySQL equivalent of the given Frictionless "Table Schema" type.
   *
   * @param string $frictionless_type
   *   Frictionless "Table Schema" data type.
   *
   * @return string
   *   Base MySQL data type.
   */
  protected function getBaseType(string $frictionless_type): string {
    if ($sql_type = static::$frictionlessTypes[$frictionless_type] ?? FALSE) {
      return $sql_type;
    }
    throw new \InvalidArgumentException($frictionless_type . ' is not a valid frictionless type.');
  }

  /**
   * Build MySQL type arg list for MySQL type.
   *
   * @param string $type
   *   MySQL data type.
   * @param string $column
   *   Column name.
   * @param string $table
   *   Table name.
   *
   * @return array
   *   MySQL type arguments.
   *
   * @throws Drupal\datastore\DataDictionary\IncompatibleTypeException
   *   When incompatible data is found in the table for the specified type.
   */
  protected function buildTypeArgs(string $type, string $column, string $table): array {
    // If this field is a DECIMAL field, build decimal and size arguments.
    if ($type === 'DECIMAL') {
      $non_decimals = $this->connection->query("SELECT MAX(LENGTH(TRIM(LEADING '-' FROM SUBSTRING_INDEX({$column}, '.', 1)))) FROM {{$table}};")->fetchField();
      $decimal = $this->connection->query("SELECT MAX(LENGTH(SUBSTRING_INDEX({$column}, '.', -1))) FROM {{$table}};")->fetchField();
      $size = $non_decimals + $decimal;
      if ($size > self::DECIMAL_MAX_SIZE || $decimal > self::DECIMAL_MAX_DECIMAL) {
        throw new IncompatibleTypeException("Decimal values found in column too large for DECIMAL type; please use type 'string' for column '{$column}'");
      }
      return [$size, $decimal];
    }

    return [];
  }

  /**
   * Build list of commands to prepare table for alter command.
   *
   * @param array $query_fields
   *   Query fields.
   * @param string $table
   *   Mysql table name.
   *
   * @return \Drupal\Core\Database\StatementInterface[]
   *   Prep command statements.
   */
  protected function buildPreAlterCommands(array $query_fields, string $table): array {
    $pre_alter_cmds = [];

    // Build pre-alter commands for each query field.
    foreach ($query_fields as ['name' => $col, 'type' => $type, 'format' => $format]) {
      // Determine base MySQL type for Frictionless column type.
      $base_type = $this->getBaseType($type);

      // Replace empty strings with NULL for non-text columns to prevent
      // misc. errors (i.e. STR_TO_DATE function related and "Incorrect
      // `type` value" errors).
      if (!in_array($base_type, self::STRING_FIELD_TYPES, TRUE)) {
        $pre_alter_cmds[] = $this->connection->update($table)->condition($col, '')->expression($col, 'NULL');
      }

      // Build pre-alter commands for date fields.
      if (in_array($base_type, self::DATE_TIME_TYPES, TRUE)) {
        $pre_alter_cmds = array_merge($pre_alter_cmds, $this->buildDatePreAlterCommands($table, $col, $format));
      }

      // Build pre-alter commands for boolean fields.
      if ($base_type === 'BOOL') {
        $pre_alter_cmds = array_merge($pre_alter_cmds, $this->buildBoolPreAlterCommands($table, $col));
      }
    }

    return $pre_alter_cmds;
  }

  /**
   * Build pre-alter commands for date fields.
   *
   * Update format of the date fields to ISO-8601 before importing into MySQL.
   *
   * @param string $table
   *   Table name.
   * @param string $column
   *   Table column.
   * @param string $format
   *   Field frictionless date format.
   *
   * @return \Drupal\Core\Database\Query\Update[]
   *   Pre-alter update DB queries.
   */
  protected function buildDatePreAlterCommands(string $table, string $column, string $format): array {
    $pre_alter_cmds = [];

    // If a valid format is provided...
    if (!empty($format) && $format !== 'default') {
      $mysql_date_format = $this->dateFormatConverter->convert($format);
      // Convert date formats for date column.
      $pre_alter_cmds[] = $this->connection->update($table)->expression($column, "STR_TO_DATE({$column}, :date_format)", [
        ':date_format' => $mysql_date_format,
      ]);
    }

    return $pre_alter_cmds;
  }

  /**
   * Build pre-alter commands for boolean fields.
   *
   * Convert strings 'true' and 'false' to '1' and '0' for boolean fields.
   *
   * @param string $table
   *   Table name.
   * @param string $column
   *   Table column.
   *
   * @return \Drupal\Core\Database\Query\Update[]
   *   Pre-alter update DB queries.
   */
  protected function buildBoolPreAlterCommands(string $table, string $column): array {
    return [
      $this->connection->update($table)->where("UPPER({$column}) = :value", [':value' => 'FALSE'])->expression($column, '0'),
      $this->connection->update($table)->where("UPPER({$column}) = :value", [':value' => 'TRUE'])->expression($column, '1'),
    ];
  }

  /**
   * Build alter command to modify table column data types.
   *
   * @param string $table
   *   MySQL table name.
   * @param array $fields
   *   Query fields.
   * @param array $indexes
   *   Query indexes.
   *
   * @return \Drupal\Core\Database\StatementInterface
   *   Prepared MySQL table alter command statement.
   */
  protected function buildAlterCommand(string $table, array $fields, array $indexes): StatementInterface {
    $mysql_type_map = $this->buildDatabaseTypeMap($fields, $table);
    // Build alter options.
    $alter_options = array_merge(
      $this->buildModifyColumnOptions($fields, $mysql_type_map),
      $this->buildAddIndexOptions($indexes, $table, $mysql_type_map)
    );

    return $this->connection->prepareStatement("ALTER TABLE {{$table}} " . implode(', ', $alter_options) . ';', []);
  }

  /**
   * Build MySQL type map from Frictionless field definitions.
   *
   * @param array $fields
   *   Frictionless field definitions.
   * @param string $table
   *   Table name.
   *
   * @return string[]
   *   Column name -> MySQL type map.
   */
  protected function buildDatabaseTypeMap(array $fields, string $table): array {
    $type_map = [];

    foreach ($fields as ['name' => $field, 'type' => $type]) {
      // Get MySQL type for column.
      $type_map[$field] = $this->getFieldType($type, $field, $table);
    }

    return $type_map;
  }

  /**
   * Build alter command modify column options.
   *
   * @param array $fields
   *   Query fields.
   * @param string[] $type_map
   *   Field -> MySQL type map.
   *
   * @return string[]
   *   Modify column options.
   */
  protected function buildModifyColumnOptions(array $fields, array $type_map): array {
    $modify_column_options = [];

    foreach ($fields as ['name' => $field, 'title' => $title]) {
      $column_type = $type_map[$field];
      // Escape characters in column title in preparation for it being used as
      // a MySQL comment.
      $comment = addslashes($title);
      // Build modify line for alter command and add the appropriate arguments
      // to the args list.
      $modify_column_options[] = "MODIFY COLUMN {$field} {$column_type} COMMENT '{$comment}'";
    }

    return $modify_column_options;
  }

  /**
   * Build alter command add index options.
   *
   * @param array $indexes
   *   Query indexes.
   * @param string $table
   *   Table name.
   * @param string[] $type_map
   *   Field -> MySQL type map.
   *
   * @return string[]
   *   Add index options.
   */
  protected function buildAddIndexOptions(array $indexes, string $table, array $type_map): array {
    $add_index_options = [];

    foreach ($indexes as ['name' => $name, 'type' => $index_type, 'fields' => $fields, 'description' => $description]) {
      // Translate Frictionless index type to MySQL.
      $mysql_index_type = $this->getIndexType($index_type);

      // Build field options.
      $field_options = array_map(function ($field) use ($table, $type_map) {
        $name = $field['name'];
        $length = $field['length'];
        $type = $type_map[$name] ?? self::DEFAULT_FIELD_TYPE;
        return $this->buildIndexFieldOption($name, $length, $table, $type);
      }, $fields);
      $formatted_field_options = implode(', ', $field_options);

      // Escape characters in index description in preparation for it being
      // used as a MySQL comment.
      $comment = addslashes($description);

      // Build add index option list.
      $add_index_options[] = "ADD {$mysql_index_type} INDEX {$name} ({$formatted_field_options}) COMMENT '{$comment}'";
    }

    return $add_index_options;
  }

  /**
   * Convert Frictionless to MySQL index types.
   *
   * @param string $frictionless_type
   *   Frictionless index type.
   *
   * @return string
   *   MySQL index type.
   */
  protected function getIndexType(string $frictionless_type): string {
    return ([
      'index'    => '',
      'fulltext' => 'FULLTEXT',
    ])[$frictionless_type];
  }

  /**
   * Build formatted index field option.
   *
   * @param string $name
   *   Index field name.
   * @param int|null $length
   *   Index field length.
   * @param string $table
   *   Table name.
   * @param string $type
   *   MySQL column type.
   *
   * @return string
   *   Formatted index field option string.
   */
  protected function buildIndexFieldOption(string $name, ?int $length, string $table, string $type): string {
    // Extract base type from full MySQL type ("DECIMAL(12, 3)" -> "DECIMAL").
    $base_type = strtok($type, '(');
    // If this field is a string type, determine what it's length should be...
    if (in_array($base_type, self::STRING_FIELD_TYPES)) {
      // Initialize length to the default index field length if not set.
      $length ??= self::DEFAULT_INDEX_FIELD_LENGTH;
      // Retrieve the max length for this table column.
      $max_length = $this->getMaxColumnLength($name, $table);
      // If the specified length is greater than the max length, defer to the
      // max length.
      $length = ($length > $max_length) ? $max_length : $length;
      // Format the length.
      $formatted_length = ' (' . strval($length) . ')';
    }
    // Otherwise, don't specify a length.
    else {
      $formatted_length = '';
    }

    return $name . $formatted_length;
  }

  /**
   * Get the length of the largest value in the specified table column.
   *
   * @param string $column
   *   Table column name.
   * @param string $table
   *   Table name.
   *
   * @return int
   *   Max table column length.
   */
  protected function getMaxColumnLength(string $column, string $table): int {
    $max_length = $this->connection->query("SELECT MAX(LENGTH({$column})) FROM {{$table}};")->fetchField();
    return intval($max_length);
  }

}
