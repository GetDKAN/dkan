<?php

/**
 * @file
 * dkan_data_dashboard.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function dkan_data_dashboard_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "panelizer" && $api == "panelizer") {
    return array("version" => "1");
  }
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function dkan_data_dashboard_views_api($module = NULL, $api = NULL) {
  return array("api" => "3.0");
}

/**
 * Implements hook_node_info().
 */
function dkan_data_dashboard_node_info() {
  $items = array(
    'data_dashboard' => array(
      'name' => t('Data Dashboard'),
      'base' => 'node_content',
      'description' => t('Content type for creation of Data Dashboards.'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
  );
  drupal_alter('node_info', $items);
  return $items;
}
