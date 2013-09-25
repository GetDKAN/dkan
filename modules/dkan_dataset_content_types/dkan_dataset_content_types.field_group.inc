<?php
/**
 * @file
 * dkan_dataset_content_types.field_group.inc
 */

/**
 * Implements hook_field_group_info().
 */
function dkan_dataset_content_types_field_group_info() {
  $export = array();

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_additional|node|dataset|default';
  $field_group->group_name = 'group_additional';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'dataset';
  $field_group->mode = 'default';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Dataset Info',
    'weight' => '3',
    'children' => array(
      0 => 'field_author',
      1 => 'field_contact_email',
      2 => 'field_contact_name',
      3 => 'field_data_dictionary',
      4 => 'field_frequency',
      5 => 'field_granularity',
      6 => 'field_license',
      7 => 'field_public_access_level',
      8 => 'field_spatial',
      9 => 'field_spatial_geographical_cover',
      10 => 'field_temporal_coverage',
      11 => 'og_group_ref',
      12 => 'release_date',
      13 => 'modified_date',
      14 => 'identifier',
    ),
    'format_type' => 'table',
    'format_settings' => array(
      'label' => 'Dataset Info',
      'instance_settings' => array(
        'label' => 'hide',
        'desc' => '<div class="fieldset-description"><i class="icon-large icon-info-sign"></i>These fields are compatible with <a href="http://www.w3.org/TR/vocab-dcat/">DCAT</a>, an RDF vocabulary designed to facilitate interoperability between data catalogs published on the Web.</div>',
        'first_column' => 'Field',
        'second_column' => 'Value',
        'cells' => 'empty',
        'classes' => ' group-additional field-group-table',
      ),
    ),
  );
  $export['group_additional|node|dataset|default'] = $field_group;

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
    'weight' => '1',
    'children' => array(
      0 => 'field_additional_info',
      1 => 'field_author',
      2 => 'field_contact_email',
      3 => 'field_contact_name',
      4 => 'field_data_dictionary',
      5 => 'field_frequency',
      6 => 'field_granularity',
      7 => 'field_public_access_level',
      8 => 'field_related_content',
      9 => 'field_resources',
      10 => 'field_spatial',
      11 => 'field_spatial_geographical_cover',
      12 => 'field_temporal_coverage',
      13 => 'og_group_ref',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'label' => 'Dataset Information',
      'instance_settings' => array(
        'required_fields' => 1,
        'classes' => ' group-additional field-group-fieldset',
        'description' => '<i class="icon-large icon-info-sign"></i>These fields are compatible with <a href="http://www.w3.org/TR/vocab-dcat/">DCAT</a>, an RDF vocabulary designed to facilitate interoperability between data catalogs published on the Web. These fields are also compatible with the Common Core metadata schema from <a href="http://project-open-data.github.io/schema/">Project Open Data</a>.',
      ),
      'formatter' => 'open',
    ),
  );
  $export['group_additional|node|dataset|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_data|node|resource|form';
  $field_group->group_name = 'group_data';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'resource';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Resource',
    'weight' => '2',
    'children' => array(
      0 => 'group_link_api',
      1 => 'group_link_file',
      2 => 'group_upload',
    ),
    'format_type' => 'htabs',
    'format_settings' => array(
      'label' => 'Resource',
      'instance_settings' => array(
        'classes' => ' group-data field-group-htabs',
      ),
    ),
  );
  $export['group_data|node|resource|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_link_api|node|resource|form';
  $field_group->group_name = 'group_link_api';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'resource';
  $field_group->mode = 'form';
  $field_group->parent_name = 'group_data';
  $field_group->data = array(
    'label' => 'Link to an API',
    'weight' => '11',
    'children' => array(
      0 => 'field_link_api',
    ),
    'format_type' => 'htab',
    'format_settings' => array(
      'label' => 'Link to an API',
      'instance_settings' => array(
        'required_fields' => 1,
        'classes' => 'htab_link_api group-link-api field-group-htab',
        'description' => '',
      ),
      'formatter' => 'closed',
    ),
  );
  $export['group_link_api|node|resource|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_link_file|node|resource|form';
  $field_group->group_name = 'group_link_file';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'resource';
  $field_group->mode = 'form';
  $field_group->parent_name = 'group_data';
  $field_group->data = array(
    'label' => 'Link to a file',
    'weight' => '9',
    'children' => array(
      0 => 'field_link_remote_file',
    ),
    'format_type' => 'htab',
    'format_settings' => array(
      'label' => 'Link to a file',
      'instance_settings' => array(
        'required_fields' => 1,
        'classes' => 'htab_link_file group-link-file field-group-htab',
        'description' => '',
      ),
      'formatter' => 'closed',
    ),
  );
  $export['group_link_file|node|resource|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_primary|node|dataset|form';
  $field_group->group_name = 'group_primary';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'dataset';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Primary',
    'weight' => '0',
    'children' => array(
      0 => 'body',
      1 => 'field_license',
      2 => 'field_tags',
      3 => 'title',
    ),
    'format_type' => 'fieldset',
    'format_settings' => array(
      'label' => 'Primary',
      'instance_settings' => array(
        'required_fields' => 1,
        'classes' => ' group-primary field-group-fieldset',
        'description' => '',
      ),
      'formatter' => 'open',
    ),
  );
  $export['group_primary|node|dataset|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_upload|node|resource|form';
  $field_group->group_name = 'group_upload';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'resource';
  $field_group->mode = 'form';
  $field_group->parent_name = 'group_data';
  $field_group->data = array(
    'label' => 'Upload a file',
    'weight' => '12',
    'children' => array(
      0 => 'field_upload',
    ),
    'format_type' => 'htab',
    'format_settings' => array(
      'label' => 'Upload a file',
      'instance_settings' => array(
        'required_fields' => 1,
        'classes' => 'htab_link_upload group-upload field-group-htab',
        'description' => '',
      ),
      'formatter' => 'closed',
    ),
  );
  $export['group_upload|node|resource|form'] = $field_group;

  return $export;
}
