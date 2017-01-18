<?php

/**
 * @file
 * dkan_harvest_dashboard.features.inc
 */

/**
 * Implements hook_views_api().
 */
function dkan_harvest_dashboard_views_api($module = NULL, $api = NULL) {
  return array(
    'api' => '3.0',
    'path' => drupal_get_path('module', 'dkan_harvest_dashboard'),
    'template path' => drupal_get_path('module', 'dkan_harvest_dashboard') . '/views/templates',
  );
}
