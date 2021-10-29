<?php

namespace Drupal\datastore;

/**
 * Frictionless Schema to SQL Schema translator.
 */
class FrictionlessSchemaTranslator implements SchemaTranslatorInterface {

  /**
   * Database driver name.
   *
   * @var string|null
   */
  protected $driver;

  /**
   * Frictionless to database type map.
   *
   * @var string|null
   */
  protected $type_map = [
    'string' => 'text',
    'number' => 'float',
    'integer' => 'int',
  ];

  /**
   * Creates a FrictionlessSchemaTranslator object.
   *
   * @param string|null $driver
   *   Database driver name, or null to not specify one in generated schemas.
   * @param string|null $type_map
   *   Frictionless to database type map, or null to use the default.
   */
  public function __construct(?string $driver = NULL, ?array $type_map = NULL) {
    if (isset($driver)) {
      $this->driver = $driver;
    }
    if (isset($type_map)) {
      $this->typeMap = $type_map;
    }
  }

  /**
   * Change the database driver name.
   *
   * @param string $driver
   *   Database driver name.
   */
  public function setDriver(string $driver): void {
    $this->driver = $driver;
  }

  /**
   * Change the database type map.
   *
   * @param string[] $type_map
   *   New database type map.
   */
  public function setTypeMap(array $type_map): void {
    $this->typeMap = $type_map;
  }

  /**
   * Translate the given frictionless data type using the type map.
   *
   * @param string $type
   *   Frictionless data type.
   *
   * @return string
   *   Corresponding data type according to the type map.
   */
  protected function translateType(string $type): string {
    return $this->typeMap[$type] ?? 'text';
  }

  /**
   * {@inheritdoc}
   */
  public function translate(array $frictionless_schema): array {
    $sql_schema = ['fields' => []];
    $type_key = isset($this->driver) ? ($this->driver . '_type') : 'type';

    foreach ($frictionless_schema['fields'] as $info) {
      $column = $info['name'] ?? $info['title'] ?? $info['description'] ?? '';
      $sql_schema['fields'][self::generateColumnIdentifier($sql_schema, $column)] = [
        'description' => $info['title'] ?? $info['description'],
        $type_key => $this->translateType($info['type']),
      ];
    }

    return $sql_schema;
  }

  /**
   * Generate a column identifier with the given table header.
   *
   * @param array[] $schema
   *   Table schema (used to prevent column identifier collisions).
   * @param string $column
   *   Header being processed.
   *
   * @return string
   *   Generated column identifier.
   */
  protected static function generateColumnIdentifier(array $schema, string $column): string {
    // Sanitize the supplied table column to generate a unique column name;
    // null-coalesce potentially NULL column names to empty strings.
    $column = self::sanitizeColumn($column ?? '');

    if (is_numeric($column) || in_array($column, self::RESERVED_WORDS)) {
      // Prepend "_" to column name that are not allowed in MySQL.
      // This can be dropped after move to Drupal 9.
      // @see https://github.com/GetDKAN/dkan/issues/3606
      $column = '_' . $column;
    }

    // Truncate the generated table column name, if necessary, to fit the
    // max column length.
    $column = self::truncateColumn($column);

    // Generate unique numeric suffix for the column if a column already
    // exists with the same name.
    for ($i = 2; isset($schema[$column]); $i++) {
      $suffix = '_' . $i;
      $column = substr($column, 0, self::MAX_COLUMN_LENGTH - strlen($suffix)) . $suffix;
    }

    return $column;
  }

  /**
   * Sanitize the given column identifier.
   *
   * Ensure that the column id does not contain characters unsupported by MySQL.
   *
   * @param string $column
   *   The column identifier being sanitized.
   *
   * @return string
   *   Sanitized column identifier.
   */
  protected static function sanitizeColumn(string $column): string {
    // Replace all spaces with underscores since spaces are not a supported
    // character.
    $column = str_replace(' ', '_', $column);
    // Strip unsupported characters from the column.
    $column = preg_replace('/[^A-Za-z0-9_ ]/', '', $column);
    // Trim underscores from the beginning and end of the column name.
    $column = trim($column, '_');
    // Convert the column name to lowercase.
    $column = strtolower($column);

    return $column;
  }

  /**
   * Truncate column name if longer than the max length for the database.
   *
   * @param string $column
   *   The column name being truncated.
   *
   * @return string
   *   Truncated column name.
   */
  protected static function truncateColumn(string $column): string {
    // If the supplied table column name is longer than the max column length,
    // truncate the column name to 5 characters under the max length and
    // substitute the truncated characters with a unique hash.
    if (strlen($column) > self::MAX_COLUMN_LENGTH) {
      $field = substr($column, 0, self::MAX_COLUMN_LENGTH - 5);
      $hash = self::generateToken($column);
      $column = $field . '_' . $hash;
    }

    return $column;
  }

  /**
   * Generate unique 4 character token based on supplied seed.
   *
   * @param string $seed
   *   Seed to use for string generation.
   *
   * @return string
   *   Unique 4 character token.
   */
  protected static function generateToken(string $seed): string {
    return substr(md5($seed), 0, 4);
  }

}
