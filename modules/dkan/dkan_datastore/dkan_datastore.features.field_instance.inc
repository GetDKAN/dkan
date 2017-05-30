<?php

/**
 * @file
 * dkan_datastore.features.field_instance.inc
 */

/**
 * Implements hook_field_default_field_instances().
 */
function dkan_datastore_field_default_field_instances() {
  $field_instances = array();

  // Exported field_instance: 'node-resource-field_datastore_status'.
  $field_instances['node-resource-field_datastore_status'] = array(
    'bundle' => 'resource',
    'default_value' => array(
      0 => array(
        'value' => 0,
      ),
    ),
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 7,
      ),
      'search_result' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'field_datastore_status',
    'label' => 'Datastore Status',
    'required' => 0,
    'settings' => array(
      'max' => 3,
      'min' => 0,
      'prefix' => '',
      'suffix' => '',
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'field_hidden',
      'settings' => array(),
      'type' => 'field_hidden',
      'weight' => 31,
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('Datastore Status');

  return $field_instances;
}
