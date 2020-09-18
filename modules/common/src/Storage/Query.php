<?php

namespace Drupal\common\Storage;

use Contracts\SorterInterface;
use Contracts\ConditionerInterface;
use Contracts\OffsetterInterface;
use Contracts\LimiterInterface;
use Procrastinator\HydratableTrait;
use Procrastinator\JsonSerializeTrait;

/**
 * Query class.
 */
class Query implements
    SorterInterface,
    ConditionerInterface,
    OffsetterInterface,
    LimiterInterface {

  use HydratableTrait, JsonSerializeTrait;

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
   * Conditions for the query. Will be appended with "AND".
   *
   * Should be an array of objects with properties:
   * - property: The property/field to filter on
   * - value: The value to filter for. Use an array for IN or BEWEEN operators.
   * - operator: Condition operator. =, <>, <, <=, >, >=, IN, NOT IN, BETWEEN
   *   are supported.
   *
   * @var array
   */
  public $conditions = [];

  /**
   * OR conditions for the query.
   *
   * Adds a group of conditions to the query joined by OR instead of AND.
   *
   * Should be an array of objects with properties:
   * - property: The property/field to filter on
   * - value: The value to filter for. Use an array for IN or BETWEEN operators.
   * - operator: Condition operator. =, <>, <, <=, >, >=, IN, NOT IN, BETWEEN
   *   are supported.
   *
   * @var array
   */
  public $orConditions = [];

  /**
   * Result sorting directives.
   *
   * @var array
   *   Associative array containing:
   *   - asc: Properties to sort by in ascending order
   *   - desc: Properties to sort by in descending order
   */
  public $sort = ['asc' => [], 'desc' => []];

  /**
   * Limit for maximum number of records returned.
   *
   * @var int|null
   */
  public $limit = 500;

  /**
   * Number of records to offset by or skip before returning first record.
   *
   * @var int|null
   */
  public $offset = NULL;

  /**
   * Return the full count of the query results, ignoring limit/offset.
   *
   * If combined with $results, will return both the full result set and count.
   * Defaults to FALSE.
   *
   * @var bool
   */
  public $count = TRUE;

  /**
   * Return the result set of the query.
   *
   * Set to FALSE and set $count to TRUE to fetch only a count.
   *
   * @var bool
   */
  public $results = TRUE;

  /**
   * Use real db field names, not human-readable desc. Also, show record_number.
   *
   * @var bool
   */
  public $showDbColumns = FALSE;

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
    $this->sort['asc'][] = $property;
  }

  /**
   * Sort records by the given property in descending order.
   *
   * @param string $property
   *   Property to sort by in descending order.
   */
  public function sortByDescending(string $property) {
    $this->sort['desc'][] = $property;
  }

  /**
   * Mark query as a count query.
   */
  public function count() {
    $this->count = TRUE;
  }

}
