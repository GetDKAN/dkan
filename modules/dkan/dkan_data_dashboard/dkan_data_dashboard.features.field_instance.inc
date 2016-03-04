<?php
/**
 * @file
 * dkan_data_dashboard.features.field_instance.inc
 */

/**
 * Implements hook_field_default_field_instances().
 */
function dkan_data_dashboard_field_default_field_instances() {
  $field_instances = array();

  // Exported field_instance: 'node-data_dashboard-field_topic'
  $field_instances['node-data_dashboard-field_topic'] = array(
    'bundle' => 'data_dashboard',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'hidden',
        'module' => 'dkan_featured_topics',
        'settings' => array(),
        'type' => 'dkan_topic_formatter',
        'weight' => 0,
      ),
      'search_result' => array(
        'label' => 'hidden',
        'module' => 'dkan_featured_topics',
        'settings' => array(),
        'type' => 'dkan_topic_formatter',
        'weight' => 0,
      ),
      'teaser' => array(
        'label' => 'hidden',
        'module' => 'dkan_featured_topics',
        'settings' => array(),
        'type' => 'dkan_topic_formatter',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'field_topic',
    'label' => 'Topics',
    'required' => 0,
    'settings' => array(
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 1,
      'module' => 'options',
      'settings' => array(
        'apply_chosen' => '',
      ),
      'type' => 'options_select',
      'weight' => 1,
    ),
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('Topics');

  return $field_instances;
}
