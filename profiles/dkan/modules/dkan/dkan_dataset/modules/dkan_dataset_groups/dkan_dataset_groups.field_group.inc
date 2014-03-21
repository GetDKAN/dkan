<?php
/**
 * @file
 * dkan_dataset_groups.field_group.inc
 */

/**
 * Implements hook_field_group_info().
 */
function dkan_dataset_groups_field_group_info() {
  $export = array();

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_tab_about|node|group|default';
  $field_group->group_name = 'group_tab_about';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'group';
  $field_group->mode = 'default';
  $field_group->parent_name = 'group_tabs';
  $field_group->data = array(
    'label' => 'About',
    'weight' => '3',
    'children' => array(
      0 => 'body',
      1 => 'field_image',
    ),
    'format_type' => 'htab',
    'format_settings' => array(
      'formatter' => 'closed',
      'instance_settings' => array(
        'description' => '',
        'classes' => ' group-tab-about field-group-htab',
        'required_fields' => 1,
      ),
    ),
  );
  $export['group_tab_about|node|group|default'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_tab_about|node|group|form';
  $field_group->group_name = 'group_tab_about';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'group';
  $field_group->mode = 'form';
  $field_group->parent_name = 'group_tabs';
  $field_group->data = array(
    'label' => 'About',
    'weight' => '11',
    'children' => array(
      0 => 'body',
      1 => 'field_image',
    ),
    'format_type' => 'htab',
    'format_settings' => array(
      'formatter' => 'closed',
      'instance_settings' => array(
        'description' => '',
        'classes' => ' group-tab-about field-group-htab',
        'required_fields' => 1,
      ),
    ),
  );
  $export['group_tab_about|node|group|form'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_tab_datasets|node|group|default';
  $field_group->group_name = 'group_tab_datasets';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'group';
  $field_group->mode = 'default';
  $field_group->parent_name = 'group_tabs';
  $field_group->data = array(
    'label' => 'Datasets',
    'weight' => '1',
    'children' => array(
      0 => 'groups_search_entity_view_1',
      1 => 'groups_search_entity_view_1_form',
    ),
    'format_type' => 'htab',
    'format_settings' => array(
      'formatter' => 'closed',
      'instance_settings' => array(
        'description' => '',
        'classes' => '',
        'id' => '',
      ),
    ),
  );
  $export['group_tab_datasets|node|group|default'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_tab_members|node|group|default';
  $field_group->group_name = 'group_tab_members';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'group';
  $field_group->mode = 'default';
  $field_group->parent_name = 'group_tabs';
  $field_group->data = array(
    'label' => 'Members',
    'weight' => '2',
    'children' => array(
      0 => 'dkan_group_members_entity_view_1',
    ),
    'format_type' => 'htab',
    'format_settings' => array(
      'formatter' => 'closed',
      'instance_settings' => array(
        'description' => '',
        'classes' => 'group-tab-members field-group-htab',
        'id' => '',
      ),
    ),
  );
  $export['group_tab_members|node|group|default'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_tabs|node|group|default';
  $field_group->group_name = 'group_tabs';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'group';
  $field_group->mode = 'default';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => '',
    'weight' => '0',
    'children' => array(
      0 => 'group_tab_datasets',
      1 => 'group_tab_about',
      2 => 'group_tab_members',
    ),
    'format_type' => 'htabs',
    'format_settings' => array(
      'label' => '',
      'instance_settings' => array(
        'classes' => ' group-tabs field-group-htabs',
      ),
    ),
  );
  $export['group_tabs|node|group|default'] = $field_group;

  $field_group = new stdClass();
  $field_group->disabled = FALSE; /* Edit this to true to make a default field_group disabled initially */
  $field_group->api_version = 1;
  $field_group->identifier = 'group_tabs|node|group|form';
  $field_group->group_name = 'group_tabs';
  $field_group->entity_type = 'node';
  $field_group->bundle = 'group';
  $field_group->mode = 'form';
  $field_group->parent_name = '';
  $field_group->data = array(
    'label' => 'Tabs',
    'weight' => '1',
    'children' => array(
      0 => 'group_tab_datasets',
      1 => 'group_tab_members',
      2 => 'group_tab_about',
    ),
    'format_type' => 'htabs',
    'format_settings' => array(
      'label' => 'Tabs',
      'instance_settings' => array(
        'classes' => ' group-tabs field-group-htabs',
      ),
    ),
  );
  $export['group_tabs|node|group|form'] = $field_group;

  return $export;
}
