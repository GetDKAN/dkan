<?php

namespace Drupal\datastore\Plugin\QueueWorker;

use Contracts\ParserInterface;
use Drupal\common\Storage\DatabaseTableInterface;
use Procrastinator\Job\AbstractPersistentJob;
use Procrastinator\Result;
use ForceUTF8\Encoding;

/**
 * Procrastinator job for importing to the datastore.
 */
class ImportJob extends AbstractPersistentJob {

  /**
   * The maximum length of a MySQL table column name.
   *
   * @var int
   */
  protected const MAX_COLUMN_LENGTH = 64;

  /**
   * List of reserved words in MySQL 5.6-8 and MariaDB.
   *
   * @var string[]
   */
  protected const RESERVED_WORDS = [
    'accessible', 'add', 'all', 'alter', 'analyze', 'and', 'as', 'asc',
    'asensitive', 'before', 'between', 'bigint', 'binary', 'blob', 'both', 'by',
    'call', 'cascade', 'case', 'change', 'char', 'character', 'check',
    'collate', 'column', 'condition', 'constraint', 'continue', 'convert',
    'create', 'cross', 'cube', 'cume_dist', 'current_date', 'current_role',
    'current_time', 'current_timestamp', 'current_user', 'cursor', 'database',
    'databases', 'day_hour', 'day_microsecond', 'day_minute', 'day_second',
    'dec', 'decimal', 'declare', 'default', 'delayed', 'delete', 'dense_rank',
    'desc', 'describe', 'deterministic', 'distinct', 'distinctrow', 'div',
    'do_domain_ids', 'double', 'drop', 'dual', 'each', 'else', 'elseif',
    'empty', 'enclosed', 'escaped', 'except', 'exists', 'exit', 'explain',
    'false', 'fetch', 'first_value', 'float', 'float4', 'float8', 'for',
    'force', 'foreign', 'from', 'fulltext', 'function', 'general', 'generated',
    'get', 'grant', 'group', 'grouping', 'groups', 'having', 'high_priority',
    'hour_microsecond', 'hour_minute', 'hour_second', 'if', 'ignore',
    'ignore_domain_ids', 'ignore_server_ids', 'in', 'index', 'infile', 'inner',
    'inout', 'insensitive', 'insert', 'int', 'int1', 'int2', 'int3', 'int4',
    'int8', 'integer', 'intersect', 'interval', 'into', 'io_after_gtids',
    'io_before_gtids', 'is', 'iterate', 'join', 'json_table', 'key', 'keys',
    'kill', 'lag', 'last_value', 'lateral', 'lead', 'leading', 'leave', 'left',
    'like', 'limit', 'linear', 'lines', 'load', 'localtime', 'localtimestamp',
    'lock', 'long', 'longblob', 'longtext', 'loop', 'low_priority',
    'master_bind', 'master_heartbeat_period', 'master_ssl_verify_server_cert',
    'match', 'maxvalue', 'mediumblob', 'mediumint', 'mediumtext', 'middleint',
    'minute_microsecond', 'minute_second', 'mod', 'modifies', 'natural', 'not',
    'no_write_to_binlog', 'nth_value', 'ntile', 'null', 'numeric', 'of',
    'offset', 'on', 'optimize', 'optimizer_costs', 'option', 'optionally', 'or',
    'order', 'out', 'outer', 'outfile', 'over', 'page_checksum',
    'parse_vcol_expr', 'partition', 'percent_rank', 'position', 'precision',
    'primary', 'procedure', 'purge', 'range', 'rank', 'read', 'reads',
    'read_write', 'real', 'recursive', 'references', 'ref_system_id', 'regexp',
    'release', 'rename', 'repeat', 'replace', 'require', 'resignal', 'restrict',
    'return', 'returning', 'revoke', 'right', 'rlike', 'row', 'row_number',
    'rows', 'schema', 'schemas', 'second_microsecond', 'select', 'sensitive',
    'separator', 'set', 'show', 'signal', 'slow', 'smallint', 'spatial',
    'specific', 'sql', 'sql_big_result', 'sql_calc_found_rows', 'sqlexception',
    'sql_small_result', 'sqlstate', 'sqlwarning', 'ssl', 'starting',
    'stats_auto_recalc', 'stats_persistent', 'stats_sample_pages', 'stored',
    'straight_join', 'system', 'table', 'terminated', 'then', 'tinyblob',
    'tinyint', 'tinytext', 'to', 'trailing', 'trigger', 'true', 'undo', 'union',
    'unique', 'unlock', 'unsigned', 'update', 'usage', 'use', 'using',
    'utc_date', 'utc_time', 'utc_timestamp', 'values', 'varbinary', 'varchar',
    'varcharacter', 'varying', 'virtual', 'when', 'where', 'while', 'window',
    'with', 'write', 'xor', 'year_month', 'zerofill',
  ];

