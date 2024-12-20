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
   * Keep track of whether this query has already been executed or not.
   */
  private bool $executed = FALSE;

  /**
   * Database connection.
   */
  protected Connection $connection;

  /**
   * Strptime-to-MySQL date format converter.
   */
  protected ConverterInterface $dateFormatConverter;

  /**
   * Query table.
   */
  protected string $table;

  /**
   * Query fields.
   */
  protected array $fields;

  /**
   * Query indexes.
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
    array $indexes,
  ) {
    $this->connection = $connection;
    $this->dateFormatConverter = $date_format_converter;
    $this->table = $this->connection->escapeTable($table);
    $this->fields = $fields;
    $this->indexes = $indexes;
  }

  /**
   * {@inheritDoc}
   */
  public function execute(): void {
    if ($this->executed) {
      throw new \Exception('Already executed. Use the query builder to build a new query rather than executing the same one twice.');
    }
    // Ensure either fields or indexes are present before attempting to run
    // this command.
    if (!empty($this->fields) || !empty($this->indexes)) {
      $this->doExecute();
    }
    $this->executed = TRUE;
  }

  /**
   * Actor for public execute method.
   */
  abstract protected function doExecute(): void;

}
