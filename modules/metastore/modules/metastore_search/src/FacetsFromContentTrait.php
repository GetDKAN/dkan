<?php

namespace Drupal\metastore_search;

use Drupal\search_api\Query\QueryInterface;
use RootedData\RootedJsonData;

/**
 * Trait used to generate facets from search API query conditions.
 *
 * @package Drupal\metastore_search
 */
trait FacetsFromContentTrait {
  use FacetsCommonTrait;

  /**
   * Private.
   *
   * @param array $params
   *   Array of search_api parameters.
   * @param \Drupal\search_api\Query\QueryInterface $query
   *   A search_api Query object.
   *
   * @return array
   *   Array of facets.
   */
  private function getFacetsFromContent(array $params, QueryInterface $query) : array {

    $results = $query->execute()->getResultItems();

    $datasets = array_map(function ($datasetId) {
      $cleanId = str_replace("dkan_dataset/", "", $datasetId);
      return $this->metastoreService->get('dataset', $cleanId);
    }, array_keys($results));

    $facetsTypes = $this->getFacetsTypes($params);

    $facets = $this->getAllFacetsWithZeroCounts($facetsTypes);

    $facetsKeys = array_reduce($datasets,
      function ($facetsKeys, $dataset) use ($facetsTypes) {
        $datasetFacetsKeys = $this->getDatasetFacetsKeys($dataset, $facetsTypes);
        return array_merge($facetsKeys, $datasetFacetsKeys);
      }, []);

    foreach ($facetsKeys as $key) {
      $facets[$key]->total++;
    }

    $facets = array_values($facets);
    return $facets;
  }

  /**
   * Private.
   *
   * A key is a unique identifier for a facet with the form
   * facet_type:facet_value.
   *
   * @param string $dataset
   *   A json string for a dataset.
   * @param array $facetsTypes
   *   The relevant facet types.
   *
   * @throws \JsonPath\InvalidJsonException
   */
  private function getDatasetFacetsKeys(string $dataset, array $facetsTypes) {
    $rooted = new RootedJsonData($dataset);

    // Map each facetType to an array of facetKeys of that type.
    $facetsKeysByType = array_map(function ($facetsType) use ($rooted) {
      $values = $this->getPropertyValuesFromFacetsType($rooted, $facetsType);
      return array_map(function ($value) use ($facetsType) {
        return "{$facetsType}:{$value}";
      }, $values);
    }, $facetsTypes);

    return array_reduce($facetsKeysByType, function ($carry, $facetsKeys) {
      return array_merge($carry, $facetsKeys);
    }, []);
  }

  /**
   * Private.
   *
   * Extracts values from a rooted json object by transforming a facet type
   * into a path to values.
   *
   * @param \RootedData\RootedJsonData $rooted
   *   RootedJson data object.
   * @param string $facetsType
   *   The facet type to transform to a path.
   *
   * @return array
   *   An array of values. It could be empty.
   */
  private function getPropertyValuesFromFacetsType(RootedJsonData $rooted, string $facetsType) {
    $property = str_replace('__', '.', $facetsType);
    $property = "$.{$property}";

    $values = $rooted->{$property};

    if (!isset($values)) {
      $values = [];
    }

    if (!is_array($values)) {
      $values = [$values];
    }

    return $values;
  }

  /**
   * Private.
   *
   * @param array $facetsTypes
   *   Relevant facet types.
   *
   * @return array
   *   An array with all facets for the given types represented.
   */
  private function getAllFacetsWithZeroCounts(array $facetsTypes) {
    $facets = [];
    foreach ($facetsTypes as $type) {

      [$schema, $field] = $this->getSchemaAndField($type);

      $allFacets = $this->metastoreService->getAll($schema);

      foreach ($allFacets as $facet) {

        $facet = isset($field) ? $facet->{"$.data." . $field} : $facet->{"$.data"};

        $facets["{$type}:{$facet}"] = (object) [
          'type' => $type,
          'name' => $facet,
          'total' => 0,
        ];
      }
    }

    return $facets;
  }

  /**
   * Private.
   *
   * @param string $facetsType
   *   The facet type.
   *
   * @return array
   *   [$schema, $field]
   */
  private function getSchemaAndField(string $facetsType): array {
    $field = NULL;
    if (preg_match('/(.*)__(.*)/', $facetsType, $matches)) {
      $schema = $matches[1];
      $field = $matches[2];
    }
    else {
      $schema = $facetsType;
    }
    return [$schema, $field];
  }

}
