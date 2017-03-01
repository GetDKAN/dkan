<?php

/**
 * @file
 * dkan_dataset_content_types.features.taxonomy.inc
 */

/**
 * Implements hook_taxonomy_default_vocabularies().
 */
function dkan_dataset_content_types_taxonomy_default_vocabularies() {
  return array(
    'format' => array(
      'name' => 'Format',
      'machine_name' => 'format',
      'description' => 'This is generated automatically, you can edit it if you wish',
      'hierarchy' => 0,
      'module' => 'taxonomy',
      'weight' => 0,
    ),
    'tags' => array(
      'name' => 'Tags',
      'machine_name' => 'tags',
      'description' => 'eg. economy, mental health, government',
      'hierarchy' => 0,
      'module' => 'taxonomy',
      'weight' => 0,
    ),
  );
}
