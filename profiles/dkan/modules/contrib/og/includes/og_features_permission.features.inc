<?php
/**
 * @file
 * Integration with features for og permissions.
 */

/**
 * Implements hook_features_export().
 */
function og_features_permission_features_export($data, &$export, $module_name = '') {
  $map = features_get_default_map('og_features_permission');
  $permissions = _og_features_get_permissions();
  $pipe = array();
  foreach ($data as $key) {
    // If this og permission is provided by a module, add it as a dependency.
    if (isset($map[$key]) && $map[$key] != $module_name) {
      $export['dependencies'][$map[$key]] = $map[$key];
    }
    else {
      // $key is 'entity_type:bundle:permission'.
      list($group_type, $bundle, $perm) = explode(':', $key);
      if (isset($permissions[$group_type][$bundle][$perm]['roles'])) {
        // Pipe in og roles.
        $roles = array();
        foreach ($permissions[$group_type][$bundle][$perm]['roles'] as $role) {
          $dep = $group_type . ':' . $bundle . ':' . $role;
          if (empty($pipe['og_features_role']) || !in_array($dep, $pipe['og_features_role'])) {
            $pipe['og_features_role'][] = $dep;
          }
        }
      }

      if (isset($permissions[$group_type][$bundle][$perm]['module'])) {
        // Add the module which defines the permission as a dependency.
        $module = $permissions[$group_type][$bundle][$perm]['module'];
        $export['dependencies'][$module] = $module;

        $export['dependencies']['features'] = 'features';
        $export['dependencies']['og'] = 'og';
        $export['features']['og_features_permission'][$key] = $key;
      }
    }
  }

  return $pipe;
}

/**
 * Implements hook_features_export_options().
 */
function og_features_permission_features_export_options() {
  $options = array();
  $modules = array();
  $module_info = system_get_info('module');

  $permissions = _og_features_get_permissions();

  foreach ($permissions as $group_type => $bundles) {
    foreach ($bundles as $bundle => $permissions) {
      foreach ($permissions as $permission => $info) {
        $key = $group_type . ':' . $bundle . ':' . $permission;
        $name = '(' . $group_type . ':' . $bundle . ') ' . $permission;
        $options[$key] = strip_tags($module_info[$info['module']]['name'] . ': ' . $name);
      }
    }
  }

  return $options;
}

/**
 * Implements hook_Features_export_render().
 */
function og_features_permission_features_export_render($module, $data) {
  $code = array();
  $code[] = '  $permissions = array();';
  $code[] = '';

  $perms = _og_features_get_permissions();

  foreach ($data as $key) {
    list($group_type, $bundle, $perm) = explode(':', $key);

    $permission = array();
    if (!empty($perms[$group_type][$bundle][$perm]['roles'])) {
      $roles = $perms[$group_type][$bundle][$perm]['roles'];
      $permission['roles'] = array_combine($roles, $roles);
    }
    else {
      $permission['roles'] = array();
    }

    // Ensure consistent ordering of roles on output to avoid extraneious diffs.
    asort($permission['roles']);

    $perm_identifier = features_var_export($key);
    $perm_export = features_var_export($permission, '  ');
    $code[] = "  // Exported og permission: {$perm_identifier}";
    $code[] = "  \$permissions[{$perm_identifier}] = {$perm_export};";
    $code[] = '';
  }
  $code[] = '  return $permissions;';

  $code = implode("\n", $code);
  return array('og_features_default_permissions' => $code);
}

/**
 * Implements hook_features_revert().
 */
function og_features_permission_features_revert($module) {
  og_features_permission_features_rebuild($module);
}

/**
 * Implements hook_features_rebuild().
 *
 * Iterates through default permissions and update the permissions map.
 *
 * @param string $module
 *   The modules whose default user permissions should be rebuild.
 */
function og_features_permission_features_rebuild($module) {
  require_once dirname(__FILE__) . '/og_features_role.features.inc';

  if ($defaults = features_get_default('og_features_permission', $module)) {
    $grant = array();
    $revoke = array();

    foreach ($defaults as $key => $details) {
      list($group_type, $bundle, $perm) = explode(':', $key);

      // Make sure the role exists for this entity.
      foreach ($details['roles'] as $role) {
        $bundle_role = _og_features_role_exists($role, $group_type, $bundle);
        if (empty($bundle_role)) {
          og_role_save(og_role_create($role, $group_type, 0, $bundle));
        }
      }

      $roles = og_roles($group_type, $bundle, 0);
      foreach ($roles as $rid => $rolename) {
        if (in_array($rolename, $details['roles'])) {
          $grant[$rid][] = $perm;
        }
        else {
          $revoke[$rid][] = $perm;
        }
      }
    }

    if (!empty($grant)) {
      foreach ($grant as $rid => $permissions) {
        og_role_grant_permissions($rid, $permissions);
      }
    }

    if (!empty($revoke)) {
      foreach ($revoke as $rid => $permissions) {
        og_role_revoke_permissions($rid, $permissions);
      }
    }
  }
}

/**
 * Get all defined permissions keyed by permission with.
 */
function _og_features_get_permissions() {
  $return = array();

  $query = db_select('og_role', 'ogr');
  $query->innerJoin('og_role_permission', 'ogrp', 'ogr.rid = ogrp.rid');

  $result = $query->condition('ogr.gid', '0')
    ->fields('ogr', array('group_type', 'group_bundle', 'name'))
    ->fields('ogrp', array('permission'))
    ->orderBy('ogr.group_type')
    ->orderBy('ogr.group_bundle')
    ->orderBy('ogrp.permission')
    ->execute();

  $assigned_perms = array();
  while ($row = $result->fetchAssoc()) {
    $assigned_perms[$row['group_type']][$row['group_bundle']][$row['permission']][] = $row['name'];
  }

  $perms = og_get_permissions();

  $groups = og_get_all_group_bundle();
  foreach ($groups as $group_type => $bundles) {
    foreach ($bundles as $bundle => $name) {
      foreach ($perms as $perm => $info) {
        if (empty($assigned_perms[$group_type][$bundle][$perm])) {
          $return[$group_type][$bundle][$perm]['roles'] = array();
        }
        else {
          $return[$group_type][$bundle][$perm]['roles'] = $assigned_perms[$group_type][$bundle][$perm];
        }
        $return[$group_type][$bundle][$perm]['module'] = $info['module'];
      }
    }
  }
  return $return;
}
