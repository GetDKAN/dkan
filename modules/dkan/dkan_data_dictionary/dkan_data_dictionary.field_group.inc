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
  $field_group->identifier = 'group_conformsto|node|resource|form';
  $field_group->group_name = 'group_conformsto';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'resource';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Data Dictionary',
    'weight' => '35',
    'children' => array(
      0 => 'field_conformsto_spec',
      1 => 'field_conformsto_schema',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'formatter' => 'collapsible',
      'instance_settings' => array(
        'description' => '',
        'classes' => 'group-conformsto field-group-fieldset',
        'required_fields' => 1,
        'id' => '',
      ),
    ),
  );
  $field_groups['group_conformsto|node|resource|form'] = $field_group;

  // Translatables
  // Included for use with string extractors like potx.
  t('Data Dictionary');

  return $field_groups;
}
