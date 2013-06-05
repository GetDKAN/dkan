<?php

/**
 * @file
 * Date query type plugin for the Search API adapter.
 */

/**
 * Plugin for "date" query types.
 */
class SearchApiFacetapiDate extends SearchApiFacetapiTerm implements FacetapiQueryTypeInterface {

  /**
   * Loads the include file containing the date API functions.
   */
  public function __construct(FacetapiAdapter $adapter, array $facet) {
    module_load_include('date.inc', 'facetapi');
    parent::__construct($adapter, $facet);
  }

  /**
   * Returns the query type associated with the plugin.
   *
   * @return string
   *   The query type.
   */
  static public function getType() {
    return 'date';
  }

  /**
   * Adds the filter to the query object.
   *
   * @param $query
   *   An object containing the query in the backend's native API.
   */
  public function execute($query) {
    // Return terms for this facet.
    $this->adapter->addFacet($this->facet, $query);
    // Change limit to "unlimited" (-1).
    $options = &$query->getOptions();
    if (!empty($options['search_api_facets'][$this->facet['name']])) {
      $options['search_api_facets'][$this->facet['name']]['limit'] = -1;
    }

    if ($active = $this->adapter->getActiveItems($this->facet)) {
      $item = end($active);
      $field = $this->facet['field'];
      $regex = str_replace(array('^', '$'), '', FACETAPI_REGEX_DATE);
      $filter = preg_replace_callback($regex, array($this, 'replaceDateString'), $item['value']);
      $this->addFacetFilter($query, $field, $filter);
    }
  }

  /**
   * Replacement callback for replacing ISO dates with timestamps.
   */
  public function replaceDateString($matches) {
    return strtotime($matches[0]);
  }

  /**
   * Initializes the facet's build array.
   *
   * @return array
   *   The initialized render array.
   */
  public function build() {
    $facet = $this->adapter->getFacet($this->facet);
    $search_ids = drupal_static('search_api_facetapi_active_facets', array());
    if (empty($search_ids[$facet['name']]) || !search_api_current_search($search_ids[$facet['name']])) {
      return array();
    }
    $search_id = $search_ids[$facet['name']];
    $build = array();
    $search = search_api_current_search($search_id);
    $results = $search[1];
    if (!$results['result count']) {
      return array();
    }
    // Gets total number of documents matched in search.
    $total = $results['result count'];

    // Most of the code below is copied from search_facetapi's implementation of
    // this method.

    // Executes query, iterates over results.
    if (isset($results['search_api_facets']) && isset($results['search_api_facets'][$this->facet['field']])) {
      $values = $results['search_api_facets'][$this->facet['field']];
      foreach ($values as $value) {
        if ($value['count']) {
          $filter = $value['filter'];
          // We only process single values further. The "missing" filter and
          // range filters will be passed on unchanged.
          if ($filter == '!') {
            $build[$filter]['#count'] = $value['count'];
          }
          elseif ($filter[0] == '"') {
            $filter = substr($value['filter'], 1, -1);
            if ($filter) {
              $raw_values[$filter] = $value['count'];
            }
          }
          else {
            $filter = substr($value['filter'], 1, -1);
            $pos = strpos($filter, ' ');
            if ($pos !== FALSE) {
              $lower = facetapi_isodate(substr($filter, 0, $pos), FACETAPI_DATE_DAY);
              $upper = facetapi_isodate(substr($filter, $pos + 1), FACETAPI_DATE_DAY);
              $filter = '[' . $lower . ' TO ' . $upper . ']';
            }
            $build[$filter]['#count'] = $value['count'];
          }
        }
      }
    }

    // Get the finest level of detail we're allowed to drill down to.
    $settings = $facet->getSettings()->settings;
    $granularity = isset($settings['date_granularity']) ? $settings['date_granularity'] : FACETAPI_DATE_MINUTE;

    // Gets active facets, starts building hierarchy.
    $parent = $gap = NULL;
    foreach ($this->adapter->getActiveItems($this->facet) as $value => $item) {
      // If the item is active, the count is the result set count.
      $build[$value] = array('#count' => $total);

      // Gets next "gap" increment.
      if ($value[0] != '[' || $value[strlen($value) - 1] != ']' || !($pos = strpos($value, ' TO '))) {
        continue;
      }
      $start = substr($value, 1, $pos);
      $end = substr($value, $pos + 4, -1);
      $date_gap = facetapi_get_date_gap($start, $end);
      $gap = facetapi_get_next_date_gap($date_gap, $granularity);

      // If there is a previous item, there is a parent, uses a reference so the
      // arrays are populated when they are updated.
      if (NULL !== $parent) {
        $build[$parent]['#item_children'][$value] = &$build[$value];
        $build[$value]['#item_parents'][$parent] = $parent;
      }

      // Stores the last value iterated over.
      $parent = $value;
    }
    if (empty($raw_values)) {
      return $build;
    }
    ksort($raw_values);

    // Mind the gap! Calculates gap from min and max timestamps.
    $timestamps = array_keys($raw_values);
    if (NULL === $parent) {
      if (count($raw_values) > 1) {
        $gap = facetapi_get_timestamp_gap(min($timestamps), max($timestamps));
        // Array of numbers used to determine whether the next gap is smaller than
        // the minimum gap allowed in the drilldown.
        $gap_numbers = array(
          FACETAPI_DATE_YEAR => 6,
          FACETAPI_DATE_MONTH => 5,
          FACETAPI_DATE_DAY => 4,
          FACETAPI_DATE_HOUR => 3,
          FACETAPI_DATE_MINUTE => 2,
          FACETAPI_DATE_SECOND => 1,
        );
        // Gets gap numbers for both the gap and minimum gap, checks if the gap
        // is within the limit set by the $granularity parameter.
        if ($gap_numbers[$gap] < $gap_numbers[$granularity]) {
          $gap = $granularity;
        }
      }
      else {
        $gap = $granularity;
      }
    }

    // Converts all timestamps to dates in ISO 8601 format.
    $dates = array();
    foreach ($timestamps as $timestamp) {
      $dates[$timestamp] = facetapi_isodate($timestamp, $gap);
    }

    // Treat each date as the range start and next date as the range end.
    $range_end = array();
    $previous = NULL;
    foreach (array_unique($dates) as $date) {
      if (NULL !== $previous) {
        $range_end[$previous] = facetapi_get_next_date_increment($previous, $gap);
      }
      $previous = $date;
    }
    $range_end[$previous] = facetapi_get_next_date_increment($previous, $gap);

    // Groups dates by the range they belong to, builds the $build array
    // with the facet counts and formatted range values.
    foreach ($raw_values as $value => $count) {
      $new_value = '[' . $dates[$value] . ' TO ' . $range_end[$dates[$value]] . ']';
      if (!isset($build[$new_value])) {
        $build[$new_value] = array('#count' => $count);
      }
      else {
        $build[$new_value]['#count'] += $count;
      }

      // Adds parent information if not already set.
      if (NULL !== $parent && $parent != $new_value) {
        $build[$parent]['#item_children'][$new_value] = &$build[$new_value];
        $build[$new_value]['#item_parents'][$parent] = $parent;
      }
    }

    return $build;
  }
}
