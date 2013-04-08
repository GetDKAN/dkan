<?php

/**
 * @file
 * The standard url processor class.
 */

/**
 * Url processor plugin that retrieves facet data from the query string.
 *
 * This plugin retrieves facet data from $_GET, and stored all information in
 * the "f" query string variable by default.
 */
class FacetapiUrlProcessorStandard extends FacetapiUrlProcessor {

  /**
   * Implements FacetapiUrlProcessor::fetchParams().
   *
   * Use $_GET as the source for facet data.
   */
  public function fetchParams() {
    return $_GET;
  }

  /**
   * Implements FacetapiUrlProcessor::normalizeParams().
   *
   * Strips the "q" and "page" variables from the params array.
   */
  public function normalizeParams(array $params, $filter_key = 'f') {
    return drupal_get_query_parameters($params, array('q', 'page'));
  }

  /**
   * Implements FacetapiUrlProcessor::getQueryString().
   */
  public function getQueryString(array $facet, array $values, $active) {
    $qstring = $this->params;
    $active_items = $this->adapter->getActiveItems($facet);

    // Appends to qstring if inactive, removes if active.
    foreach ($values as $value) {
      if ($active && isset($active_items[$value])) {
        unset($qstring[$this->filterKey][$active_items[$value]['pos']]);
      }
      elseif (!$active) {
        $field_alias = rawurlencode($facet['field alias']);
        $qstring[$this->filterKey][] = $field_alias . ':' . $value;
      }
    }

    // Removes duplicates, resets array keys and returns query string.
    // @see http://drupal.org/node/1340528
    $qstring[$this->filterKey] = array_values(array_unique($qstring[$this->filterKey]));
    return array_filter($qstring);
  }

  /**
   * Implements FacetapiUrlProcessor::setBreadcrumb().
   */
  public function setBreadcrumb() {
    $breadcrumb = drupal_get_breadcrumb();

    // Gets search keys and active items form the adapter.
    $keys = $this->adapter->getSearchKeys();
    $active_items = $this->adapter->getAllActiveItems();

    $item = menu_get_item();

    // Initializes base breadcrumb query.
    $query = $this->params;
    unset($query[$this->filterKey]);

    // Adds the current search to the query.
    if ($keys) {
      // The last item should be text, not a link.
      $breadcrumb[] = $active_items ? l($keys, current_path(), array('query' => $query)) : check_plain($keys);
    }

    // Adds filters to the breadcrumb trail.
    $last = end($active_items);
    foreach ($active_items as $item) {
      $query[$this->filterKey][] = rawurlencode($item['field alias']) . ':' . $item['value'];

      // Replaces with the mapped value.
      $value = $this->adapter->getMappedValue($item['facets'][0], $item['value']);

      // The last item should be text, not a link.
      if ($last == $item) {
        $breadcrumb[] = !empty($value['#html']) ? $value['#markup'] : check_plain($value['#markup']);
      }
      else {
        // Appends the filter to the breadcrumb trail.
        $breadcrumb[] = l($value['#markup'], current_path(), array('query' => $query, 'html' => !empty($value['#html'])));
      }
    }

    // Sets the breadcrumb trail with the keys and filters.
    drupal_set_breadcrumb($breadcrumb);
  }
}
