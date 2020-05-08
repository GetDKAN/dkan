<?php

namespace Drupal\common\Storage;

use Drupal\Core\Database\Query\Select;
use Drupal\datastore\Storage\Query;

/**
 * Class QueryToQueryHelperTrait.
 *
 * Given a Query object, setup a Drupal's select query.
 *
 * @codeCoverageIgnore
 */
trait QueryToQueryHelperTrait {

  /**
   * Private.
   */
  private function setQueryConditions(Select $db_query, Query $query) {
    foreach ($query->conditions as $property => $value) {
      $db_query->condition($property, $value, "LIKE");
    }
  }

  /**
   * Private.
   */
  private function setQueryOrderBy(Select $db_query, Query $query) {
    foreach ($query->sort['ASC'] as $property) {
      $db_query->orderBy($property);
    }

    foreach ($query->sort['DESC'] as $property) {
      $db_query->orderBy($property, 'DESC');
    }
  }

  /**
   * Private.
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
    elseif ($query->offset) {
      $db_query->range($query->limit);
    }
  }

}
