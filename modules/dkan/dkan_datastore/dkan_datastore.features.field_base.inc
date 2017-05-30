<?php

/**
 * @file
 * dkan_datastore.features.field_base.inc
 */

/**
 * Implements hook_field_default_field_bases().
 */
function dkan_datastore_field_default_field_bases() {
  $field_bases = array();

  // Exported field_base: 'field_datastore_status'.
  $field_bases['field_datastore_status'] = array(
    'active' => 1,
    'cardinality' => 1,
    'deleted' => 0,
    'entity_types' => array(),
    'field_name' => 'field_datastore_status',
    'indexes' => array(),
    'locked' => 0,
    'module' => 'field_hidden',
    'settings' => array(),
    'translatable' => 0,
    'type' => 'field_hidden_integer',
  );

  return $field_bases;
}
