<?php

namespace Drupal\datastore_mysql_import\Service;

use Dkan\Datastore\Importer;
use Drupal\Core\Database\Database;
use Procrastinator\Result;

use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * Expiremental MySQL LOAD DATA importer.
 *
 * @codeCoverageIgnore
 */
class MysqlImport extends Importer {

  /**
   * The maximum length of a MySQL table column name.
   *
   * @var int
   */
  protected const MAX_COLUMN_LENGTH = 64;

  /**
   * List of reserved words in MySQL 5.6-8 and MariaDB.
   *
   * @var array
   */
  protected const RESERVED_WORDS = ['accessible', 'add', 'all', 'alter', 'analyze',
    'and', 'as', 'asc', 'asensitive', 'before', 'between', 'bigint', 'binary',
    'blob', 'both', 'by', 'call', 'cascade', 'case', 'change', 'char',
    'character', 'check', 'collate', 'column', 'condition', 'constraint',
    'continue', 'convert', 'create', 'cross', 'cube', 'cume_dist',
    'current_date', 'current_role', 'current_time', 'current_timestamp',
    'current_user', 'cursor', 'database', 'databases', 'day_hour',
    'day_microsecond', 'day_minute', 'day_second', 'dec', 'decimal', 'declare',
    'default', 'delayed', 'delete', 'dense_rank', 'desc', 'describe',
    'deterministic', 'distinct', 'distinctrow', 'div', 'do_domain_ids',
    'double', 'drop', 'dual', 'each', 'else', 'elseif', 'empty', 'enclosed',
    'escaped', 'except', 'exists', 'exit', 'explain', 'false', 'fetch',
    'first_value', 'float', 'float4', 'float8', 'for', 'force', 'foreign',
    'from', 'fulltext', 'function', 'general', 'generated', 'get', 'grant',
    'group', 'grouping', 'groups', 'having', 'high_priority', 'hour_microsecond',
    'hour_minute', 'hour_second', 'if', 'ignore', 'ignore_domain_ids',
    'ignore_server_ids', 'in', 'index', 'infile', 'inner', 'inout',
    'insensitive', 'insert', 'int', 'int1', 'int2', 'int3', 'int4', 'int8',
    'integer', 'intersect', 'interval', 'into', 'io_after_gtids',
    'io_before_gtids', 'is', 'iterate', 'join', 'json_table', 'key', 'keys',
    'kill', 'lag', 'last_value', 'lateral', 'lead', 'leading', 'leave', 'left',
    'like', 'limit', 'linear', 'lines', 'load', 'localtime', 'localtimestamp',
    'lock', 'long', 'longblob', 'longtext', 'loop', 'low_priority',
    'master_bind', 'master_heartbeat_period', 'master_ssl_verify_server_cert',
    'match', 'maxvalue', 'mediumblob', 'mediumint', 'mediumtext', 'middleint',
    'minute_microsecond', 'minute_second', 'mod', 'modifies', 'natural', 'not',
    'no_write_to_binlog', 'nth_value', 'ntile', 'null', 'numeric', 'of',
    'offset', 'on', 'optimize', 'optimizer_costs', 'option', 'optionally',
    'or', 'order', 'out', 'outer', 'outfile', 'over', 'page_checksum',
    'parse_vcol_expr', 'partition', 'percent_rank', 'position', 'precision',
    'primary', 'procedure', 'purge', 'range', 'rank', 'read', 'reads',
    'read_write', 'real', 'recursive', 'references', 'ref_system_id', 'regexp',
    'release', 'rename', 'repeat', 'replace', 'require', 'resignal',
    'restrict', 'return', 'returning', 'revoke', 'right', 'rlike', 'row',
    'row_number', 'rows', 'schema', 'schemas', 'second_microsecond', 'select',
    'sensitive', 'separator', 'set', 'show', 'signal', 'slow', 'smallint',
    'spatial', 'specific', 'sql', 'sql_big_result', 'sql_calc_found_rows',
    'sqlexception', 'sql_small_result', 'sqlstate', 'sqlwarning', 'ssl',
    'starting', 'stats_auto_recalc', 'stats_persistent', 'stats_sample_pages',
    'stored', 'straight_join', 'system', 'table', 'terminated', 'then',
    'tinyblob', 'tinyint', 'tinytext', 'to', 'trailing', 'trigger', 'true',
    'undo', 'union', 'unique', 'unlock', 'unsigned', 'update', 'usage', 'use',
    'using', 'utc_date', 'utc_time', 'utc_timestamp', 'values', 'varbinary',
    'varchar', 'varcharacter', 'varying', 'virtual', 'when', 'where', 'while',
    'window', 'with', 'write', 'xor', 'year_month', 'zerofill',
  ];

  /**
   * Override.
   *
   * {@inheritdoc}
   */
  protected function runIt() {
    // Attempt to resolve resource file name from file path.
    $file_path = \Drupal::service('file_system')->realpath($this->resource->getFilePath());
    if ($file_path === FALSE) {
      return $this->setResultError(sprintf('Unable to resolve file name "%s" for resource with identifier "%s" and version "%s".', $file_path, $this->resource->getIdentifier(), $this->resource->getVersion()));
    }

    // Read the header line from the CSV file.
    try {
      $header_line = $this->getFirstLineFromFile($file_path);
    }
    catch (FileException $e) {
      return $this->setResultError($e->getMessage());
    }

    // Extract the columns names using the header line.
    $columns = str_getcsv($header_line);
    // Generate sanitized table headers from column names.
    $headers = $this->generateTableHeaders($columns);

    // Use headers to set the storage schema.
    $this->setStorageSchema($headers);

    // Attempt to detect the line ending for this resource file using the first
    // line from the file.
    $eol = $this->getEol($header_line);
    // On failure, stop the import job and log an error.
    if (!isset($eol)) {
      return $this->setResultError(sprintf('Failed to detect EOL character for resource file "%s" from header line "%s".', $file_path, $header_line));
    }

    // Call `count` on database table in order to ensure a database table has
    // been created for the datastore.
    // @todo Find a better way to ensure creation of datastore tables.
    $this->dataStorage->count();
    // Construct and execute a SQL import statement using the information
    // gathered from the CSV file being imported.
    $this->getDatabaseConnectionCapableOfDataLoad()->query(
      $this->getSqlStatement($file_path, $this->dataStorage->getTableName(), $headers, $eol));

    Database::setActiveConnection();

    $this->getResult()->setStatus(Result::DONE);

    return $this->getResult();
  }