  /**
   * Storage class.
   *
   * @var \Drupal\common\Storage\DatabaseTableInterface
   */
  protected $dataStorage;

  /**
   * Parser object.
   *
   * @var \Contracts\ParserInterface
   */
  protected $parser;

  /**
   * Datastore resource.
   *
   * @var \Drupal\datastore\DatastoreResource
   */
  protected $resource;

  public const BYTES_PER_CHUNK = 8192;

  /**
   * Constructor method.
   *
   * @param string $identifier
   *   Job identifier.
   * @param mixed $storage
   *   Storage class.
   * @param array|null $config
   *   Configuration options.
   */
  protected function __construct(string $identifier, $storage, array $config = NULL) {
    parent::__construct($identifier, $storage, $config);

    $this->dataStorage = $config['storage'];

    if (!($this->dataStorage instanceof DatabaseTableInterface)) {
      throw new \Exception('Storage must be an instance of ' . DatabaseTableInterface::class);
    }

    $this->parser = $config['parser'];
    $this->resource = $config['resource'];
  }

  /**
   * Transform possible multiline string to single line for description.
   *
   * @param string $column
   *   Column name.
   *
   * @return string
   *   Column name on single line.
   */
  public static function sanitizeDescription(string $column) {
    $trimmed = array_filter(array_map('trim', explode("\n", $column)));
    return implode(" ", $trimmed);
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
  public static function sanitizeHeader(string $column): string {
    // Replace all spaces and newline characters with underscores since they are
    // not supported.
    $column = preg_replace('/(?: |\r\n|\r|\n)/', '_', $column);
    // Strip unsupported characters from the header.
    $column = preg_replace('/[^A-Za-z0-9_]/', '', $column);
    // Trim underscores from the beginning and end of the column name.
    $column = trim($column, '_');
    // Convert the column name to lowercase.
    $column = strtolower($column);

    if (is_numeric($column) || in_array($column, ImportJob::RESERVED_WORDS)) {
      // Prepend "_" to column name that are not allowed in MySQL
      // This can be dropped after move to Drupal 9.
      // @see https://github.com/GetDKAN/dkan/issues/3606
      $column = '_' . $column;
    }

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
  public static function truncateHeader(string $column): string {
    // If the supplied table column name is longer than the max column length,
    // truncate the column name to 5 characters under the max length and
    // substitute the truncated characters with a unique hash.
    if (strlen($column) > ImportJob::MAX_COLUMN_LENGTH) {
      $field = substr($column, 0, ImportJob::MAX_COLUMN_LENGTH - 5);
      $hash = substr(md5($column), 0, 4);
      $column = $field . '_' . $hash;
    }

    return $column;
  }

  /**
   * Get the storage object.
   */
  public function getStorage() {
    return $this->dataStorage;
  }

  /**
   * {@inheritdoc}
   */
  protected function runIt() {
    $filename = $this->resource->getFilePath();
    $size = @filesize($filename);
    if (!$size) {
      return $this->setResultError("Can't get size from file {$filename}");
    }

    if ($size <= $this->getBytesProcessed()) {
      return $this->getResult();
    }

    $maximum_execution_time = $this->getTimeLimit() ? (time() + $this->getTimeLimit()) : PHP_INT_MAX;

    try {
      $this->assertTextFile($filename);
      $this->parseAndStore($filename, $maximum_execution_time);
    }
    catch (\Exception $e) {
      return $this->setResultError($e->getMessage());
    }

    // Flush the parser.
    $this->store();

    if ($this->getBytesProcessed() >= $size) {
      $this->getResult()->setStatus(Result::DONE);
    }
    else {
      $this->getResult()->setStatus(Result::STOPPED);
    }

    return $this->getResult();
  }

  /**
   * Confirm this is a valid text file.
   *
   * Allow 'text/*' or 'application/*' per PHP 8.0 changes.
   *
   * @param string $filename
   *   Filename to test.
   *
   * @throws \Exception
   *   Will throw exception if not valid, do nothing if valid.
   */
  protected function assertTextFile(string $filename) {
    if ($mimeType = mime_content_type($filename)) {
      $mime_explode = explode('/', $mimeType);
      if (!in_array($mime_explode[0], ['text', 'application'])) {
        throw new \Exception("Invalid mime type: {$mimeType}");
      }
    }
  }

  /**
   * Add error message to result object.
   *
   * @param mixed $message
   *   Result message. Usually a string.
   *
   * @return \Procrastinator\Result
   *   Updated result object.
   */
  protected function setResultError($message): Result {
    $this->getResult()->setStatus(Result::ERROR);
    $this->getResult()->setError($message);
    return $this->getResult();
  }

  /**
   * Get current count of bytes processed of file.
   *
   * @return int
   *   Count of bytes processed.
   */
  protected function getBytesProcessed() {
    $chunksProcessed = $this->getStateProperty('chunksProcessed', 0);
    return $chunksProcessed * self::BYTES_PER_CHUNK;
  }

  /**
   * Parse a file and store results.
   *
   * @param string $filename
   *   The file name including path.
   * @param mixed $maximumExecutionTime
   *   Maximum time to parse for before exiting.
   */
  protected function parseAndStore($filename, $maximumExecutionTime) {
    $h = fopen($filename, 'r');
    fseek($h, $this->getBytesProcessed());

    $chunksProcessed = $this->getStateProperty('chunksProcessed', 0);
    while (time() < $maximumExecutionTime) {
      $chunk = fread($h, self::BYTES_PER_CHUNK);

      if (!$chunk) {
        $this->getResult()->setStatus(Result::DONE);
        $this->parser->finish();
        break;
      }
      $chunk = Encoding::toUTF8($chunk);
      $this->parser->feed($chunk);
      $chunksProcessed++;

      $this->store();
      $this->setStateProperty('chunksProcessed', $chunksProcessed);
    }
    fclose($h);
  }

  /**
   * Drop all import jobs.
   */
  public function drop() {
    $results = $this->dataStorage->retrieveAll();
    foreach ($results as $id => $data) {
      $this->dataStorage->remove($id);
    }
    $this->getResult()->setStatus(Result::STOPPED);
  }

  /**
   * Store the current instance of ImportJob.
   */
  protected function store() {
    $recordNumber = $this->getStateProperty('recordNumber', 0);
    $records = [];
    foreach ($this->parser->getRecords() as $record) {
      // Skip the first record. It is the header.
      if ($recordNumber != 0) {
        // @todo Identify if we need to pass an id to the storage.
        $records[] = json_encode($record);
      }
      else {
        $this->setStorageSchema($record);
      }
      $recordNumber++;
    }
    if (!empty($records)) {
      $this->dataStorage->storeMultiple($records);
    }
    $this->setStateProperty('recordNumber', $recordNumber);
  }

  /**
   * Set the schema for the datastore storage operation.
   *
   * @param array $header
   *   Array of header strings.
   */
  protected function setStorageSchema(array $header) {
    $schema = [];
    $this->assertUniqueHeaders($header);
    foreach ($header as $field) {
      $schema['fields'][$field] = [
        'type' => "text",
      ];
    }
    $this->dataStorage->setSchema($schema);
  }

  /**
   * Verify headers are unique.
   *
   * @param array $header
   *   List of strings.
   *
   * @throws \Exception
   */
  protected function assertUniqueHeaders(array $header) {
    if (count($header) != count(array_unique($header))) {
      $duplicates = array_keys(array_filter(array_count_values($header), function ($i) {
          return $i > 1;
      }));
      throw new \Exception("Duplicate headers error: " . implode(', ', $duplicates));
    }
  }

  /**
   * Get the parser object.
   *
   * @return \Contracts\ParserInterface
   *   Parser object.
   */
  public function getParser(): ParserInterface {
    return $this->parser;
  }

  /**
   * {@inheritdoc}
   */
  protected function serializeIgnoreProperties(): array {
    $ignore = parent::serializeIgnoreProperties();
    $ignore[] = "dataStorage";
    $ignore[] = "resource";
    return $ignore;
  }

}
