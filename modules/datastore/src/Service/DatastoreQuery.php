<?php

namespace Drupal\datastore\Service;

use Procrastinator\HydratableTrait;
use Procrastinator\JsonSerializeTrait;

/**
 * DatastoreQuery.
 */
class DatastoreQuery {

  use HydratableTrait, JsonSerializeTrait;

  /**
   * The collection of records (usually, a database table) to query against.
   *
   * @var array
   */
  public $resources = [];

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
   * Joins for the query.
   *
   * @var array
   */
  public $joins = [];

  /**
   * Result sorting directives.
   *
   * @var object
   *   Containing properties:
   *   - asc: Properties to sort by in ascending order
   *   - desc: Properties to sort by in descending order
   */
  public $sort;

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
   * Return the schema for the resource?
   *
   * @var bool
   */
  public $schema = TRUE;

  /**
   * Show keys for each property in results. If false, results will be an array
   * of simple arrays rather than an array of keyed objects.
   *
   * @var bool
   */
  public $keys = TRUE;
}
