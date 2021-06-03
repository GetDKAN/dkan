<?php

namespace Drupal\metastore_search;

use Drupal\common\EventDispatcherTrait;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Query\Query;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\Utility\QueryHelperInterface;

/**
 * Trait QueryBuilderTrait.
 *
 * @package Drupal\metastore_search
 */
trait QueryBuilderTrait {
  use EventDispatcherTrait;

  /**
   * Private.
   *
   * @param array $params
   *   Array of search parameters.
   * @param \Drupal\search_api\IndexInterface $index
   *   A search_api Index object.
   * @param \Drupal\search_api\Utility\QueryHelperInterface $queryHelper
   *   A search_api QueryHelper object.
   *
   * @return array
   *   An array with the query as the first element and whether any conditions
   *   are active as the second. Example: [$query, TRUE].
   */
  private function getQuery(array $params, IndexInterface $index, QueryHelperInterface $queryHelper): array {
    $query = $queryHelper->createQuery($index);

    list($query, $activeFullText) = $this->setFullText($query, $params, $index);
    list($query, $activeCondition) = $this->setFieldConditions($query, $params, $index);
    $query = $this->setSort($query, $params, $index);
    $query = $this->setRange($query, $params, $index);

    return [$query, $activeFullText || $activeCondition];
  }

  /**
   * Private.
   *
   * @param \Drupal\search_api\Query\QueryInterface $query
   *   A search_api query object.
   * @param array $params
   *   Array of search parameters.
   * @param \Drupal\search_api\IndexInterface $index
   *   A search_api Index object.
   *
   * @return array
   *   An array with the query as the first element and whether fulltext is
   *   active as the second. Example: [$query, TRUE].
   */
  private function setFullText(QueryInterface $query, array $params, IndexInterface $index): array {
    if (!isset($params['fulltext']) || empty($params['fulltext'])) {
      return [$query, FALSE];
    }

    $fulltextFields = $index->getFulltextFields();

    if (empty($fulltextFields)) {
      return [$query, FALSE];
    }

    $conditions = [];
    foreach ($fulltextFields as $field) {
      $conditions[$field][] = $params['fulltext'];
    }

    $query = $this->createConditionGroup($query, $conditions, 'OR');

    return [$query, TRUE];
  }

  /**
   * Private.
   *
   * @param \Drupal\search_api\Query\QueryInterface $query
   *   A search_api query object.
   * @param array $conditions
   *   An array of conditions. Shape: [field_name => ['value1', 'value2], ...].
   * @param string $conjunction
   *   'OR' or 'AND'.
   *
   * @return \Drupal\search_api\Query\QueryInterface
   *   A search_api query object.
   */
  private function createConditionGroup(QueryInterface $query, array $conditions, string $conjunction = 'AND'): QueryInterface {
    $conditionGroup = $query->createConditionGroup($conjunction);

    foreach ($conditions as $field => $values) {
      foreach ($values as $value) {
        $conditionGroup->addCondition($field, $value);
      }
    }

    $query->addConditionGroup($conditionGroup);

    return $query;
  }

  /**
   * Private.
   *
   * @param \Drupal\search_api\Query\QueryInterface $query
   *   A search_api query object.
   * @param array $params
   *   Array of search parameters.
   * @param \Drupal\search_api\IndexInterface $index
   *   A search_api Index object.
   *
   * @return array
   *   An array with the query as the first element and whether conditions are
   *   active as the second. Example: [$query, TRUE].
   */
  private function setFieldConditions(QueryInterface $query, array $params, IndexInterface $index): array {
    $active = FALSE;

    $fields = array_keys($index->getFields());

    foreach ($fields as $field) {
      if (isset($params[$field])) {

        $info = $this->dispatchEvent(Search::EVENT_SEARCH_QUERY_BUILDER_CONDITION,
          [
            'field' => $field,
            'values' => $this->getValuesFromCommaSeparatedString($params[$field]),
            'conjunction' => 'AND',
          ]);

        $conditions = [];
        $conditions[$info['field']] = $info['values'];
        $query = $this->createConditionGroup($query, $conditions, $info['conjunction']);
        $active = TRUE;
      }
    }

    return [$query, $active];
  }

  /**
   * Private.
   *
   * @param \Drupal\search_api\Query\QueryInterface $query
   *   A search_api query object.
   * @param array $params
   *   Array of search parameters.
   * @param \Drupal\search_api\IndexInterface $index
   *   A search_api Index object.
   *
   * @return \Drupal\search_api\Query\QueryInterface
   *   A search_api query object.
   */
  private function setSort(QueryInterface $query, array $params, IndexInterface $index): QueryInterface {
    $fields = array_keys($index->getFields());

    if (isset($params['sort']) && in_array($params['sort'], $fields)) {
      $query->sort($params['sort'], $this->getSortOrder($params));
      return $query;
    }

    $query->sort('search_api_relevance', Query::SORT_DESC);
    return $query;
  }

  /**
   * Private.
   *
   * @param array $params
   *   Search parameters.
   *
   * @return mixed
   *   String describing sort order as ascending or descending.
   */
  private function getSortOrder(array $params) {
    $default = QueryInterface::SORT_ASC;
    if (!isset($params['sort-order'])) {
      return $default;
    }
    if ($params['sort-order'] != 'asc' && $params['sort-order'] != 'desc') {
      return $default;
    }
    return ($params['sort-order'] == 'asc') ? QueryInterface::SORT_ASC :
      QueryInterface::SORT_DESC;
  }

  /**
   * Private.
   *
   * @param \Drupal\search_api\Query\QueryInterface $query
   *   A search_api query object.
   * @param array $params
   *   Array of search parameters.
   *
   * @return \Drupal\search_api\Query\QueryInterface
   *   A search_api query object.
   */
  private function setRange(QueryInterface $query, array $params): QueryInterface {
    $defaults = [
      'page' => 1,
      'page-size' => 10,
    ];
    $params = $params + $defaults;

    $end = ($params['page'] * $params['page-size']);
    $start = $end - $params['page-size'];
    $query->range($start, $params['page-size']);

    return $query;
  }

}
