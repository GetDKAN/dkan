<?php
/**
 * @file
 * dkan_sitewide_user.features.field_instance.inc
 */

/**
 * Implements hook_field_default_field_instances().
 */
function dkan_sitewide_user_field_default_field_instances() {
  $field_instances = array();

  // Exported field_instance: 'user-user-field_about'.
  $field_instances['user-user-field_about'] = array(
    'bundle' => 'user',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'text',
        'settings' => array(),
        'type' => 'text_default',
        'weight' => 3,
      ),
    ),
    'entity_type' => 'user',
    'field_name' => 'field_about',
    'label' => 'About',
    'required' => 0,
    'settings' => array(
      'text_processing' => 0,
      'user_register_form' => 1,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'text',
      'settings' => array(
        'rows' => 3,
      ),
      'type' => 'text_textarea',
      'weight' => 2,
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('About');

  return $field_instances;
}
