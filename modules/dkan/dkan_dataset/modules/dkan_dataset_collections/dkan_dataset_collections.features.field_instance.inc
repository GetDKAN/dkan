<?php

/**
 * @file
 * dkan_dataset_collections.features.field_instance.inc
 */

/**
 * Implements hook_field_default_field_instances().
 */
function dkan_dataset_collections_field_default_field_instances() {
  $field_instances = array();

  // Exported field_instance: 'node-dataset-field_dkan_ispartof_ref'.
  $field_instances['node-dataset-field_dkan_ispartof_ref'] = array(
    'bundle' => 'dataset',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => 'Please see: <a href="https://project-open-data.cio.gov/v1.1/schema/#isPartOf" target="_blank">https://project-open-data.cio.gov</a>',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'entityreference',
        'settings' => array(
          'bypass_access' => 0,
          'link' => 1,
        ),
        'type' => 'entityreference_label',
        'weight' => 6,
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
    'field_name' => 'field_dkan_ispartof_ref',
    'label' => 'Collection',
    'required' => 0,
    'settings' => array(
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'entityreference',
      'settings' => array(
        'match_operator' => 'CONTAINS',
        'path' => '',
        'size' => 60,
      ),
      'type' => 'entityreference_autocomplete',
      'weight' => 10,
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('Collection');
  t('Please see: <a href="https://project-open-data.cio.gov/v1.1/schema/#isPartOf" target="_blank">https://project-open-data.cio.gov</a>');

  return $field_instances;
}
