<?php

/**
 * @file
 * dkan_topics.features.taxonomy.inc
 */

/**
 * Implements hook_taxonomy_default_vocabularies().
 */
function dkan_topics_taxonomy_default_vocabularies() {
  return array(
    'dkan_topics' => array(
      'name' => 'Topics',
      'machine_name' => 'dkan_topics',
      'description' => '',
      'hierarchy' => 0,
      'module' => 'taxonomy',
      'weight' => 0,
    ),
  );
}
