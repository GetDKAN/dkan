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
   * Number of rows.
   *
   * @var int
   */
  public $numOfRows;

  /**
   * Constructor.
   */
  public function __construct(int $numOfColumns, array $columns, int $numOfRows) {
    $this->numOfColumns = $numOfColumns;
    $this->columns = $columns;
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
      'numOfRows' => $this->numOfRows,
    ];
  }

}
