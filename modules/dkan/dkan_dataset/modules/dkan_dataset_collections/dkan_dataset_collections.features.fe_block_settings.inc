<?php
/**
 * @file
 * dkan_dataset_collections.features.fe_block_settings.inc
 */

/**
 * Implements hook_default_fe_block_settings().
 */
function dkan_dataset_collections_default_fe_block_settings() {
  $export = array();

  $export['version'] = '2.0';

  $export['views-collection_parent-block'] = array(
    'cache' => -1,
    'css_class' => '',
    'custom' => 0,
    'delta' => 'collection_parent-block',
    'module' => 'views',
    'node_types' => array(),
    'pages' => '',
    'roles' => array(),
    'themes' => array(
      'nuboot_radix' => array(
        'region' => '',
        'status' => 0,
        'theme' => 'nuboot_radix',
        'weight' => 0,
      ),
      'usda_radix_theme' => array(
        'region' => '',
        'status' => 0,
        'theme' => 'usda_radix_theme',
        'weight' => 0,
      ),
    ),
    'title' => '',
    'visibility' => 0,
  );

  return $export;
}
