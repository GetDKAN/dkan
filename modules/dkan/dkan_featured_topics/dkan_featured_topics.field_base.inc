<?php
/**
 * @file
 * dkan_featured_topics.field_base.inc
 */

/**
 * Implements hook_installed_fields().
 */
function _dkan_featured_topics_installed_fields() {
  $field_bases = array();

  // field_base: 'field_icon_type' - check if it exists.
  if(!field_info_field('field_icon_type') {
    $field_bases['field_icon_type'] = array(
      'active' => 1,
      'cardinality' => 1,
      'deleted' => 0,
      'entity_types' => array(),
      'field_name' => 'field_icon_type',
      'indexes' => array(
        'value' => array(
          0 => 'value',
        ),
      ),
      'locked' => 0,
      'module' => 'list',
      'settings' => array(
        'allowed_values' => array(
          'image' => 'Image',
          'font' => 'Font Icon',
        ),
        'allowed_values_function' => '',
      ),
      'translatable' => 0,
      'type' => 'list_text',
    );
  }

  // field_base: 'field_topic'
  if(!field_info_field('field_topic') {
    $field_bases['field_topic'] = array(
      'active' => 1,
      'cardinality' => -1,
      'deleted' => 0,
      'entity_types' => array(),
      'field_name' => 'field_topic',
      'indexes' => array(
        'tid' => array(
          0 => 'tid',
        ),
      ),
      'locked' => 0,
      'module' => 'taxonomy',
      'settings' => array(
        'allowed_values' => array(
          0 => array(
            'vocabulary' => 'dkan_topics',
            'parent' => 0,
          ),
        ),
      ),
      'translatable' => 0,
      'type' => 'taxonomy_term_reference',
    );
  }

  // field_base: 'field_topic_icon'
  if(!field_info_field('field_topic_icon') {
    $field_bases['field_topic_icon'] = array(
      'active' => 1,
      'cardinality' => 1,
      'deleted' => 0,
      'entity_types' => array(),
      'field_name' => 'field_topic_icon',
      'indexes' => array(),
      'locked' => 0,
      'module' => 'font_icon_select',
      'settings' => array(
        'font' => 1,
        'font_blacklist' => '',
        'max_length' => 255,
      ),
      'translatable' => 0,
      'type' => 'font_icon_select_icon',
    );
  }

  // field_base: 'field_topic_icon_color'
  if(!field_info_field('field_topic_icon_color') {
    $field_bases['field_topic_icon_color'] = array(
      'active' => 1,
      'cardinality' => 1,
      'deleted' => 0,
      'entity_types' => array(),
      'field_name' => 'field_topic_icon_color',
      'indexes' => array(
        'rgb' => array(
          0 => 'rgb',
        ),
      ),
      'locked' => 0,
      'module' => 'color_field',
      'settings' => array(),
      'translatable' => 0,
      'type' => 'color_field_rgb',
    );
  }

  return $field_bases;
}
