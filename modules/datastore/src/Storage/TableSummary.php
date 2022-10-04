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
   * Constructor.
   */
  public function __construct(int $numOfColumns, array $columns, array $indexes, int $numOfRows) {
    $this->numOfColumns = $numOfColumns;
    $this->columns = $columns;
    $this->indexes = $indexes;
    $this->numOfRows = $numOfRows;
  }

  /**
   * Inherited.
   *
   * {@inheritdoc}
   */
  public function jsonSerialize() {
    return [
      'numOfColumns' => $this->numOfColumns,
      'columns' => $this->columns,
      'indexes' => $this->indexes,
      'numOfRows' => $this->numOfRows,
    ];
  }

}
