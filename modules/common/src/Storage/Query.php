<?php

namespace Drupal\common\Storage;

use Contracts\SorterInterface;
use Contracts\ConditionerInterface;
use Contracts\OffsetterInterface;
use Contracts\LimiterInterface;

/**
 * Query class.
 */
class Query implements
    SorterInterface,
    ConditionerInterface,
    OffsetterInterface,
    LimiterInterface {

  /**
   * The collection of records (usually, a database table) to query against.
   *
   * @var string
   */
  public $collection;

  /**
   * Properties (usually, columns) to retrieve from the collection.
   *
   * @var array
   */
  public $properties = [];

  /**
   * Condition statements for the query.
   *
   * @var array
   */
  public $conditions = [];

  /**
   * Result sorting directives.
   *
   * @var array
   *   Associative array containing:
   *   - ASC: Properties to sort by in ascending order
   *   - DESC: Properties to sort by in descending order
   */
  public $sort = ['ASC' => [], 'DESC' => []];

  /**
   * Limit for maximum number of records returned.
   *
   * @var int|null
   */
  public $limit = NULL;

  /**
   * Number of records to offset by or skip before returning first record.
   *
   * @var int|null
   */
  public $offset = NULL;

  /**
   * Whether to mark as count query.
   *
   * @var bool
   */
  public $count = FALSE;

  /**
   * Set the identifier of what is being retrieved.
   *
   * @param string $id
   *   Identifier of collection. When working with an SQL database, this would
   *   be the table name.
   */
  public function setCollectionToRetrieve($id) {
    $this->collection = $id;
  }

  /**
   * Add a single property to the properties being retrieved.
   *
   * @param string $property
   *   The name of a property, such as a column from a db table.
   */
  public function filterByProperty($property) {
    $this->properties[] = $property;
  }

  /**
   * Retrieve only objects with properties of certain values.
   *
   * @param string $property
   *   Property to filter on.
   * @param string $value
   *   Property value to filter against.
   */
  public function conditionByIsEqualTo(string $property, string $value) {
    $this->conditions[$property] = $value;
  }

  /**
   * Set the maximum number of records to return.
   *
   * @param int $number_of_items
   *   Number of items.
   */
  public function limitTo(int $number_of_items) {
    $this->limit = $number_of_items;
  }

  /**
   * Offset where we start getting records.
   *
   * @param int $offset
   *   Number of records to offset by before retrieving.
   */
  public function offsetBy(int $offset) {
    $this->offset = $offset;
  }

  /**
   * Sort records by the given property in ascending order.
   *
   * @param string $property
   *   Property to sort by in ascending order.
   */
  public function sortByAscending(string $property) {
    $this->sort['ASC'][] = $property;
  }

  /**
   * Sort records by the given property in descending order.
   *
   * @param string $property
   *   Property to sort by in descending order.
   */
  public function sortByDescending(string $property) {
    $this->sort['DESC'][] = $property;
  }

  /**
   * Mark query as a count query.
   */
  public function count() {
    $this->count = TRUE;
  }

}
