<?php
/**
 * @file
 * dkan_sitewide_context.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function dkan_sitewide_context_ctools_plugin_api() {
  list($module, $api) = func_get_args();
  if ($module == "context" && $api == "context") {
    return array("version" => "3");
  }
  list($module, $api) = func_get_args();
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}
