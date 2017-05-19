<?php

/**
 * @file
 * open_data_federal_extras.field_group.inc
 */

/**
 * Implements hook_field_group_info().
 */
function open_data_federal_extras_field_group_info() {
  $field_groups = array();

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_odfie_pod|node|dataset|default';
  $field_group->group_name = 'group_odfie_pod';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'dataset';
  $field_group->mode = 'default';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'U.S. Federal Government Fields',
    'weight' => '5',
    'children' => array(
      0 => 'field_odfe_bureau_code',
      1 => 'field_odfe_program_code',
      2 => 'field_odfe_data_quality',
      3 => 'field_odfe_investment_uii',
      4 => 'field_odfe_system_of_records',
    ),
    'format_type' => 'table',
    'format_settings' => array(
      'label' => 'U.S. Federal Government Fields',
      'instance_settings' => array(
        'label' => 'hide',
        'desc' => 'These fields are compatible with <a href="https://project-open-data.cio.gov/">Project Open Data</a>, an Open Data policy of the US Federal government.',
        'first_column' => '',
        'second_column' => '',
        'cells' => 'empty',
      ),
    ),
  );
  $field_groups['group_odfie_pod|node|dataset|default'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_odfie_pod|node|dataset|form';
  $field_group->group_name = 'group_odfie_pod';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'dataset';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'U.S. Federal Government Fields',
    'weight' => '3',
    'children' => array(
      0 => 'field_odfe_bureau_code',
      1 => 'field_odfe_program_code',
      2 => 'field_odfe_data_quality',
      3 => 'field_odfe_investment_uii',
      4 => 'field_odfe_system_of_records',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'label' => 'U.S. Federal Government Fields',
      'instance_settings' => array(
        'required_fields' => 1,
        'classes' => 'group-odfie-pod field-group-fieldset',
        'description' => 'These are fields that are specific to <a href="https://project-open-data.cio.gov">Project Open Data</a>.',
      ),
      'formatter' => 'collapsible',
    ),
  );
  $field_groups['group_odfie_pod|node|dataset|form'] = $field_group;

  // Translatables
  // Included for use with string extractors like potx.
  t('U.S. Federal Government Fields');

  return $field_groups;
}
