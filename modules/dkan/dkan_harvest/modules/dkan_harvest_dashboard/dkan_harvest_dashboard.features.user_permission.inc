<?php
// @codingStandardsIgnoreFile
/**
 * @file
 * dkan_harvest_dashboard.features.user_permission.inc
 */

/**
 * Implements hook_user_default_permissions().
 */
function dkan_harvest_dashboard_user_default_permissions() {
  $permissions = array();

  // Exported permission: 'use Rules component rules_dkan_harvest_source_cache'.
  $permissions['use Rules component rules_dkan_harvest_source_cache'] = array(
    'name' => 'use Rules component rules_dkan_harvest_source_cache',
    'roles' => array(
      'administrator' => 'administrator',
    ),
    'module' => 'rules',
  );

  // Exported permission: 'use Rules component rules_dkan_harvest_source_cache_and_migrate'.
  $permissions['use Rules component rules_dkan_harvest_source_cache_and_migrate'] = array(
    'name' => 'use Rules component rules_dkan_harvest_source_cache_and_migrate',
    'roles' => array(
      'administrator' => 'administrator',
    ),
    'module' => 'rules',
  );

  // Exported permission: 'use Rules component rules_dkan_harvest_source_migrate'.
  $permissions['use Rules component rules_dkan_harvest_source_migrate'] = array(
    'name' => 'use Rules component rules_dkan_harvest_source_migrate',
    'roles' => array(
      'administrator' => 'administrator',
    ),
    'module' => 'rules',
  );

  return $permissions;
}
