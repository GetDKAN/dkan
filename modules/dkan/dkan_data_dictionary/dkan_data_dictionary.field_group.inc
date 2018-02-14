<?php

/**
 * @file
 * dkan_data_dictionary.field_group.inc
 */

/**
 * Implements hook_field_group_info().
 */
function dkan_data_dictionary_field_group_info() {
  $field_groups = array();

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_describedby_schema_file|node|resource|form';
  $field_group->group_name = 'group_describedby_schema_file';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'resource';
  $field_group->mode = 'form';
  $field_group->parent_name = 'group_describedby_schema_source';
  $field_group->data = array(
    'label' => 'File',
    'weight' => '39',
    'children' => array(
      0 => 'field_describedby_file',
    ),
    'format_type' => 'htab',
    'format_settings' => array(
      'formatter' => 'closed',
      'instance_settings' => array(
        'description' => '',
        'classes' => '',
        'required_fields' => 1,
      ),
    ),
  );
  $field_groups['group_describedby_schema_file|node|resource|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_describedby_schema_json|node|resource|form';
  $field_group->group_name = 'group_describedby_schema_json';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'resource';
  $field_group->mode = 'form';
  $field_group->parent_name = 'group_describedby_schema_source';
  $field_group->data = array(
    'label' => 'JSON',
    'weight' => '40',
    'children' => array(
      0 => 'field_describedby_schema',
    ),
    'format_type' => 'htab',
    'format_settings' => array(
      'formatter' => 'closed',
      'instance_settings' => array(
        'description' => '',
        'classes' => 'group-conformsto-schema-json field-group-htab',
        'required_fields' => 1,
      ),
    ),
  );
  $field_groups['group_describedby_schema_json|node|resource|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_describedby_schema_source|node|resource|form';
  $field_group->group_name = 'group_describedby_schema_source';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'resource';
  $field_group->mode = 'form';
  $field_group->parent_name = 'group_describedby';
  $field_group->data = array(
    'label' => 'Schema Source',
    'weight' => '37',
    'children' => array(
      0 => 'group_describedby_schema_file',
      1 => 'group_describedby_schema_json',
    ),
    'format_type' => 'htabs',
    'format_settings' => array(
      'formatter' => '',
      'instance_settings' => array(
        'classes' => 'group-conformsto-schema-source field-group-htabs',
        'id' => '',
      ),
    ),
  );
  $field_groups['group_describedby_schema_source|node|resource|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_describedby|node|resource|form';
  $field_group->group_name = 'group_describedby';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'resource';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Data Dictionary',
    'weight' => '10',
    'children' => array(
      0 => 'field_describedby_spec',
      1 => 'group_describedby_schema_source',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'label' => 'Data Dictionary',
      'instance_settings' => array(
        'required_fields' => 1,
        'id' => '',
        'classes' => 'group-conformsto field-group-fieldset',
        'description' => 'A data dictionary describes your data at the field/column level. You can upload or link to a data dictionary in any format, or create your own in JSON using the <a href="https://frictionlessdata.io/specs/table-schema/">Table Schema</a> specification.',
      ),
      'formatter' => 'open',
    ),
  );
  $field_groups['group_describedby|node|resource|form'] = $field_group;

  // Translatables
  // Included for use with string extractors like potx.
  t('Data Dictionary');
  t('File');
  t('JSON');
  t('Schema Source');

  return $field_groups;
}
