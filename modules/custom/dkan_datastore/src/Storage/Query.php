<?php

namespace Drupal\dkan_datastore\Storage;

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

  public $thing;
  public $properties = [];
  public $conditions = [];
  public $sort = ['ASC' => [], 'DESC' => []];
  public $limit = NULL;
  public $offset = NULL;
  public $count = FALSE;

  /**
   * Set the identifier of what is being retrieved.
   *
   * In the case of things being retrieved from a SQL database,
   * a table name would be the identifier.
   */
  public function setThingToRetrieve($id) {
    $this->thing = $id;
  }

  /**
   * Properties to be retrieved.
   */
  public function filterByProperty($property) {
    $this->properties[] = $property;
  }

  /**
   * Retrieve only objects with properties of certain values.
   */
  public function conditionByIsEqualTo(string $property, string $value) {
    $this->conditions[$property] = $value;
  }

  /**
   * Get a specific number of records.
   */
  public function limitTo(int $number_of_items) {
    $this->limit = $number_of_items;
  }

  /**
   * Offset where we start getting records.
   */
  public function offsetBy(int $offset) {
    $this->offset = $offset;
  }

  /**
   * Sort records by the given property in ascending order.
   */
  public function sortByAscending(string $property) {
    $this->sort['ASC'][] = $property;
  }

  /**
   * Sort records by the given property in descending order.
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
