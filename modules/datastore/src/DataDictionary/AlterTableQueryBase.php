<?php

namespace Drupal\datastore\DataDictionary;

use Drupal\Core\Database\Connection;

use PDLT\ConverterInterface;

/**
 * Alter table query interface.
 *
 * Provides ability to alter schema of existing datastore tables.
 */
abstract class AlterTableQueryBase implements AlterTableQueryInterface {

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $connection;

  /**
   * Strptime-to-MySQL date format converter.
   *
   * @var \PDLT\ConverterInterface
   */
  protected ConverterInterface $dateFormatConverter;

  /**
   * Query table.
   *
   * @var string
   */
  protected string $table;

  /**
   * Query fields.
   *
   * @var array
   */
  protected array $fields;

  /**
   * Query indexes.
   *
   * @var array
   */
  protected array $indexes;

  /**
   * Build a table alter query.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \PDLT\ConverterInterface $date_format_converter
   *   Strptime-to-MySQL date format converter.
   * @param string $table
   *   Query table.
   * @param array $fields
   *   Query fields.
   * @param array $indexes
   *   Query indexes.
   */
  public function __construct(
    Connection $connection,
    ConverterInterface $date_format_converter,
    string $table,
    array $fields,
    array $indexes
  ) {
    $this->connection = $connection;
    $this->dateFormatConverter = $date_format_converter;
    $this->table = $this->connection->escapeTable($table);
    $this->fields = $fields;
    $this->indexes = $indexes;
  }

  /**
   * Apply types and indexes to the given table.
   */
  public function execute(): void {
    // Ensure either fields or indexes are present before attempting to run
    // this command.
    if (!empty($this->fields) || !empty($this->indexes)) {
      $this->doExecute();
    }
  }

  /**
   * Actor for public execute method.
   */
  abstract protected function doExecute(): void;

}
