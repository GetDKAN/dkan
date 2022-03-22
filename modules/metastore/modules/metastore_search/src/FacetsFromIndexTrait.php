<?php

namespace Drupal\metastore_search;

use Drupal\search_api\IndexInterface;
use Drupal\search_api\Query\QueryInterface;

/**
 * Retrieves search API facets from search api indexes.
 *
 * @package Drupal\metastore_search
 */
trait FacetsFromIndexTrait {
  use FacetsCommonTrait;

  /**
   * Private.
   *
   * @param array $params
   *   Array of search_api parameters.
   * @param \Drupal\search_api\IndexInterface $index
   *   A search_api index.
   * @param \Drupal\search_api\Query\QueryInterface $query
   *   A search_api Query object.
   *
   * @return array
   *   An array of facets.
   */
  private function getFacetsFromIndex(array $params, IndexInterface $index, QueryInterface $query): array {
    $fields = array_keys($index->getFields());
    $facetsTypes = $this->getFacetsTypes($params);
    $facets = [];
    foreach ($facetsTypes as $type) {
      if (in_array($type, $fields)) {
        $facets = array_merge($facets, $this->getFacetsForType($type, $query));
      }
    }
    return $facets;
  }

  /**
   * Private.
   *
   * @param string $type
   *   String describing type.
   * @param \Drupal\search_api\Query\QueryInterface $query
   *   A search_api Query object.
   *
   * @return array
   *   Array containing the facets.
   */
  private function getFacetsForType(string $type, QueryInterface $query) {
    $facets = [];
    $field = '';

    // Prepare facets for fields that correspond to objects.
    $matches = [];
    if (preg_match('/(.*)__(.*)/', $type, $matches)) {
      $schema = $matches[1];
      $field = $matches[2];
    }
    else {
      $schema = $type;
    }
    foreach ($this->metastoreService->getAll($schema) as $collection) {
      $facet_name = empty($field) ? $collection->{'$.data'} : $collection->{'$.data.' . $field};
      $facets[] = $this->getFacet($type, $facet_name, $query);
    }

    return $facets;
  }

  /**
   * Private.
   *
   * @param string $type
   *   Type.
   * @param string $facetName
   *   Face name.
   * @param \Drupal\search_api\Query\QueryInterface $query
   *   A search_api Query object.
   *
   * @return array
   *   Results for a facet.
   */
  private function getFacet(string $type, string $facetName, QueryInterface $query): object {
    $cloneQuery = clone $query;
    $cloneQuery->addCondition($type, $facetName);
    $result = $cloneQuery->execute();
    return (object) [
      'type' => $type,
      'name' => $facetName,
      'total' => $result->getResultCount(),
    ];
  }

}
