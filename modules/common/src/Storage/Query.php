<?php

namespace Drupal\common\Storage;

use Contracts\SorterInterface;
use Contracts\ConditionerInterface;
use Contracts\OffsetterInterface;
use Contracts\LimiterInterface;

/**
 * DKAN API Query data object.
 */
class Query implements
    SorterInterface,
    ConditionerInterface,
    OffsetterInterface,
    LimiterInterface {

  /**
   * The collection of records (usually, a database table) to query against.
   *
   * @var array
   */
  public $dataDictionaryFields;


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
   * Result sorting directives.
   *
   * @var array
   *   Array of sort objects.
   */
  public $sorts = [];

  /**
   * Join against another table.
   *
   * @var array
   *   Should be an array of objects with properties:
   *   - resource: Uuid of the table's resource.
   *   - alias: Alias for the table.
   *   - on: Array of the condition to be met.
   */
  public $joins = [];

  /**
   * Fields to group by in query.
   *
   * @var string[]
   */
  public $groupby = [];

  /**
   * Limit for maximum number of records returned.
   *
   * @var int|null
   */
  public $limit;

  /**
   * Number of records to offset by or skip before returning first record.
   *
   * @var int|null
   */
  public $offset = 0;

  /**
   * Return the full count of the query results, ignoring limit/offset.
   *
   * @var bool
   */
  public $count = FALSE;

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
   * @param bool $case
   *   Case sensitive filter?
   */
  public function conditionByIsEqualTo(string $property, string $value, bool $case = FALSE) {
    $this->conditions[] = (object) [
      'property' => $property,
      'value' => $value,
      'operator' => $case ? 'LIKE BINARY' : 'LIKE',
    ];
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
    $this->sorts[] = (object) [
      "property" => $property,
      "order" => "asc",
    ];
  }

  /**
   * Sort records by the given property in descending order.
   *
   * @param string $property
   *   Property to sort by in descending order.
   */
  public function sortByDescending(string $property) {
    $this->sorts[] = (object) [
      "property" => $property,
      "order" => "desc",
    ];
  }

  /**
   * Mark query as a count query.
   *
   * Note - for now, leaves limit/offset alone.
   */
  public function count() {
    $this->count = TRUE;
    unset($this->limit);
  }

}
