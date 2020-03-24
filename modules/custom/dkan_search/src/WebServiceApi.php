<?php

namespace Drupal\dkan_search;

use Drupal\dkan_common\JsonResponseTrait;
use Drupal\dkan_metastore\Service;
use Drupal\search_api\Query\QueryInterface;

/**
 * Controller.
 */
class WebServiceApi {
  use JsonResponseTrait;

  /**
   * Search.
   */
  public function search() {
    $storage = \Drupal::service("entity_type.manager")->getStorage('search_api_index');

    /* @var \Drupal\search_api\IndexInterface $index */
    $index = $storage->load('dkan');

    if (!$index) {
      return $this->getResponse((object) ['message' => "An index named [dkan] does not exist."], 500);
    }

    $fields = array_keys($index->getFields());

    $params = $this->getParams();

    /* @var $qh \Drupal\search_api\Utility\QueryHelper */
    $qh = \Drupal::service("search_api.query_helper");

    /* @var $query \Drupal\search_api\Query\QueryInterface */
    $query = $qh->createQuery($index);

    $this->setFullText($query, $params, $index);

    $this->setFieldConditions($query, $fields, $params);

    $facets = $this->getFacets($query, $fields);

    $this->setSort($query, $params, $fields);

    $this->setRange($query, $params);

    /* @var  $result ResultSet*/
    $result = $query->execute();
    $count = $result->getResultCount();

    $data = $this->getData($result);

    $responseBody = (object) [
      'total' => $count,
      'results' => $data,
      'facets' => $facets,
    ];

    return $this->getResponse($responseBody);
  }

  /**
   * Private.
   */
  private function getData($result) {
    /* @var  $metastore Service */
    $metastore = \Drupal::service("dkan_metastore.service");

    return array_filter(array_map(function ($item) use ($metastore) {
      $id = $item->getId();
      $id = str_replace("dkan_dataset/", "", $id);
      try {
        return json_decode($metastore->get("dataset", $id));
      }
      catch (\Exception $e) {
        return NULL;
      }
    }, $result->getResultItems()));
  }

  /**
   * Private.
   */
  private function setRange(QueryInterface $query, $params) {
    $end = ($params['page'] * $params['page-size']);
    $start = $end - $params['page-size'];
    $query->range($start, $params['page-size']);
  }

  /**
   * Private.
   */
  private function getParams() {
    $defaults = [
      "page-size" => 10,
      "page" => 1,
    ];

    /* @var $requestStack RequestStack */
    $requestStack = \Drupal::service('request_stack');
    $request = $requestStack->getCurrentRequest();
    $params = $request->query->all();

    foreach ($defaults as $param => $default) {
      $params[$param] = isset($params[$param]) ? $params[$param] : $default;
    }

    if ($params["page-size"] > 100) {
      $params["page-size"] = 100;
    }

    return $params;
  }

  /**
   * Private.
   */
  private function setFullText(QueryInterface $query, $params, $index) {
    if (!isset($params['fulltext'])) {
      return;
    }

    $fulltextFields = $index->getFulltextFields();
    if (empty($fulltextFields)) {
      return;
    }

    $values = [];
    foreach ($fulltextFields as $field) {
      $values[$field][] = $params['fulltext'];
    }

    $this->createConditionGroup($query, $values, 'OR');
  }

  /**
   * Private.
   */
  private function setFieldConditions(QueryInterface $query, $fields, $params) {
    foreach ($fields as $field) {
      if (isset($params[$field])) {
        $values[$field] = $this->getValuesFromCommaSeparatedString($params[$field]);
        $this->createConditionGroup($query, $values);
      }
    }
  }

  /**
   * Private.
   */
  private function getValuesFromCommaSeparatedString($string) {
    $values = [];
    foreach (explode(",", $string) as $value) {
      $values[] = trim($value);
    }
    return $values;
  }

  /**
   * Private.
   */
  private function getFacets(QueryInterface $query, $fields) {
    $facetsTypes = ['theme', 'keyword', 'publisher__name'];
    $facets = [];

    foreach ($facetsTypes as $type) {
      if (in_array($type, $fields)) {
        $facets = array_merge($facets, $this->getFacetsForType($query, $type));
      }
    }

    return $facets;
  }

  /**
   * Private.
   */
  private function getFacetsForType(QueryInterface $query, $type) {
    $facets = [];
    $field = '';

    /* @var  $metastore Service */
    $metastore = \Drupal::service("dkan_metastore.service");

    // Prepare facets for fields that correspond to objects.
    $matches = [];
    if (preg_match('/(.*)__(.*)/', $type, $matches)) {
      $schema = $matches[1];
      $field = $matches[2];
    }
    else {
      $schema = $type;
    }
    foreach ($metastore->getAll($schema) as $thing) {
      $facet_name = empty($field) ? $thing->data : $thing->data->{$field};
      $facets[] = $this->getFacetHelper($query, $type, $facet_name);
    }

    return $facets;
  }

  /**
   * Private.
   */
  private function getFacetHelper(QueryInterface $query, $type, $facet_name) {
    $myquery = clone $query;
    $myquery->addCondition($type, $facet_name);
    $result = $myquery->execute();
    return [
      'type' => $type,
      'name' => $facet_name,
      'total' => $result->getResultCount(),
    ];
  }

  /**
   * Private.
   */
  private function setSort(QueryInterface $query, $params, $fields) {
    if (isset($params['sort']) && in_array($params['sort'], $fields)) {
      $query->sort($params['sort'], $this->getSortOrder($query, $params));
      return;
    }

    $query->sort('search_api_relevance', $query::SORT_DESC);
  }

  /**
   * Private.
   */
  private function getSortOrder(QueryInterface $query, $params) {
    $default = $query::SORT_ASC;
    if (!isset($params['sort-order'])) {
      return $default;
    }
    if ($params['sort-order'] != 'asc' && $params['sort-order'] != 'desc') {
      return $default;
    }
    return ($params['sort-order'] == 'asc') ? $query::SORT_ASC : $query::SORT_DESC;
  }

  /**
   * Private.
   */
  private function createConditionGroup(QueryInterface $query, $array, $conjuction = 'AND') {
    $cg = $query->createConditionGroup($conjuction);
    foreach ($array as $field => $values) {
      foreach ($values as $value) {
        $cg->addCondition($field, $value);
      }
    }
    $query->addConditionGroup($cg);
  }

}
