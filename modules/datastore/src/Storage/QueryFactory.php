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
    $this->datastoreQuery = $datastoreQuery;
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
   * @return \Drupal\common\Storage\Query
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
    $this->populateQueryGroupBy($query);
    $this->populateQuerySorts($query);
    if ($this->datastoreQuery->{"$.limit"}) {
      $query->limit = $this->datastoreQuery->{"$.limit"};
    }
    $query->offset = $this->datastoreQuery->{"$.offset"} ?? 0;
    $query->showDbColumns = TRUE;

    return $query;
  }

  /**
   * Helper function for adding group by clauses to the given query.
   *
   * @param Drupal\common\Storage\Query $query
   *   DKAN query object we're building.
   *
   * @throws \Exception
   *   When ungrouped properties are found in the datastore query.
   */
  private function populateQueryGroupBy(Query $query): void {
    $groupings = $this->extractPropertyNames($this->datastoreQuery->{"$.groupings"} ?? []);
    if (empty($groupings)) {
      return;
    }
    $props = $this->extractPropertyNames($this->datastoreQuery->{"$.properties"} ?? []);
    if ($ungrouped = array_diff($props, $groupings)) {
      throw new \Exception('Un-grouped properties found in aggregate query: ' . implode(', ', $ungrouped));
    }
    $query->groupby = $groupings;
  }

  /**
   * Extract list of property names from an array of properties.
   *
   * Properties can be either objects, associative-arrays, or strings. If a
   * property is an object or associative-array, the property name is stored in
   * the "property" field. If a property is a string, the string itself is a
   * property name.
   *
   * @param array $props
   *   List of query properties (which can be a string, associative array, or
   *   object, see above).
   *
   * @return string[]
   *   List of extracted property names.
   *
   * @throws \Exception
   *   When an invaid property is encountered.
   */
  protected function extractPropertyNames(array $props): array {
    return array_filter(array_map(function ($prop) {
      if (is_string($prop)) {
        return $prop;
      }
      // If prop is an array, convert it to an object.
      if (is_array($prop)) {
        $prop = (object) $prop;
      }
      if (is_object($prop)) {
        if (isset($prop->property)) {
          return $prop->property;
        }
        // If this property object is an expression, ignore it.
        if (isset($prop->expression)) {
          return NULL;
        }
      }
    }, $props));
  }

  /**
   * Populate a query object with the queries from a datastore query payload.
   *
   * @param Drupal\common\Storage\Query $query
   *   DKAN generalized query object.
   */
  private function populateQueryProperties(Query $query) {
    if (empty($this->datastoreQuery->{"$.properties"})) {
      return;
    }
    foreach ($this->datastoreQuery->{"$.properties"} as $property) {
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
    if (is_array($property) && isset($property["resource"])) {
      $property = (object) self::resourceRename($property);
    }
    elseif (is_array($property) && isset($property["expression"])) {
      $property["expression"] = $this->expressionConvert($property["expression"]);
      $property = (object) $property;
    }
    elseif (!is_string($property) && !is_numeric($property) && !is_array($property)) {
      throw new \Exception("Bad query property.");
    }
    return $property;
  }

  /**
   * Convert expressions from a datastore query to regular DKAN query format.
   *
   * @param array $expression
   *   An expression from a datastore query, including "resources".
   *
   * @return object
   *   Standardized expression object with "collection" instead of "resource".
   */
  private function expressionConvert(array $expression) {
    foreach ($expression["operands"] as $key => $operand) {
      $expression["operands"][$key] = $this->operandConvert($operand);
    }
    return (object) $expression;
  }

  /**
   * Convert an expression operand for expressionConvert().
   *
   * @param mixed $operand
   *   Operand from operands array.
   *
   * @return mixed
   *   Operand ready for query.
   */
  private function operandConvert($operand) {
    if (is_array($operand) && isset($operand["operator"])) {
      return $this->expressionConvert($operand);
    }
    elseif (is_numeric($operand)) {
      return $operand;
    }
    else {
      return $this->propertyConvert($operand);
    }
  }

  /**
   * Process both potential sorting direction.
   *
   * @param Drupal\common\Storage\Query $query
   *   DKAN query object we're building.
   */
  private function populateQuerySorts(Query $query) {
    if (!$this->datastoreQuery->{"$.sorts"}) {
      return;
    }
    foreach ($this->datastoreQuery->{"$.sorts"} as $sort) {
      $query->sorts[] = (object) $this->resourceRename($sort);
    }
  }

  /**
   * Parse and normalize query conditions.
   *
   * @param Drupal\common\Storage\Query $query
   *   DKAN query object we're building.
   */
  private function populateQueryConditions(Query $query) {
    if (empty($this->datastoreQuery->{"$.conditions"})) {
      return;
    }
    $conditions = [];
    $primaryAlias = $this->datastoreQuery->{"$.resources[0].alias"};
    foreach ($this->datastoreQuery->{"$.conditions"} as $c) {
      $conditions[] = $this->populateQueryCondition($c, $primaryAlias);
    }
    $query->conditions = $conditions;
  }

  /**
   * Parse and normalize a single datastore query condition.
   *
   * @param mixed $condition
   *   Either a condition object or a condition group.
   *
   * @return object
   *   Valid condition object for use in a DKAN query.
   */
  private function populateQueryCondition($condition) {
    $primaryAlias = $this->datastoreQuery->{"$.resources[0].alias"};
    if (isset($condition["property"])) {
      $return = (object) [
        "collection" => isset($condition["resource"]) ? $condition["resource"] : $primaryAlias,
        "property" => $condition["property"],
        "value" => $this->propertyConvert($condition["value"]),
      ];
      if (isset($condition["operator"])) {
        $return->operator = $condition["operator"];
      }
      return $return;
    }
    elseif (isset($condition["groupOperator"])) {
      return $this->populateQueryGroup($condition);
    }
    throw new \Exception("Invalid condition");
  }

  /**
   * Populate a single condition group.
   *
   * @param mixed $conditionGroup
   *   A conditionGroup array.
   *
   * @return object
   *   Valid condition group object for use in a DKAN query.
   */
  private function populateQueryGroup($conditionGroup) {
    foreach ($conditionGroup["conditions"] as $c) {
      $conditions[] = $this->populateQueryCondition($c);
    }
    return (object) [
      "groupOperator" => $conditionGroup["groupOperator"],
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
    if (empty($this->datastoreQuery->{"$.joins"}) && count($this->datastoreQuery->{"$.resources"}) <= 1) {
      return;
    }
    if (count($this->datastoreQuery->{"$.resources"}) > 1
      && count($this->datastoreQuery->{"$.joins"}) < (count($this->datastoreQuery->{"$.resources"}) - 1)) {
      throw new \Exception("Too many resources specified.");
    }
    foreach ($this->datastoreQuery->{"$.joins"} as $join) {
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
    $storage = $this->storageMap[$join["resource"]];
    $queryJoin = new \stdClass();
    $queryJoin->collection = $storage->getTableName();
    $queryJoin->alias = $join["resource"];
    $queryJoin->condition = (object) $this->populateQueryCondition($join["condition"]);
    return $queryJoin;
  }

  /**
   * Rename any "resource" keys to "collection" in assoc. array.
   *
   * @param array $input
   *   Input array.
   *
   * @return array
   *   Array with renamed keys.
   */
  private static function resourceRename(array $input) {
    $return = [];
    foreach ($input as $key => $value) {
      if ($key == "resource") {
        $key = "collection";
      }
      if (is_array($value)) {
        $value = self::resourceRename($value);
      }
      $return[$key] = $value;
    }
    return $return;
  }

}
