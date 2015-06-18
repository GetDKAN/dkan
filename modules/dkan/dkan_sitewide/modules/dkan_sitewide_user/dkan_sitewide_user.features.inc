<?php
/**
 * @file
 * dkan_sitewide_user.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function dkan_sitewide_user_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "field_group" && $api == "field_group") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function dkan_sitewide_user_views_api($module = NULL, $api = NULL) {
  return array("api" => "3.0");
}
