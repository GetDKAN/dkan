<?php

namespace Drupal\common;

use Symfony\Component\HttpFoundation\File\Exception\FileException;

trait ResourceSchemaDetectionTrait {
  /**
   * Resource file columns.
   *
   * @var string[]
   */
  protected $columns;

  /**
   * First line from resource file.
   *
   * @var string
   */
  protected $headerLine;

  /**
   * Resource table schema.
   *
   * @var array[]
   */
  protected $schema;

  /**
   * Read the first line from the given file.
   *
   * @param string $file_path
   *   File path.
   *
   * @return string
   *   First line from file.
   *
   * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
   *   On failure to open the file;
   *   on failure to read the first line from the file.
   */
  public function getFirstLineFromFile(): string {
    // If the header line has already been determined (and stored), return it.
    if (isset($this->headerLine)) {
      return $this->headerLine;
    }

    // Attempt to resolve resource file name from file path.
    $file_path = \Drupal::service('file_system')->realpath($this->resource->getFilePath());
    if ($file_path === FALSE) {
      throw new FileException(sprintf('Unable to resolve file path for resource with identifier "%s".', $this->resource->getUniqueIdentifier()));
    }

    // Ensure the "auto_detect_line_endings" ini setting is enabled before
    // openning the file to ensure Mac style EOL characters are detected.
    $old_ini = ini_set('auto_detect_line_endings', '1');
    // Read the first (header) line from the CSV file.
    $f = fopen($file_path, 'r');
    // Revert ini setting once the file has been opened.
    if ($old_ini !== FALSE) {
      ini_set('auto_detect_line_endings', $old_ini);
    }
    // Ensure the file could be successfully opened.
    if (!isset($f) || $f === FALSE) {
      throw new FileException(sprintf('Failed to open resource file "%s".', $file_path));
    }
    // Attempt to retrieve the first line from the resource file.
    $header_line = fgets($f);
    // Close the resource file since it is no longer necessary.
    fclose($f);
    // Ensure the first line of the resource file was successfully read.
    if (!isset($header_line) || $header_line === FALSE) {
      throw new FileException(sprintf('Failed to read header line from resource file "%s".', $file_path));
    }

    // Cache the header line for later reference.
    $this->headerLine = $header_line;

    return $header_line;
  }

  /**
   * Accessor for schema property.
   *
   * @return array[]
   *  Schema property value.
   */
  public function getSchema(): array {
    if (!isset($this->schema)) {
      $frictionless_schema = $this->extractFrictionlessSchemaFromMetadata() ?? $this->buildFrictionlessSchemaFromFile();
      $this->schema = self::convertFrictionlessToSqlSchema($frictionless_schema);
    }

    return $this->schema;
  }

  /**
   * Clean up and set the schema for SQL storage.
   *
   * @param array $column
   *   Column row from a CSV or other tabular data source.
   *
   * @param int $limit
   *   Maximum length of a column column in the target database. Defaults to
   *   64, the max length in MySQL.
   */
  protected static function convertFrictionlessToSqlSchema(array $frictionless_schema): array {
    $sql_schema = ['fields' => []];

    foreach ($frictionless_schema as $info) {
      $column = $info['name'] ?? $info['title'] ?? $info['description'] ?? '';
      $sql_schema['fields'][self::generateColumnIdentifier($sql_schema, $column)] = [
        'description' => $info['title'] ?? $info['description'],
        'type' => $info['type'],
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
    if (strlen($column) > self::getMaxColumnLength()) {
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


  /**
   * Extract schema from the distribution metadata for the supplied resource.
   *
   * @param \Drupal\common\Resource $header_line
   *   CSV header line.
   *
   * @return array|null
   *   Frictionless Schema or `null` on failure.
   */
  protected function extractFrictionlessSchemaFromMetadata(): ?array {
    $schema = NULL;

    // Use the "unique" ID from the supplied resource to determine it's
    // distribution.
    $distribution_ids = \Drupal::service('dkan.metastore.reference_lookup')->getReferencers('distribution', $this->getUniqueIdentifier(), 'downloadURL');
    // If were able to successfully determine the distribution for this
    // resource, attempt to retrieve the fields (resource headers) from the
    // distribution's metadata.
    if (!empty($distribution_ids) && $distribution_id = reset($distribution_ids)) {
      $distribution_json = \Drupal::service('dkan.metastore.storage')->getInstance('distribution')->retrieve($distribution_id);
      $distribution_metadata = json_decode($distribution_json);
      $schema = $distribution_metadata->data->fields ?? NULL;
    }

    return $schema;
  }

  /**
   * Determine the column identifiers for this resource.
   *
   * @return string[]
   *   Resource column identifiers.
   */
  public function getColumns(): array {
    if (isset($this->columns)) {
      return $this->columns;
    }

    // Attempt to retrieve the field details from this resource distribution's
    // metadata.
    // If we were unable to find the resource's field details in it's
    // distribution, extract the columns names using the header line, and
    // default to the text data type for all fields.
    $frictionless_schema = $this->extractFrictionlessSchemaFromMetadata();
    if (!empty($frictionless_schema)) {
      $columns = array_map(function ($info) {
        return $info['name'] ?? $info['title'] ?? $info['description'] ?? '';
      }, $frictionless_schema);
    }
    else {
      $columns = $this->getFileHeaders();
    }

    $this->columns = array_reduce($columns, 'self::generateColumnIdentifier');
    return $this->columns;
  }

  /**
   * Build frictionless schema with only text data types for this resource.
   *
   * @return array[]
   *   Frictionless schema.
   */
  protected function buildFrictionlessSchemaFromFile(): array {
    return array_map(function (string $header) {
      return [
        'title' => $header,
        'type' => 'text',
      ];
    }, $this->getFileHeaders());
  }

  /**
   * Attempt to determine table headers for the given resource file.
   *
   * @return string[]
   *   Resource file headers.
   *
   * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
   *   When called on a resource with a unsupported mime-type.
   */
  protected function getFileHeaders(): array {
    $mime_type = $this->getMimeType();

    if ($mime_type === 'text/csv') {
      $headers = str_getcsv($this->getFirstLineFromFile());
    } elseif ($mime_type === 'text/tab-separated-values') {
      $headers = str_getcsv($this->getFirstLineFromFile(), "\t");
    }
    else {
      throw new FileException(sprintf('Unable to determine resource file headers for resource with mime-type of "%s".', $this->getMimeType()));
    }

    return $headers;
  }
}
