<?php
/**
 * @file
 * dkan_sitewide_search_db.strongarm.inc
 */

/**
 * Implements hook_strongarm().
 */
function dkan_sitewide_search_db_strongarm() {
  $export = array();

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'facetapi_pretty_paths_searcher_search_api@datasets';
  $strongarm->value = 1;
  $export['facetapi_pretty_paths_searcher_search_api@datasets'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'facetapi_pretty_paths_searcher_search_api@datasets_options';
  $strongarm->value = array(
    'sort_path_segments' => 1,
  );
  $export['facetapi_pretty_paths_searcher_search_api@datasets_options'] = $strongarm;

  return $export;
}
