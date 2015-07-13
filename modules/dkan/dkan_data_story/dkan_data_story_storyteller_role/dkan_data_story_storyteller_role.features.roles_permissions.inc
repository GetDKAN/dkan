<?php
/**
 * @file
 * dkan_data_story_storyteller_role.features.roles_permissions.inc
 */

/**
 * Implements hook_default_roles_permissions().
 */
function dkan_data_story_storyteller_role_default_roles_permissions() {
  $roles = array();

  // Exported role: storyteller
  $roles['storyteller'] = array(
    'name' => 'storyteller',
    'weight' => 6,
    'machine_name' => 'storyteller',
    'permissions' => array(
      'access administration menu' => TRUE,
      'access administration pages' => TRUE,
      'access content overview' => TRUE,
      'administer panelizer node dkan_data_story content' => TRUE,
      'create dkan_data_story content' => TRUE,
      'delete own dkan_data_story content' => TRUE,
      'edit own dkan_data_story content' => TRUE,
      'use panels in place editing' => TRUE,
      'use text format html' => TRUE,
    ),
  );

  return $roles;
}
