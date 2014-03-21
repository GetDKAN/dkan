<?php
/**
 * @file
 * dkan_sitewide_roles_perms.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function dkan_sitewide_roles_perms_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}
