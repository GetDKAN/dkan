<?php

namespace Drupal\common\Storage;

use Drupal\Core\Database\Query\Select;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Database\Connection;

/**
 * Class to convert a DKAN Query object into a Drupal DB API Select Object.
 */
class SelectFactory {

  /**
   * A database table object, which includes a database connection.
   *
   * @var Drupal\Core\Database\Connection
   */
  private $connection;

  /**
   * Alias for primary table.
   *
   * @var string
   */
  private $alias;

  /**
   * Constructor function.
   *
   * @param Drupal\Core\Database\Connection $connection
   *   A database table object, which includes a database connection.
   * @param string $alias
   *   Alias for primary table.
   */
  public function __construct(Connection $connection, string $alias = 't') {
    $this->connection = $connection;
    $this->alias = $alias;
  }

  /**
   * Create Drupal select object.
   *
   * @param Drupal\common\Storage\Query $query
   *   DKAN Query object.
   */
  public function create(Query $query): Select {
    $db_query = $this->connection->select($query->collection, $this->alias);

    $this->setQueryProperties($db_query, $query);
    $this->setQueryConditions($db_query, $query);
    $this->setQueryOrderBy($db_query, $query);
    $this->setQueryLimitAndOffset($db_query, $query);
    $this->setQueryJoins($db_query, $query);

    // $string = $db_query->__toString();
    if ($query->count) {
      $db_query = $db_query->countQuery();
    }
    return $db_query;
  }

  /**
   * Set filter conditions on DB query.
   *
   * @param Drupal\Core\Database\Query\Select $db_query
   *   A Drupal database query API object.
   * @param Drupal\common\Storage\Query $query
   *   A DKAN query object.
   */
  private function setQueryProperties(Select $db_query, Query $query) {
    // If properties is empty, just get all from base collection.
    if (empty($query->properties)) {
      $db_query->fields($this->alias);
      return;
    }

    foreach ($query->properties as $p) {
      if (is_object($p) && isset($p->expression)) {
        $expressionStr = $this->expressionToString($p->expression);
        $db_query->addExpression($expressionStr, $p->alias);
      }
      else {
        $property = $this->normalizeProperty($p);
        $db_query->addField($property->collection, $property->property, $property->alias);
      }
    }
  }

  /**
   * Normalizes query properties as objects with consistent property names.
   *
   * @param mixed $property
   *   A property object or string from the Query::properties array.
   *
   * @return object
   *   Normalized property for conversion to field in select object.
   */
  private function normalizeProperty($property): object {
    if (is_string($property) && self::safeProperty($property)) {
      return (object) [
        "collection" => $this->alias,
        "property" => $property,
        "alias" => NULL,
      ];
    }
    if (!is_object($property) || !isset($property->property) || !isset($property->collection)) {
      throw new \Exception("Bad query property: " . print_r($property, 1));
    }
    self::safeProperty($property->property);
    if (!isset($property->alias)) {
      $property->alias = NULL;
    }
    return $property;
  }

  /**
   * Checks for any "." in property name and throws exception of found.
   *
   * All property names should be structured objects if they need to specify a
   * collection.
   *
   * @param string $string
   *   Property name.
   */
  public static function safeProperty(string $string) {
    if (preg_match("/^[^.]+$/", $string)) {
      return TRUE;
    }
    throw new \Exception("Unsafe property name: $string");
  }

  /**
   * When adding an expression, collection and property must one string.
   *
   * (The datastore query API, however, requires a structured object.)
   *
   * @param object $expression
   *   Query expression object.
   *
   * @return string
   *   Valid expression string.
   */
  private function expressionToString(object $expression) {
    $operands = [];
    foreach ($expression->operands as $operand) {
      if (is_numeric($operand)) {
        $operands[] = $operand;
      }
      elseif (is_object($operand) && isset($operand->operator)) {
        $operands[] = $this->expressionToString($operand);
      }
      else {
        $property = $this->normalizeProperty($operand);
        $operands[] = "{$property->collection}.{$property->property}";
      }
    }

    if (ctype_alnum($expression->operator)) {
      throw new \Exception("Only basic arithmetic expressions currently supported.");
    }
    else {
      $expressionStr = implode(" $expression->operator ", $operands);
    }

    return "($expressionStr)";
  }

