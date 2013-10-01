<?php

/**
 * Implements hook_features_api().
 */
function user_features_api() {
  return array(
    'user_role' => array(
      'name' => t('Roles'),
      'feature_source' => TRUE,
      'default_hook' => 'user_default_roles',
      'default_file' => FEATURES_DEFAULTS_INCLUDED,
    ),
    'user_permission' => array(
      'name' => t('Permissions'),
      'feature_source' => TRUE,
      'default_hook' => 'user_default_permissions',
      'default_file' => FEATURES_DEFAULTS_INCLUDED,
    ),
  );
}

/**
 * Implements hook_features_export().
 */
function user_permission_features_export($data, &$export, $module_name = '') {
  $export['dependencies']['features'] = 'features';

  // Ensure the modules that provide the given permissions are included as dependencies.
  $map = user_permission_get_modules();
  foreach ($data as $perm) {
    $perm_name = $perm;
    // Export vocabulary permissions using the machine name, instead of
    // vocabulary id.
    _user_features_change_term_permission($perm_name, 'machine_name');
    if (isset($map[$perm_name])) {
      $perm_module = $map[$perm_name];
      $export['dependencies'][$perm_module] = $perm_module;
      $export['features']['user_permission'][$perm] = $perm;
    }
  }

  return array();
}

/**
 * Implements hook_features_export_options().
 */
function user_permission_features_export_options() {
  $modules = array();
  $module_info = system_get_info('module');
  foreach (module_implements('permission') as $module) {
    $modules[$module_info[$module]['name']] = $module;
  }
  ksort($modules);

  $options = array();
  foreach ($modules as $display_name => $module) {
    if ($permissions = module_invoke($module, 'permission')) {
      foreach ($permissions as $perm => $perm_item) {
        // Export vocabulary permissions using the machine name, instead of
        // vocabulary id.
        _user_features_change_term_permission($perm);
        $options[$perm] = strip_tags("{$display_name}: {$perm_item['title']}");
      }
    }
  }
  return $options;
}

/**
 * Implements hook_features_export_render().
 */
function user_permission_features_export_render($module, $data) {
  $perm_modules = &drupal_static(__FUNCTION__ . '_perm_modules');
  if (!isset($perm_modules)) {
    $perm_modules = user_permission_get_modules();
  }

  $code = array();
  $code[] = '  $permissions = array();';
  $code[] = '';

  $permissions = _user_features_get_permissions();

  foreach ($data as $perm_name) {
    $permission = array();
    // Export vocabulary permissions using the machine name, instead of
    // vocabulary id.
    $perm = $perm_name;
    _user_features_change_term_permission($perm_name, 'machine_name');
    $permission['name'] = $perm;
    if (!empty($permissions[$perm_name])) {
      sort($permissions[$perm_name]);
      $permission['roles'] = drupal_map_assoc($permissions[$perm_name]);
    }
    else {
      $permission['roles'] = array();
    }
    if (isset($perm_modules[$perm_name])) {
      $permission['module'] = $perm_modules[$perm_name];
    }
    $perm_identifier = features_var_export($perm);
    $perm_export = features_var_export($permission, '  ');
    $code[] = "  // Exported permission: {$perm_identifier}.";
    $code[] = "  \$permissions[{$perm_identifier}] = {$perm_export};";
    $code[] = "";
  }

  $code[] = '  return $permissions;';
  $code = implode("\n", $code);
  return array('user_default_permissions' => $code);
}

/**
 * Implements hook_features_revert().
 */
function user_permission_features_revert($module) {
  user_permission_features_rebuild($module);
}

/**
 * Implements hook_features_rebuild().
 * Iterate through default permissions and update the permissions map.
 *
 * @param $module
 *   The module whose default user permissions should be rebuilt.
 */
