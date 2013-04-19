<?php
/**
 * @file
 * dkan_sitewide_user.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function dkan_sitewide_user_ctools_plugin_api() {
  list($module, $api) = func_get_args();
  if ($module == "context" && $api == "context") {
    return array("version" => "3");
  }
  list($module, $api) = func_get_args();
  if ($module == "field_group" && $api == "field_group") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function dkan_sitewide_user_views_api() {
  return array("api" => "3.0");
}
