<?php

namespace Drupal\datastore_mysql_import\Service;

use Dkan\Datastore\Importer;
use Drupal\Core\Database\Database;
use Procrastinator\Result;

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
    $fileSystem = \Drupal::service('file_system');
    $filename = $fileSystem->realpath($this->resource->getFilePath());

    // Read the first (header) line from the CSV file.
    $f = fopen($filename, 'r');
    $header_line = fgets($f);
    fclose($f);
    // Extract the columns names using the header line.
    $columns = str_getcsv($header_line);
    // Generate sanitized table headers from column names.
    $headers = $this->generateTableHeaders($columns);
    // Set the storage schema using the list of table headers.
    $this->setStorageSchema($headers);

    // Instance of Drupal\datastore\Storage\DatabaseTable.
    $storage = $this->dataStorage;
    $storage->count();

    $sqlStatementLines = $this->getSqlStatement($filename, $storage, $headers);

    $sqlStatement = implode(' ', $sqlStatementLines);

    $db = $this->getDatabaseConnectionCapableOfDataLoad();
    $db->query($sqlStatement);

    Database::setActiveConnection();

    $this->getResult()->setStatus(Result::DONE);

    return $this->getResult();
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
   * @param string[] $columns
   *   List of column names.
   *
   * @return array
   *   List of sanitized table headers keyed by original column names.
   */
  private function generateTableHeaders(array $columns): array {
    return array_replace([], ...array_map(function ($column) {
      // Sanitize the supplied table header to generate a unique column name.
      $header = $this->sanitizeHeader($column);

      if (is_numeric($header) || in_array($header, self::RESERVED_WORDS)) {
        // Prepend "_" to column name that are not allowed in MySQL
        // This can be dropped after move to Drupal 9.
        // @see https://github.com/GetDKAN/dkan/issues/3606
        $header = '_' . $header;
      }

      // Truncate the generated table column name, if necessary, to fit the max
      // column length.
      $header = $this->truncateHeader($header);

      return [$column => $header];
    }, $columns));
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
   * Private.
   */
  private function getSqlStatement($filename, $storage, $header) {
    return [
      'LOAD DATA LOCAL INFILE \'' . $filename . '\'',
      'INTO TABLE ' . $storage->getTableName(),
      'FIELDS TERMINATED BY \',\'',
      'ENCLOSED BY \'\"\'',
      'LINES TERMINATED BY \'\n\'',
      'IGNORE 1 ROWS',
      '(' . implode(',', $header) . ')',
      'SET record_number = NULL;',
    ];
  }

}
