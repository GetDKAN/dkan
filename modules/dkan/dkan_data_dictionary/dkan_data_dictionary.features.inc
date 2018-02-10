<?php

/**
 * @file
 * dkan_data_dictionary.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function dkan_data_dictionary_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "field_group" && $api == "field_group") {
    return array("version" => "1");
  }
}
