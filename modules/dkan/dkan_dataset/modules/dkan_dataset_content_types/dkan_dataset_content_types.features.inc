<?php

/**
 * @file
 * dkan_dataset_content_types.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function dkan_dataset_content_types_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "field_group" && $api == "field_group") {
    return array("version" => "1");
  }
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_node_info().
 */
function dkan_dataset_content_types_node_info() {
  $items = array(
    'dataset' => array(
      'name' => t('Dataset'),
      'base' => 'node_content',
      'description' => t('Datasets are simply used to group related pieces of data. These can then be found under a single url with a description and licensing information.'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
    'resource' => array(
      'name' => t('Resource'),
      'base' => 'node_content',
      'description' => t('Data can be any file or link to a file containing useful data.'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
  );
  drupal_alter('node_info', $items);
  return $items;
}
