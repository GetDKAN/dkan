<?php

namespace Drupal\metastore_search;

/**
 * Trait for shared search api facet functionality.
 *
 * @package Drupal\metastore_search
 */
trait FacetsCommonTrait {

  /**
   * Private.
   *
   * @param string $string
   *   Comma-separated string.
   *
   * @return array
   *   Values.
   */
  private function getValuesFromCommaSeparatedString(string $string): array {
    return array_map('trim', str_getcsv($string));
  }

  /**
   * Private.
   *
   * @param array $params
   *   An array with parameters for a search_api query.
   *
   * @return string[]
   *   The relevant facet types.
   *
   * @todo Stop hard-coding the approved facet types; get from index.
   */
  private function getFacetsTypes(array $params): array {
    $facetsTypes = [];
    $approvedFacetsTypes = ['theme', 'keyword', 'publisher__name'];

    if (isset($params['facets']) && is_string($params['facets'])) {
      $facetsTypes = $this->getValuesFromCommaSeparatedString($params['facets']);
    }

    // If no specific facet types were requested, returned all approved facet
    // types.
    if (empty($facetsTypes)) {
      return $approvedFacetsTypes;
    }

    // Make sure that facet types given are part of the approved list.
    return array_filter($facetsTypes, function ($facetsType) use ($approvedFacetsTypes) {
      return in_array($facetsType, $approvedFacetsTypes);
    });
  }

}
