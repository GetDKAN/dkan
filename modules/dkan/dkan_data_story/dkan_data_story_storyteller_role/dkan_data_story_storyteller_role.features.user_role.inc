<?php
/**
 * @file
 * dkan_data_story_storyteller_role.features.user_role.inc
 */

/**
 * Implements hook_user_default_roles().
 */
function dkan_data_story_storyteller_role_user_default_roles() {
  $roles = array();

  // Exported role: storyteller.
  $roles['storyteller'] = array(
    'name' => 'storyteller',
    'weight' => 6,
    'machine_name' => 'storyteller',
  );

  return $roles;
}
