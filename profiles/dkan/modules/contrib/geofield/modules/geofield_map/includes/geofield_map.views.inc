<?php

/**
 * @file
 *   Default views hooks.
 */

/**
 * Implements hook_views_plugins().
 */

function geofield_map_views_plugins() {
  $plugins = array(
    'module' => 'geofield_map',
    'style' => array(
      'geofield_map_map' => array(
        'title' => t('Geofield Map'),
        'help' => t('Displays a View as an Geofield map.'),
        'handler' => 'geofield_map_plugin_style_map',
        'theme' => 'geofield_map_map',
        'theme path' => drupal_get_path('module', 'geofield_map') . '/includes',
        'path' => drupal_get_path('module', 'geofield_map') . '/includes',
        'uses fields' => TRUE,
        'uses row plugin' => FALSE,
        'uses options' => TRUE,
        'uses grouping' => FALSE,
        'type' => 'normal',
        'even empty' => TRUE,
      ),
    ),
  );
  
  return $plugins;
}
