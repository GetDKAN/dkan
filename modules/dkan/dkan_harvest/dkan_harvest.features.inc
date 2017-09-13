<?php

/**
 * @file
 * dkan_harvest.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function dkan_harvest_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "path_breadcrumbs" && $api == "path_breadcrumbs") {
    return array("version" => "1");
  }
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function dkan_harvest_views_api($module = NULL, $api = NULL) {
  return array("api" => "3.0");
}

/**
 * Implements hook_node_info().
 */
function dkan_harvest_node_info() {
  $items = array(
    'harvest_source' => array(
      'name' => t('Harvest Source'),
      'base' => 'node_content',
      'description' => t('Source for harvesting datasets from an external source into DKAN. For instance, another data portal’s data.json file could be set to harvest once a day and add all the datasets returned to your catalog.'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
  );
  drupal_alter('node_info', $items);
  return $items;
}
