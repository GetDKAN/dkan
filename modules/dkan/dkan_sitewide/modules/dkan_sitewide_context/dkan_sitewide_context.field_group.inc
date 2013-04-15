<?php
/**
 * @file
 * dkan_sitewide_context.field_group.inc
 */

/**
 * Implements hook_field_group_info().
 */
function dkan_sitewide_context_field_group_info() {
  $export = array();

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_additional|node|dataset|form';
  $field_group->group_name = 'group_additional';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'dataset';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Dataset Information',
    'weight' => '5',
    'children' => array(
      0 => 'field_author',
      1 => 'field_data_dictionary',
      2 => 'field_frequency',
      3 => 'field_granularity',
      4 => 'field_spatial',
      5 => 'field_spatial_geographical_cover',
      6 => 'field_temporal_coverage',
      7 => 'field_additional_info',
      8 => 'field_resources',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'label' => 'Dataset Information',
      'instance_settings' => array(
        'required_fields' => 1,
        'classes' => ' group-additional field-group-fieldset',
        'description' => '<i class="icon-large icon-info-sign"></i>These fields are compatible with <a href="http://www.w3.org/TR/vocab-dcat/">DCAT</a>, an RDF vocabulary designed to facilitate interoperability between data catalogs published on the Web.',
      ),
      'formatter' => 'open',
    ),
  );
  $export['group_additional|node|dataset|form'] = $field_group;

  return $export;
}
