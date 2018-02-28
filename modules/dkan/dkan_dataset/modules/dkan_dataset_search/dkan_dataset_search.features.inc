<?php

/**
 * @file
 * dkan_dataset_search.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function dkan_dataset_search_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "facetapi" && $api == "facetapi_defaults") {
    return array("version" => "1");
  }
  if ($module == "page_manager" && $api == "pages_default") {
    return array("version" => "1");
  }
  if ($module == "path_breadcrumbs" && $api == "path_breadcrumbs") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function dkan_dataset_search_views_api($module = NULL, $api = NULL) {
  return array("api" => "3.0");
}
