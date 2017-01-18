<?php

/**
 * @file
 * dkan_dataset_voting.features.field_base.inc
 */

/**
 * Implements hook_field_default_field_bases().
 */
function dkan_dataset_voting_field_default_field_bases() {
  $field_bases = array();

  // Exported field_base: 'field_rating'.
  $field_bases['field_rating'] = array(
    'active' => 1,
    'cardinality' => 1,
    'deleted' => 0,
    'entity_types' => array(),
    'field_name' => 'field_rating',
    'foreign keys' => array(),
    'indexes' => array(),
    'locked' => 0,
    'module' => 'fivestar',
    'settings' => array(
      'axis' => 'vote',
    ),
    'translatable' => 0,
    'type' => 'fivestar',
  );

  return $field_bases;
}
