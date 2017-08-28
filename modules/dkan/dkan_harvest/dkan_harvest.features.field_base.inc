<?php

/**
 * @file
 * dkan_harvest.features.field_base.inc
 */

/**
 * Implements hook_field_default_field_bases().
 */
function dkan_harvest_field_default_field_bases() {
  $field_bases = array();

  // Exported field_base: 'field_dkan_harveset_type'.
  $field_bases['field_dkan_harveset_type'] = array(
    'active' => 1,
    'cardinality' => 1,
    'deleted' => 0,
    'entity_types' => array(),
    'field_name' => 'field_dkan_harveset_type',
    'indexes' => array(
      'value' => array(
        0 => 'value',
      ),
    ),
    'locked' => 0,
    'module' => 'list',
    'settings' => array(
      'allowed_values' => '',
      'allowed_values_function' => 'dkan_harvest_field_dkan_harveset_type_allowed_values',
    ),
    'translatable' => 0,
    'type' => 'list_text',
  );

  // Exported field_base: 'field_dkan_harvest_defaults'.
  $field_bases['field_dkan_harvest_defaults'] = array(
    'active' => 1,
    'cardinality' => -1,
    'deleted' => 0,
    'entity_types' => array(),
    'field_name' => 'field_dkan_harvest_defaults',
    'indexes' => array(),
    'locked' => 0,
    'module' => 'double_field',
    'settings' => array(
      'first' => array(
        'maxlength' => 255,
        'precision' => 10,
        'scale' => 2,
        'size' => 'normal',
        'type' => 'varchar',
      ),
      'second' => array(
        'maxlength' => 255,
        'precision' => 10,
        'scale' => 2,
        'size' => 'normal',
        'type' => 'varchar',
      ),
    ),
    'translatable' => 0,
    'type' => 'double_field',
  );

  // Exported field_base: 'field_dkan_harvest_excludes'.
  $field_bases['field_dkan_harvest_excludes'] = array(
    'active' => 1,
    'cardinality' => -1,
    'deleted' => 0,
    'entity_types' => array(),
    'field_name' => 'field_dkan_harvest_excludes',
    'indexes' => array(),
    'locked' => 0,
    'module' => 'double_field',
    'settings' => array(
      'first' => array(
        'maxlength' => 255,
        'precision' => 10,
        'scale' => 2,
        'size' => 'normal',
        'type' => 'varchar',
      ),
      'second' => array(
        'maxlength' => 255,
        'precision' => 10,
        'scale' => 2,
        'size' => 'normal',
        'type' => 'varchar',
      ),
    ),
    'translatable' => 0,
    'type' => 'double_field',
  );

  // Exported field_base: 'field_dkan_harvest_filters'.
  $field_bases['field_dkan_harvest_filters'] = array(
    'active' => 1,
    'cardinality' => -1,
    'deleted' => 0,
    'entity_types' => array(),
    'field_name' => 'field_dkan_harvest_filters',
    'indexes' => array(),
    'locked' => 0,
    'module' => 'double_field',
    'settings' => array(
      'first' => array(
        'maxlength' => 255,
        'precision' => 10,
        'scale' => 2,
        'size' => 'normal',
        'type' => 'varchar',
      ),
      'second' => array(
        'maxlength' => 255,
        'precision' => 10,
        'scale' => 2,
        'size' => 'normal',
        'type' => 'varchar',
      ),
    ),
    'translatable' => 0,
    'type' => 'double_field',
  );

  // Exported field_base: 'field_dkan_harvest_machine_name'.
  $field_bases['field_dkan_harvest_machine_name'] = array(
    'active' => 1,
    'cardinality' => 1,
    'deleted' => 0,
    'entity_types' => array(),
    'field_name' => 'field_dkan_harvest_machine_name',
    'field_permissions' => array(
      'type' => 0,
    ),
    'indexes' => array(
      'machine' => array(
        0 => 'machine',
      ),
    ),
    'locked' => 0,
    'module' => 'safeword',
    'settings' => array(
      'allow_machine_changes' => 0,
      'complete_path_label' => 'Complete path',
      'machine_description' => 'A URL-safe version of the text. It may only contain lowercase letters, numbers and underscores. Leave blank to re-generate.',
      'machine_label' => 'Machine Name',
      'max_length' => 255,
      'replace_pattern' => '(--|<[^<>]+>|[^/a-z0-9-_]|/)+',
      'replace_value' => '_',
      'show_complete_path' => 0,
      'unique' => 1,
    ),
    'translatable' => 0,
    'type' => 'safeword_title',
  );

  // Exported field_base: 'field_dkan_harvest_overrides'.
  $field_bases['field_dkan_harvest_overrides'] = array(
    'active' => 1,
    'cardinality' => -1,
    'deleted' => 0,
    'entity_types' => array(),
    'field_name' => 'field_dkan_harvest_overrides',
    'indexes' => array(),
    'locked' => 0,
    'module' => 'double_field',
    'settings' => array(
      'first' => array(
        'maxlength' => 255,
        'precision' => 10,
        'scale' => 2,
        'size' => 'normal',
        'type' => 'varchar',
      ),
      'second' => array(
        'maxlength' => 255,
        'precision' => 10,
        'scale' => 2,
        'size' => 'normal',
        'type' => 'varchar',
      ),
    ),
    'translatable' => 0,
    'type' => 'double_field',
  );

  // Exported field_base: 'field_dkan_harvest_source_uri'.
  $field_bases['field_dkan_harvest_source_uri'] = array(
    'active' => 1,
    'cardinality' => 1,
    'deleted' => 0,
    'entity_types' => array(),
    'field_name' => 'field_dkan_harvest_source_uri',
    'field_permissions' => array(
      'type' => 0,
    ),
    'indexes' => array(
      'format' => array(
        0 => 'format',
      ),
    ),
    'locked' => 0,
    'module' => 'text',
    'settings' => array(
      'max_length' => 255,
    ),
    'translatable' => 0,
    'type' => 'text',
  );

  // Exported field_base: 'field_dkan_harvest_topic'.
  $field_bases['field_dkan_harvest_topic'] = array(
    'active' => 1,
    'cardinality' => -1,
    'deleted' => 0,
    'entity_types' => array(),
    'field_name' => 'field_dkan_harvest_topic',
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

  // Exported field_base: 'field_orphan'.
  $field_bases['field_orphan'] = array(
    'active' => 1,
    'cardinality' => 1,
    'deleted' => 0,
    'entity_types' => array(),
    'field_name' => 'field_orphan',
    'indexes' => array(
      'value' => array(
        0 => 'value',
      ),
    ),
    'locked' => 0,
    'module' => 'list',
    'settings' => array(
      'allowed_values' => array(
        0 => '',
        1 => '',
      ),
      'allowed_values_function' => '',
    ),
    'translatable' => 0,
    'type' => 'list_boolean',
  );

  return $field_bases;
}
