<?php

namespace Drupal\datastore\Storage;

/**
 * Simple object for storing summary information.
 *
 * @todo Use JSON Schema maybe to validate this?
 */
class TableSummary implements \JsonSerializable {

  /**
   * Number of columns.
   *
   * @var int
   */
  public $numOfColumns;

  /**
   * Summary columns.
   *
   * @var string[]
   */
  public $columns;

  /**
   * Summary indexes.
   *
   * @var array
   */
  protected $indexes;

  /**
   * Number of rows.
   *
   * @var int
   */
  public $numOfRows;

  /**
   * Full text indexes.
   */
  private ?array $fulltextIndexes;

  /**
   * Constructor.
   *
   * @param int $numOfColumns
   *   Number of columns.
   * @param array $columns
   *   Summary columns.
   * @param array|null $indexes
   *   Summary indexes.
   * @param array|null $fulltext_indexes
   *   Full text indexes.
   * @param int $numOfRows
   *   Number of rows.
   */
  public function __construct(
    int $numOfColumns,
    array $columns,
    ?array $indexes,
    ?array $fulltext_indexes,
    int $numOfRows
  ) {
    $this->numOfColumns = $numOfColumns;
    $this->columns = $columns;
    $this->indexes = $indexes;
    $this->fulltextIndexes = $fulltext_indexes;
    $this->numOfRows = $numOfRows;
  }

  /**
   * Specify data which should be serialized to JSON.
   *
   * @return mixed
   */
  #[\ReturnTypeWillChange]
  public function jsonSerialize() {
    return array_filter([
      'numOfColumns' => $this->numOfColumns,
      'columns' => $this->columns,
      'indexes' => $this->indexes,
      'fulltextIndexes' => $this->fulltextIndexes,
      'numOfRows' => $this->numOfRows,
    ]);
  }

}
