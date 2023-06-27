<?php

namespace Drupal\common\Storage;

use Drupal\Core\Database\Query\Select;
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
   * Our select object.
   *
   * @var \Drupal\Core\Database\Query\Select
   */
  private $dbQuery;

  /**
   * Iterator for "words" named placeholder.
   *
   * @var int
   */
  private $wordsIterator = 0;

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

    $this->dbQuery = $this->connection->select($query->collection, $this->alias);
    $this->setQueryProperties($query);
    $this->setQueryConditions($query);
    $this->setQueryGroupBy($query);
    $this->setQueryOrderBy($query);
    $this->setQueryLimitAndOffset($query);
    $this->setQueryJoins($query);
    if (!empty($query->dataDictionaryFields)) {
      $meta_data = $query->dataDictionaryFields;
      $fields = $this->dbQuery->getFields();
      $this->addDateExpressions($this->dbQuery, $fields, $meta_data);
    }
    // $string = $this->dbQuery->__toString();
    if ($query->count) {
      $this->dbQuery = $this->dbQuery->countQuery();
    }
    return $this->dbQuery;
  }

  /**
   * Specify fields on DB query.
   *
   * @param Drupal\common\Storage\Query $query
   *   A DKAN query object.
   */
  private function setQueryProperties(Query $query) {
    // If properties is empty, just get all from base collection.
    if (empty($query->properties)) {
      $this->dbQuery->fields($this->alias);

      return;
    }
    foreach ($query->properties as $p) {
      $this->setQueryProperty($p);
    }
  }

  /**
   * Reformatting date fields.
   *
   *  {@inheritdoc}
   */
  private function addDateExpressions($db_query, $fields, $meta_data) {
    foreach ($meta_data as $definition) {
      // Confirm definition name is in the fields list.
      if ($fields[$definition['name']]['field'] && $definition['type'] == 'date') {
        $db_query->addExpression("DATE_FORMAT(" . $definition['name'] . ", '" . $definition['format'] . "')", $definition['name']);
      }
    }
  }

  /**
   * Set a single property.
   *
   * @param mixed $property
   *   One property from a query properties array.
   */
  private function setQueryProperty($property) {

    if (isset($property->expression)) {
      $expressionStr = $this->expressionToString($property->expression);
      $this->dbQuery->addExpression($expressionStr, $property->alias);
    }
    else {
      $property = $this->normalizeProperty($property);
      $this->dbQuery->addField($property->collection, $property->property, $property->alias);
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
  private function expressionToString($expression) {
    $operands = [];
    $expressionStr = "";
    $supportedFunctions = $this->getSupportedFunctions();
    foreach ($expression->operands as $operand) {
      $operands[] = $this->normalizeOperand($operand);
    }

    if (!ctype_alnum($expression->operator)) {
      $expressionStr = implode(" $expression->operator ", $operands);
    }
    elseif (in_array(strtolower($expression->operator), $supportedFunctions)) {
      $operator = strtoupper($expression->operator);
      $column = reset($operands);
      $expressionStr = "$operator($column)";
    }
    else {
      throw new \Exception("Only basic arithmetic expressions and basic SQL functions currently supported.");
    }

    return "($expressionStr)";
  }

  /**
   * Return supported SQL functions.
   */
  private function getSupportedFunctions() {
    return [
      'sum',
      'count',
      'avg',
      'max',
      'min',
    ];
  }

  /**
   * Normalize an operand for use in Select query.
   *
   * @param mixed $operand
   *   Operand from a query's expression operand array.
   *
   * @return mixed
   *   String or numeric operand for expression.
   */
  private function normalizeOperand($operand) {
    if (is_numeric($operand)) {
      return $operand;
    }
    elseif (is_object($operand) && isset($operand->expression)) {
      return $this->expressionToString($operand->expression);
    }
    else {
      return $this->propertyToString($operand);
    }
  }

  /**
   * Convert a "property" property to a string, including alias.
   *
   * @param mixed $property
   *   Property object or string representing a property for main collection.
   *
   * @return string
   *   Property name with alias prefix.
   */
  private function propertyToString($property) {
    $property = $this->normalizeProperty($property);
    return "{$property->collection}.{$property->property}";
  }

  /**
   * Set filter conditions on DB query.
   *
   * @param Drupal\common\Storage\Query $query
   *   A DKAN query object.
   */
  private function setQueryConditions(Query $query) {
    foreach ($query->conditions as $c) {
      if (isset($c->groupOperator)) {
        $this->addConditionGroup($this->dbQuery, $c);
      }
      else {
        $this->addCondition($this->dbQuery, $c);
      }
    }
  }

  /**
   * Add a condition to the DB query object.
   *
   * @param \Drupal\Core\Database\Query\Select|\Drupal\Core\Database\Query\Condition $statementObj
   *   Drupal DB API select object or condition object.
   * @param object $condition
   *   A condition from the DKAN query object.
   */
  private function addCondition($statementObj, $condition) {
    $this->normalizeOperator($condition);
    if ($condition->operator == "match") {
      $this->addMatchCondition($statementObj, $condition);
      return;
    }
    $field = (isset($condition->collection) ? $condition->collection : $this->alias)
      . '.'
      . $condition->property;
    $statementObj->condition($field, $condition->value, strtoupper($condition->operator));
  }

  /**
   * Add a custom where condition in the case of a fulltext match operator.
   *
   * Currently, only BOOLEAN MODE Mysql fulltext searches supported.
   *
   * @param \Drupal\Core\Database\Query\Select|\Drupal\Core\Database\Query\Condition $statementObj
   *   Drupal DB API select object or condition object.
   * @param object $condition
   *   A condition from the DKAN query object.
   */
  private function addMatchCondition($statementObj, $condition) {
    $properties = explode(',', $condition->property);
    $fields = [];
    foreach ($properties as $property) {
      $fields[] = ($condition->collection ?? $this->alias)
      . '.'
      . $property;
    }
    $fields_list = implode(',', $fields);

    $where = "MATCH($fields_list) AGAINST (:words{$this->wordsIterator} IN BOOLEAN MODE)";
    $statementObj->where($where, [":words{$this->wordsIterator}" => $condition->value]);
    $this->wordsIterator++;
  }

  /**
   * Fix any quirks in DKAN query object that won't translate well to SQL.
   *
   * @param object $condition
   *   A condition from the DKAN query object.
   */
  private function normalizeOperator($condition) {
    if (!isset($condition->operator)) {
      $condition->operator = '=';
    }
    elseif ($condition->operator == 'contains') {
      $condition->operator = 'like';
      $condition->value = "%{$condition->value}%";
    }
    elseif ($condition->operator == 'starts with') {
      $condition->operator = 'like';
      $condition->value = "{$condition->value}%";
    }
  }

  /**
   * Add a condition group to the database query.
   *
   * @param Drupal\Core\Database\Query\Select|Drupal\Core\Database\Query\Condition $statementObj
   *   Drupal DB API select object.
   * @param object $conditionGroup
   *   A condition from the DKAN query object.
   */
  private function addConditionGroup($statementObj, $conditionGroup) {
    $groupMethod = "{$conditionGroup->groupOperator}ConditionGroup";
    $group = $this->dbQuery->$groupMethod();
    foreach ($conditionGroup->conditions as $c) {
      if (isset($c->groupOperator)) {
        $this->addConditionGroup($group, $c);
      }
      else {
        $this->addCondition($group, $c);
      }
    }
    $statementObj->condition($group);
  }

  /**
   * Set fields to group by on DB query.
   *
   * @param Query $query
   *   A DKAN query object.
   */
  private function setQueryGroupBy(Query $query) {
    array_map([$this->dbQuery, 'groupBy'], $query->groupby);
  }

  /**
   * Set sort order on DB query.
   *
   * @param Query $query
   *   A DKAN query object.
   */
  private function setQueryOrderBy(Query $query) {
    foreach ($query->sorts as $sort) {
      $this->setQueryDirectionOrderBy($sort, $this->dbQuery);
    }
  }

  /**
   * Sort helper function.
   *
   * Set order by statements for a specific direction.
   *
   * @param object $sort
   *   The sort properties.
   */
  private function setQueryDirectionOrderBy($sort) {
    if (!is_object($sort) || !in_array($sort->order, ["asc", "desc"])) {
      throw new \InvalidArgumentException("Invalid sort.");
    }
    if (!isset($sort->order)) {
      $sort->order = "asc";
    }

    $propertyStr = $sort->property;
    if (isset($sort->collection)) {
      $propertyStr = "{$sort->collection}.{$propertyStr}";
    }
    $this->dbQuery->orderBy($propertyStr, strtoupper($sort->order));
  }

  /**
   * Set limit and offset on DB query.
   *
   * @param Query $query
   *   A DKAN query object.
   */
  private function setQueryLimitAndOffset(Query $query) {
    if (isset($query->limit) && $query->limit !== NULL) {
      $this->dbQuery->range(($query->offset ?? 0), ($query->limit));
    }
    elseif (isset($query->offset) && $query->offset) {
      $this->dbQuery->range(($query->offset));
    }
  }

  /**
   * Add joins to the DB query.
   *
   * @param Query $query
   *   A DKAN query object.
   */
  private function setQueryJoins(Query $query) {
    foreach ($query->joins as $join) {
      if (isset($join->condition)) {
        $this->dbQuery->join($join->collection, $join->alias, $this->conditionString($join->condition));
      }
      if (empty($query->properties)) {
        $this->dbQuery->fields($join->alias);
      }
    }
  }

  /**
   * Format a DKAN query "On" object as a string for SQL join.
   *
   * @param object $condition
   *   Join "condition" object from DKAN query.
   *
   * @return string
   *   A proper "on" condition string for SQL join.
   */
  private function conditionString($condition): string {
    if (!isset($condition->operator)) {
      $condition->operator = '=';
    }

    if (!isset($condition->collection) || !isset($condition->value->collection)) {
      throw new \Exception("Invalid join condition; collection must be specified.");
    }

    $value = $this->propertyToString($condition->value);
    return "{$condition->collection}.{$condition->property} $condition->operator $value";
  }

}
