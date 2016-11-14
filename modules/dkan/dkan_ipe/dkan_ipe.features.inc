<?php

/**
 * @file
 * dkan_ipe.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function dkan_ipe_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}
