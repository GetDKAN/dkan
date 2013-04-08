<?php

/**
 * @file
 * Term query type plugin for the Apache Solr adapter.
 */

/**
 * Plugin for "term" query types.
 */
class SearchApiFacetapiTerm extends FacetapiQueryType implements FacetapiQueryTypeInterface {

  /**
   * Returns the query type associated with the plugin.
   *
   * @return string
   *   The query type.
   */
  static public function getType() {
    return 'term';
  }

  /**
   * Adds the filter to the query object.
   *
   * @param SearchApiQueryInterface $query
   *   An object containing the query in the backend's native API.
   */
  public function execute($query) {
    // Return terms for this facet.
    $this->adapter->addFacet($this->facet, $query);

    $settings = $this->adapter->getFacet($this->facet)->getSettings();
    // Adds the operator parameter.
    $operator = $settings->settings['operator'];

    // Add active facet filters.
    $active = $this->adapter->getActiveItems($this->facet);
    if (empty($active)) {
      return;
    }

    if (FACETAPI_OPERATOR_OR == $operator) {
      // If we're dealing with an OR facet, we need to use a nested filter.
      $facet_filter = $query->createFilter('OR');
    }
    else {
      // Otherwise we set the conditions directly on the query.
      $facet_filter = $query;
    }

    foreach ($active as $filter => $filter_array) {
      $field = $this->facet['field'];
      $this->addFacetFilter($facet_filter, $field, $filter);
    }

    // For OR facets, we now have to add the filter to the query.
    if (FACETAPI_OPERATOR_OR == $operator) {
      $query->filter($facet_filter);
    }
  }

  /**
   * Helper method for setting a facet filter on a query or query filter object.
   */
  protected function addFacetFilter($query_filter, $field, $filter) {
    // Integer (or other nun-string) filters might mess up some of the following
    // comparison expressions.
    $filter = (string) $filter;
    if ($filter == '!') {
      $query_filter->condition($field, NULL);
    }
    elseif ($filter[0] == '[' && $filter[strlen($filter) - 1] == ']' && ($pos = strpos($filter, ' TO '))) {
      $lower = trim(substr($filter, 1, $pos));
      $upper = trim(substr($filter, $pos + 4, -1));
      if ($lower == '*' && $upper == '*') {
        $query_filter->condition($field, NULL, '<>');
      }
      else {
        if ($lower != '*') {
          // Iff we have a range with two finite boundaries, we set two
          // conditions (larger than the lower bound and less than the upper
          // bound) and therefore have to make sure that we have an AND
          // conjunction for those.
          if ($upper != '*' && !($query_filter instanceof SearchApiQueryInterface || $query_filter->getConjunction() === 'AND')) {
            $original_query_filter = $query_filter;
            $query_filter = new SearchApiQueryFilter('AND');
          }
          $query_filter->condition($field, $lower, '>=');
        }
        if ($upper != '*') {
          $query_filter->condition($field, $upper, '<=');
        }
      }
    }
    else {
      $query_filter->condition($field, $filter);
    }
    if (isset($original_query_filter)) {
      $original_query_filter->filter($query_filter);
    }
  }

  /**
   * Initializes the facet's build array.
   *
   * @return array
   *   The initialized render array.
   */
  public function build() {
    $facet = $this->adapter->getFacet($this->facet);
    // The current search per facet is stored in a static variable (during
    // initActiveFilters) so that we can retrieve it here and get the correct
    // current search for this facet.
    $search_ids = drupal_static('search_api_facetapi_active_facets', array());
    if (empty($search_ids[$facet['name']]) || !search_api_current_search($search_ids[$facet['name']])) {
      return array();
    }
    $search_id = $search_ids[$facet['name']];
    $search = search_api_current_search($search_id);
    $build = array();
    $results = $search[1];
    if (isset($results['search_api_facets']) && isset($results['search_api_facets'][$this->facet['field']])) {
      $values = $results['search_api_facets'][$this->facet['field']];
      foreach ($values as $value) {
        $filter = $value['filter'];
        // As Facet API isn't really suited for our native facet filter
        // representations, convert the format here. (The missing facet can
        // stay the same.)
        if ($filter[0] == '"') {
          $filter = substr($filter, 1, -1);
        }
        elseif ($filter != '!') {
          // This is a range filter.
          $filter = substr($filter, 1, -1);
          $pos = strpos($filter, ' ');
          if ($pos !== FALSE) {
            $filter = '[' . substr($filter, 0, $pos) . ' TO ' . substr($filter, $pos + 1) . ']';
          }
        }
        $build[$filter] = array(
          '#count' => $value['count'],
        );
      }
    }
    return $build;
  }

}