  /**
   * Set filter conditions on DB query.
   *
   * @param Drupal\Core\Database\Query\Select $db_query
   *   A Drupal database query API object.
   * @param Drupal\common\Storage\Query $query
   *   A DKAN query object.
   */
  private function setQueryConditions(Select $db_query, Query $query) {
    foreach ($query->conditions as $c) {
      if (isset($c->groupOperator)) {
        $this->addConditionGroup($db_query, $c);
      }
      else {
        $this->addCondition($db_query, $c);
      }
    }
  }

  /**
   * Add a condition to the DB query object.
   *
   * @param mixed $db_query
   *   Drupal DB API select object or condition object.
   * @param object $condition
   *   A condition from the DKAN query object.
   */
  private function addCondition($db_query, object $condition) {
    if (!isset($condition->operator)) {
      $condition->operator = '=';
    }
    $field = ($condition->collection ? $condition->collection : $this->alias)
      . '.'
      . $condition->property;
    $db_query->condition($field, $condition->value, strtoupper($condition->operator));
  }

  /**
   * Add a condition group to the database query.
   *
   * @param Drupal\Core\Database\Query\Select|Drupal\Core\Database\Query\Condition $db_query
   *   Drupal DB API select object.
   * @param object $conditionGroup
   *   A condition from the DKAN query object.
   */
  private function addConditionGroup($db_query, $conditionGroup) {
    $groupMethod = "{$conditionGroup->groupOperator}ConditionGroup";
    $group = $db_query->$groupMethod();
    foreach ($conditionGroup->conditions as $c) {
      if (isset($c->groupOperator)) {
        $this->addConditionGroup($group, $c);
      }
      else {
        $this->addCondition($group, $c);
      }
    }
    $db_query->condition($group);
  }

  /**
   * Set sort order on DB query.
   *
   * @param Drupal\Core\Database\Query\Select $db_query
   *   A Drupal database query API object.
   * @param Query $query
   *   A DKAN query object.
   */
  private function setQueryOrderBy(Select $db_query, Query $query) {
    foreach ($query->sort as $direction => $sort) {
      if (!in_array($direction, ["asc", "desc"])) {
        throw new \Exception("Invalid sort.");
      }
      foreach ($sort as $property) {
        $nProperty = $this->normalizeProperty($property);
        $propertyStr = "{$nProperty->collection}.{$nProperty->property}";
        $db_query->orderBy($propertyStr, strtoupper($direction));
      }
    }
  }

  /**
   * Set limit and offset on DB query.
   *
   * @param Drupal\Core\Database\Query\Select $db_query
   *   A Drupal database query API object.
   * @param Query $query
   *   A DKAN query object.
   */
  private function setQueryLimitAndOffset(Select $db_query, Query $query) {
    if ($query->limit) {
      if ($query->offset) {
        $db_query->range($query->offset, $query->limit);
      }
      else {
        $db_query->range(0, $query->limit);
      }
    }
  }

  /**
   * Add joins to the DB query.
   *
   * @param Drupal\Core\Database\Query\Select $db_query
   *   A Drupal database query API object.
   * @param Query $query
   *   A DKAN query object.
   */
  private static function setQueryJoins(Select $db_query, Query $query) {
    if (empty($query->joins)) {
      return;
    }
    foreach ($query->joins as $join) {
      if (isset($join->on)) {
        $db_query->join($join->collection, $join->alias, self::onString($join->on));
      }
      if (empty($query->properties)) {
        $db_query->fields($join->alias);
      }
    }
  }

  /**
   * Format a DKAN query "On" object as a string for SQL join.
   *
   * @param object $on
   *   Join "on" object from DKAN query.
   *
   * @return string
   *   A proper "on" string for SQL join.
   */
  private static function onString($on): string {
    return "{$on[0]->collection}.{$on[0]->property} = {$on[1]->collection}.{$on[1]->property}";
  }

}
