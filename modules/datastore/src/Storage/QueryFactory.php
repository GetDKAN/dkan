<?php

namespace Drupal\datastore\Storage;

use Drupal\common\Storage\Query;
use Drupal\datastore\Service\DatastoreQuery;

/**
 * Product a Query object based on a Datastore Query.
 */
class QueryFactory {

  /**
   * Datastore Query object for conversion.
   *
   * @var Drupal\datastore\Service\DatastoreQuery
   */
  private $datastoreQuery;

  /**
   * Storage map array of storage objects keyed by resource alias.
   *
   * @var array
   */
  private $storageMap;

  /**
   * Constructor.
   *
   * @param Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   Datastore query request object.
   * @param array $storageMap
   *   Storage map array.
   */
  public function __construct(DatastoreQuery $datastoreQuery, array $storageMap) {
    $this->datastoreQuery = $this->clone($datastoreQuery);
    $this->storageMap = $storageMap;
  }

  /**
   * Static factory create method.
   *
   * @param Drupal\datastore\Service\DatastoreQuery $datastoreQuery
   *   Datastore query request object.
   * @param array $storageMap
   *   Storage map array.
   *
   * @return Drupal\common\Storage\Query
   *   DKAN query object.
   */
  public static function create(DatastoreQuery $datastoreQuery, array $storageMap): Query {
    $factory = new self($datastoreQuery, $storageMap);
    return $factory->populateQuery();
  }

  /**
   * Create Query and populate with properties with DatastoreQuery object.
   *
   * @return Drupal\common\Storage\Query
   *   Query object.
   */
  public function populateQuery(): Query {
    $query = new Query();

    $this->populateQueryProperties($query);
    $this->populateQueryConditions($query);
    $this->populateQueryJoins($query);
    $this->populateQuerySorts($query);
    $query->limit = $this->datastoreQuery->limit;
    $query->offset = $this->datastoreQuery->offset;
    $query->showDbColumns = TRUE;

    return $query;
  }

  /**
   * Populate a query object with the queries from a datastore query payload.
   *
   * @param Drupal\common\Storage\Query $query
   *   DKAN generalized query object.
   */
  private function populateQueryProperties(Query $query) {
    foreach ($this->datastoreQuery->properties as $property) {
      $query->properties[] = $this->propertyConvert($property);
    }
  }

  /**
   * Convert properties from a datastore query to regular DKAN query format.
   *
   * @param mixed $property
   *   A datastore query property object, with "resource" properties.
   *
   * @return object
   *   Standardized property object with "collection" instead of "resource."
   */
  private function propertyConvert($property) {
    if (is_object($property) && isset($property->resource)) {
      $property->collection = $this->clone($property->resource);
      unset($property->resource);
    }
    elseif (is_object($property) && isset($property->expression)) {
      $property->expression = $this->expressionConvert($this->clone($property->expression));
    }
    elseif (!is_string($property)) {
      throw new \Exception("Bad query property.");
    }
    return $property;
  }

  /**
   * Convert expressions from a datastore query to regular DKAN query format.
   *
   * @param object $expression
   *   An expression from a datastore query, including "resources".
   *
   * @return object
   *   Standardized expression object with "collection" instead of "resource".
   */
  private function expressionConvert($expression) {
    foreach ($expression->operands as $key => $operand) {
      if (is_object($operand) && isset($operand->operator)) {
        $expression->operands[$key] = $this->expressionConvert($operand);
      }
      elseif (is_numeric($operand)) {
        continue;
      }
      else {
        $expression->operands[$key] = $this->propertyConvert($operand);
      }
    }
    return $expression;
  }

  /**
   * Process both potential sorting direction.
   *
   * @param Drupal\common\Storage\Query $query
   *   DKAN query object we're building.
   */
  private function populateQuerySorts(Query $query) {
    if (isset($this->datastoreQuery->sort->asc)) {
      $this->populateQuerySortDirection($query, 'asc');
    }
    if (isset($this->datastoreQuery->sort->desc)) {
      $this->populateQuerySortDirection($query, 'desc');
    }
  }