function user_permission_features_rebuild($module) {
  if ($defaults = features_get_default('user_permission', $module)) {
    // Make sure the list of available node types is up to date, especially when
    // installing multiple features at once, for example from an install profile
    // or via drush.
    node_types_rebuild();

    $modules = user_permission_get_modules();
    $roles = _user_features_get_roles();
    $permissions_by_role = _user_features_get_permissions(FALSE);
    foreach ($defaults as $permission) {
      $perm = $permission['name'];
      _user_features_change_term_permission($perm, 'machine_name');
      if (empty($modules[$perm])) {
        $args = array('!name' => $perm, '!module' => $module,);
        $msg = t('Warning in features rebuild of !module. No module defines permission "!name".', $args);
        drupal_set_message($msg, 'warning');
        continue;
      }
      // Export vocabulary permissions using the machine name, instead of
      // vocabulary id.
      foreach ($roles as $role) {
        if (in_array($role, $permission['roles'])) {
          $permissions_by_role[$role][$perm] = TRUE;
        }
        else {
          $permissions_by_role[$role][$perm] = FALSE;
        }
      }
    }
    // Write the updated permissions.
    foreach ($roles as $rid => $role) {
      if (isset($permissions_by_role[$role])) {
        user_role_change_permissions($rid, $permissions_by_role[$role]);
      }
    }
  }
}

/**
 * Implements hook_features_export().
 */
function user_role_features_export($data, &$export, $module_name = '') {
  $export['dependencies']['features'] = 'features';
  $map = features_get_default_map('user_role', 'name');
  foreach ($data as $role) {
    // Role is provided by another module. Add dependency.
    if (isset($map[$role]) && $map[$role] != $module_name) {
      $export['dependencies'][$map[$role]] = $map[$role];
    }
    // Export.
    elseif(user_role_load_by_name($role)) {
      $export['features']['user_role'][$role] = $role;
    }
  }
  return array();
}

/**
 * Implements hook_features_export_options().
 */
function user_role_features_export_options() {
  return drupal_map_assoc(_user_features_get_roles(FALSE));
}

/**
 * Implements hook_features_export_render().
 */
function user_role_features_export_render($module, $data) {
  $code = array();
  $code[] = '  $roles = array();';
  $code[] = '';

  foreach ($data as $name) {
    if ($role = user_role_load_by_name($name)) {
      unset($role->rid);
      $role_identifier = features_var_export($name);
      $role_export = features_var_export($role , '  ');
      $code[] = "  // Exported role: {$name}.";
      $code[] = "  \$roles[{$role_identifier}] = {$role_export};";
      $code[] = "";
    }
  }

  $code[] = '  return $roles;';
  $code = implode("\n", $code);
  return array('user_default_roles' => $code);
}

/**
 * Implements hook_features_revert().
 */
function user_role_features_revert($module) {
  user_role_features_rebuild($module);
}

/**
 * Implements hook_features_rebuild().
 */
function user_role_features_rebuild($module) {
  if ($defaults = features_get_default('user_role', $module)) {
    foreach ($defaults as $role) {
      $role = (object) $role;
      if ($existing = user_role_load_by_name($role->name)) {
        $role->rid = $existing->rid;
      }
      user_role_save($role);
    }
  }
}

/**
 * Generate $rid => $role with role names untranslated.
 */
function _user_features_get_roles($builtin = TRUE) {
  $roles = array();
  foreach (user_roles() as $rid => $name) {
    switch ($rid) {
      case DRUPAL_ANONYMOUS_RID:
        if ($builtin) {
          $roles[$rid] = 'anonymous user';
        }
        break;
      case DRUPAL_AUTHENTICATED_RID:
        if ($builtin) {
          $roles[$rid] = 'authenticated user';
        }
        break;
      default:
        $roles[$rid] = $name;
        break;
    }
  }
  return $roles;
}

/**
 * Represent the current state of permissions as a perm to role name array map.
 */
function _user_features_get_permissions($by_role = TRUE) {
  $map = user_permission_get_modules();
  $roles = _user_features_get_roles();
  $permissions = array();
  foreach (user_role_permissions($roles) as $rid => $role_permissions) {
    if ($by_role) {
      foreach (array_keys(array_filter($role_permissions)) as $permission) {
        if (isset($map[$permission])) {
          $permissions[$permission][] = $roles[$rid];
        }
      }
    }
    else {
      $permissions[$roles[$rid]] = array();
      foreach ($role_permissions as $permission => $status) {
        if (isset($map[$permission])) {
          $permissions[$roles[$rid]][$permission] = $status;
        }
      }
    }
  }
  return $permissions;
}
