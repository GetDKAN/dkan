<?php

/**
 * @file
 * dkan_sitewide_roles_perms.features.user_role.inc
 */

/**
 * Implements hook_user_default_roles().
 */
function dkan_sitewide_roles_perms_user_default_roles() {
  $roles = array();

  // Exported role: administrator.
  $roles['administrator'] = array(
    'name' => 'administrator',
    'weight' => 3,
    'machine_name' => '',
  );

  // Exported role: editor.
  $roles['editor'] = array(
    'name' => 'editor',
    'weight' => 2,
    'machine_name' => '',
  );

  return $roles;
}
