<?php

namespace Drupal\common;

interface ResourceSchemaDetectionInterface {

  /**
   * The maximum length of a MySQL table column name.
   *
   * @var int
   */
  public const MAX_COLUMN_LENGTH = 64;

  /**
   * List of reserved words in MySQL 5.6-8 and MariaDB.
   *
   * @var array
   */
  public const RESERVED_WORDS = [
    'accessible', 'add', 'all', 'alter', 'analyze',
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
   * RegEx for matching UUIDs.
   *
   * @var string
   */
  public const UUID_REGEX = '%^[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}$%';

  /**
   * Accessor for schema property.
   *
   * @return array
   *  Schema property value.
   */
  public function getSchema(): array;

}
