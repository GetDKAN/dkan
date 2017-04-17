<?php
/**
 * @file
 * dkan_acquia_expire.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function dkan_acquia_expire_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}
