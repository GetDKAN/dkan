<?php
/**
 * @file
 * dkan_front.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function dkan_front_ctools_plugin_api() {
  list($module, $api) = func_get_args();
  if ($module == "context" && $api == "context") {
    return array("version" => "3");
  }
  list($module, $api) = func_get_args();
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function dkan_front_views_api() {
  return array("version" => "3.0");
}