  /**
   * Populate a specific sorting direction.
   *
   * @param mixed $query
   *   DKAN query object we're building.
   * @param mixed $direction
   *   The specific sort direction to populate.
   */
  private function populateQuerySortDirection($query, $direction) {
    foreach ($this->datastoreQuery->sort->$direction as $sort) {
      $query->sort[$direction][] = $this->propertyConvert($sort);
    }
  }

  /**
   * Parse and normalize query conditions.
   *
   * @param Drupal\common\Storage\Query $query
   *   DKAN query object we're building.
   */
  private function populateQueryConditions(Query $query) {
    $conditions = [];
    $primaryAlias = $this->datastoreQuery->resources[0]->alias;
    foreach ($this->datastoreQuery->conditions as $c) {
      $conditions[] = $this->populateQueryCondition($c, $primaryAlias);
    }
    $query->conditions = $conditions;
  }

  /**
   * Parse and normalize a single datastore query condition.
   *
   * @param mixed $datastoreCondition
   *   Either a condition object or a condition group.
   * @param string $primaryAlias
   *   Alias for main resource being queried.
   *
   * @return object
   *   Valid condition object for use in a DKAN query.
   */
  private function populateQueryCondition($datastoreCondition, string $primaryAlias) {
    if (isset($datastoreCondition->property)) {
      $return = (object) [
        "collection" => isset($datastoreCondition->resource) ? $datastoreCondition->resource : $primaryAlias,
        "property" => $datastoreCondition->property,
        "value" => $datastoreCondition->value,
      ];
      if (isset($datastoreCondition->operator)) {
        $return->operator = $datastoreCondition->operator;
      }
      return $return;
    }
    elseif (isset($datastoreCondition->groupOperator)) {
      return $this->populateQueryGroup($datastoreCondition, $primaryAlias);
    }
    throw new \Exception("Invalid condition");
  }

  /**
   * Populate a single condition group.
   *
   * @param mixed $datastoreCondition
   *   Either a condition object or a condition group.
   * @param string $primaryAlias
   *   Alias for main resource being queried.
   *
   * @return object
   *   Valid condition group object for use in a DKAN query.
   */
  private function populateQueryGroup($datastoreCondition, $primaryAlias) {
    foreach ($datastoreCondition->conditions as $c) {
      $conditions[] = $this->populateQueryCondition($c, $primaryAlias);
    }
    return (object) [
      "groupOperator" => $datastoreCondition->groupOperator,
      "conditions" => $conditions,
    ];
  }

  /**
   * Helper function for converting joins to Query format.
   *
   * @param Drupal\common\Storage\Query $query
   *   DKAN query object we're building.
   */
  private function populateQueryJoins(Query $query) {
    if (empty($this->datastoreQuery->joins) && count($this->datastoreQuery->resources) <= 1) {
      return;
    }
    if (count($this->datastoreQuery->resources) > 1
      && count($this->datastoreQuery->joins) < (count($this->datastoreQuery->resources) - 1)) {
      throw new \Exception("Too many resources specified.");
    }
    foreach ($this->datastoreQuery->joins as $join) {
      $query->joins[] = $this->populateQueryJoin($join);
    }
  }

  /**
   * Populate a single join statement.
   *
   * @param object $join
   *   A join object from list of joins.
   */
  private function populateQueryJoin($join) {
    $storage = $this->storageMap[$join->resource];
    $queryJoin = new \stdClass();
    $queryJoin->collection = $storage->getTableName();
    $queryJoin->alias = $join->resource;
    $queryJoin->condition = (object) [
      "collection" => $join->condition->resource,
      "property" => $join->condition->property,
      "value" => (object) [
        "collection" => $join->condition->value->resource,
        "property" => $join->condition->value->property,
      ],
    ];
    return $queryJoin;
  }

  /**
   * Helper function to perform a deep clone of an object.
   *
   * Use with caution - no protection against infinite recursion.
   *
   * @param mixed $input
   *   Incoming object for cloning.
   *
   * @return object
   *   Deep-cloned object.
   */
  private function clone($input) {
    if (is_object($input)) {
      $output = unserialize(serialize($input));
      return $output;
    }
    return $input;
  }

}
