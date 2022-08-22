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
   * Datastore table.
   *
   * @var string
   */
  protected string $datastoreTable;

  /**
   * Data-dictionary fields.
   *
   * @var array
   */
  protected array $dictionaryFields;

  /**
   * Data-dictionary indexes.
   *
   * @var array
   */
  protected array $dictionaryIndexes;

  /**
   * Build a table alter query.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   * @param \PDLT\ConverterInterface $date_format_converter
   *   Strptime-to-MySQL date format converter.
   * @param string $datastore_table
   *   Datastore table.
   * @param array $dictionary_fields
   *   Data-dictionary fields.
   * @param array $dictionary_indexes
   *   Data-dictionary indexes.
   */
  public function __construct(
    Connection $connection,
    ConverterInterface $date_format_converter,
    string $datastore_table,
    array $dictionary_fields,
    array $dictionary_indexes
  ) {
    $this->connection = $connection;
    $this->dateFormatConverter = $date_format_converter;
    $this->datastoreTable = $this->connection->escapeTable($datastore_table);
    $this->dictionaryFields = $dictionary_fields;
    $this->dictionaryIndexes = $dictionary_indexes;
  }

  /**
   * Apply data dictionary types to the given table.
   */
  abstract public function execute(): void;

}
