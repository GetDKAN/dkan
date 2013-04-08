<?php
/**
 * @file
 * uuid_services_example.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function uuid_services_example_ctools_plugin_api() {
  list($module, $api) = func_get_args();
  if ($module == "services" && $api == "services") {
    return array("version" => "3");
  }
}