  /**
   * Read the first line from the given file.
   *
   * @param string $file_path
   *   File path.
   *
   * @return string
   *   First line from file.
   *
   * @throws Symfony\Component\HttpFoundation\File\Exception\FileException
   *   On failure to open the file;
   *   on failure to read the first line from the file.
   */
  protected function getFirstLineFromFile(string $file_path): string {
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

    return $header_line;
  }

  /**
   * Attempt to detect the EOL character for the given line.
   *
   * @param string $line
   *   Line being analyzed.
   *
   * @return string|null
   *   The EOL character for the given line, or NULL on failure.
   */
  protected function getEol(string $line): ?string {
    $eol = NULL;

    if (preg_match('/\r\n$/', $line)) {
      $eol = '\r\n';
    }
    elseif (preg_match('/\r$/', $line)) {
      $eol = '\r';
    }
    elseif (preg_match('/\n$/', $line)) {
      $eol = '\n';
    }

    return $eol;
  }

  /**
   * Private.
   */
  private function getDatabaseConnectionCapableOfDataLoad() {
    $options = \Drupal::database()->getConnectionOptions();
    $options['pdo'][\PDO::MYSQL_ATTR_LOCAL_INFILE] = 1;
    Database::addConnectionInfo('extra', 'default', $options);
    Database::setActiveConnection('extra');

    return Database::getConnection();
  }

  /**
   * Properly escape and format the supplied list of column names.
   *
   * @param string|null[] $columns
   *   List of column names.
   *
   * @return array
   *   List of sanitized table headers.
   */
  private function generateTableHeaders(array $columns): array {
    $headers = [];

    foreach ($columns as $column) {
      // Sanitize the supplied table header to generate a unique column name;
      // null-coalesce potentially NULL column names to empty strings.
      $header = $this->sanitizeHeader($column ?? '');

      if (is_numeric($header) || in_array($header, self::RESERVED_WORDS)) {
        // Prepend "_" to column name that are not allowed in MySQL
        // This can be dropped after move to Drupal 9.
        // @see https://github.com/GetDKAN/dkan/issues/3606
        $header = '_' . $header;
      }

      // Truncate the generated table column name, if necessary, to fit the max
      // column length.
      $header = $this->truncateHeader($header);

      // Generate unique numeric suffix for the header if a header already
      // exists with the same name.
      for ($i = 2; isset($headers[$header]); $i++) {
        $suffix = '_' . $i;
        $header = substr($header, 0, self::MAX_COLUMN_LENGTH - strlen($suffix)) . $suffix;
      }

      $headers[$header] = $header;
    }

    return $headers;
  }

  /**
   * Sanitize table column name according to the MySQL supported characters.
   *
   * @param string $column
   *   The column name being sanitized.
   *
   * @returns string
   *   Sanitized column name.
   */
  protected function sanitizeHeader(string $column): string {
    // Replace all spaces with underscores since spaces are not a supported
    // character.
    $column = str_replace(' ', '_', $column);
    // Strip unsupported characters from the header.
    $column = preg_replace('/[^A-Za-z0-9_ ]/', '', $column);
    // Trim underscores from the beginning and end of the column name.
    $column = trim($column, '_');
    // Convert the column name to lowercase.
    $column = strtolower($column);

    return $column;
  }

  /**
   * Truncate column name if longer than the max column length for the database.
   *
   * @param string $column
   *   The column name being truncated.
   *
   * @returns string
   *   Truncated column name.
   */
  protected function truncateHeader(string $column): string {
    // If the supplied table column name is longer than the max column length,
    // truncate the column name to 5 characters under the max length and
    // substitute the truncated characters with a unique hash.
    if (strlen($column) > self::MAX_COLUMN_LENGTH) {
      $field = substr($column, 0, self::MAX_COLUMN_LENGTH - 5);
      $hash = substr(md5($column), 0, 4);
      $column = $field . '_' . $hash;
    }

    return $column;
  }

  /**
   * Construct a SQL file import statement using the given file information.
   *
   * @param string $file_path
   *   File path to the CSV file being imported.
   * @param string $tablename
   *   Name of the datastore table the file is being imported into.
   * @param string[] $headers
   *   List of CSV headers.
   * @param string $eol
   *   End Of Line character for file importation.
   *
   * @return string
   *   Generated SQL file import statement.
   */
  private function getSqlStatement(string $file_path, string $tablename, array $headers, string $eol): string {
    return implode(' ', [
      'LOAD DATA LOCAL INFILE \'' . $file_path . '\'',
      'INTO TABLE ' . $tablename,
      'FIELDS TERMINATED BY \',\'',
      'OPTIONALLY ENCLOSED BY \'"\'',
      'ESCAPED BY \'\'',
      'LINES TERMINATED BY \'' . $eol . '\'',
      'IGNORE 1 ROWS',
      '(' . implode(',', $headers) . ')',
      'SET record_number = NULL;',
    ]);
  }

}
