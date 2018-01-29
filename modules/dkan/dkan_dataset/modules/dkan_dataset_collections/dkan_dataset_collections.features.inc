<?php
/**
 * @file
 * dkan_dataset_collections.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function dkan_dataset_collections_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "facetapi" && $api == "facetapi_defaults") {
    return array("version" => "1");
  }
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function dkan_dataset_collections_views_api($module = NULL, $api = NULL) {
  return array("api" => "3.0");
}
