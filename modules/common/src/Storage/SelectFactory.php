<?php

namespace Drupal\common\Storage;

use Drupal\Core\Database\Query\Select;
use Drupal\Core\Database\Connection;

/**
 * Class to convert a DKAN Query object into a Drupal DB API Select Object.
 */
class SelectFactory {

  /**
   * Create Drupal select object.
   *
   * @param Query $query
   *   DKAN Query object.
   * @param Drupal\Core\Database\Connection $connection
   *   A database table object, which includes a database connection.
   *
   * @return Drupal\Core\Database\Query\Select
   *   Drupal DB API select object.
   */
  public static function create(Query $query, Connection $connection): Select {
    $db_query = $connection->select($query->collection, 't');

    self::setQueryProperties($db_query, $query);
    self::setQueryConditions($db_query, $query);
    self::setQueryOrConditions($db_query, $query);
    self::setQueryOrderBy($db_query, $query);
    self::setQueryLimitAndOffset($db_query, $query);

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
  private static function setQueryProperties(Select $db_query, Query $query) {
    // If properties is empty, just get all from base collection.
    if (empty($query->properties)) {
      $db_query->fields('t');
      return;
    }

    foreach ($query->properties as $p) {
      if (is_string($p)) {
        $db_query->addField('t', $p);
      }
      elseif (is_object($p) && isset($p->property)) {
        $db_query->addField($p->resource, $p->property, $p->alias);
      }
      elseif (is_object($p) && isset($p->expression)) {
        $expressionStr = $this->expressionToString($p->expression);
        $db_query->addExpression($expressionStr, $p->alias);
      }
    }
  }

  private static function expressionToString($expression) {
    throw new \Exception("Unsupported $expression");
  }

  /**
   * Set filter conditions on DB query.
   *
   * @param Drupal\Core\Database\Query\Select $db_query
   *   A Drupal database query API object.
   * @param Drupal\common\Storage\Query $query
   *   A DKAN query object.
   */
  private static function setQueryConditions(Select $db_query, Query $query) {
    foreach ($query->conditions as $c) {
      if (!isset($c->operator)) {
        $c->operator = 'like';
      }
      $c->operator = strtoupper($c->operator);
      $db_query->condition($c->property, $c->value, strtoupper($c->operator));
    }
  }

  /**
   * Set a group of filter "OR" conditions on DB query.
   *
   * @param Drupal\Core\Database\Query\Select $db_query
   *   A Drupal database query API object.
   * @param Query $query
   *   A DKAN query object.
   */
  private static function setQueryOrConditions(Select $db_query, Query $query) {
    if (empty($query->orConditions)) {
      return;
    }
    $orGroup = $db_query->orConditionGroup();
    foreach ($query->orConditions as $c) {
      if (!isset($c->operator)) {
        $c->operator = "LIKE";
      }
      $c->operator = strtoupper($c->operator);
      $orGroup->condition($c->property, $c->value, $c->operator);
    }
    $db_query->condition($orGroup);
  }

  /**
   * Set sort order on DB query.
   *
   * @param Drupal\Core\Database\Query\Select $db_query
   *   A Drupal database query API object.
   * @param Query $query
   *   A DKAN query object.
   */
  private static function setQueryOrderBy(Select $db_query, Query $query) {
    foreach ($query->sort['asc'] as $property) {
      $db_query->orderBy(strtolower($property));
    }

    foreach ($query->sort['desc'] as $property) {
      $db_query->orderBy(strtolower($property), 'DESC');
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
  private static function setQueryLimitAndOffset(Select $db_query, Query $query) {
    if ($query->limit) {
      if ($query->offset) {
        $db_query->range($query->offset, $query->limit);
      }
      else {
        $db_query->range(0, $query->limit);
      }
    }
    elseif ($query->offset) {
      $db_query->range($query->limit);
    }
  }

}
