<?php
/**
 * @file
 * Integration with features for og roles.
 */

/**
 * Implements hook_features_export().
 */
function og_features_role_features_export($data, &$export, $module_name = '') {
  $export['dependencies']['features'] = 'features';
  $export['dependencies']['og'] = 'og';
  $map = features_get_default_map('og_default_roles');
  $og_roles = array(
    OG_ANONYMOUS_ROLE,
    OG_AUTHENTICATED_ROLE,
    OG_ADMINISTRATOR_ROLE,
  );
  $default_roles = og_get_default_roles();
  foreach ($data as $value) {
    list($group_type, $bundle, $role) = explode(':', $value);
    if (in_array($role, $default_roles)) {
      if (!in_array($role, $og_roles)) {
        // Role is provided via module add it as a dependency.
        if ($module = _og_features_role_dependencies($role)) {
          $export['dependencies'][$module] = $module;
        }
      }
    }
    else {
      // Add the role to the export since its not provided by a module.
      $export['features']['og_features_role'][$value] = $value;
    }
  }

  return array();
}

/**
 * Implements hook_features_export_options().
 */
function og_features_role_features_export_options() {
  $options = array();
  $default_roles = og_get_default_roles();

  $groups = og_get_all_group_bundle();
  foreach ($groups as $group_type => $bundles) {
    foreach ($bundles as $bundle => $name) {
      $roles = og_roles($group_type, $bundle, 0);
      foreach ($roles as $rid => $role) {
        if (!in_array($role, $default_roles)) {
          $key = $group_type . ':' . $bundle . ':' . $role;
          $options[$key] = $key;
        }
      }
    }
  }

  return $options;
}

/**
 * Implements hook_features_export_render().
 */
function og_features_role_features_export_render($module, $data) {
  $code = array();
  $code[] = '  $roles = array();';
  $code[] = '';

  foreach ($data as $name) {
    list($group_type, $bundle, $rolename) = explode(':', $name);
    $role = array(
      'gid' => 0,
      'group_type' => $group_type,
      'group_bundle' => $bundle,
      'name' => $rolename,
    );
    $role_identifier = features_var_export($name);
    $role_export = features_var_export($role, '  ');
    $code[] = "  // Exported OG Role: {$role_identifier}.";
    $code[] = "  \$roles[{$role_identifier}] = ${role_export};";
    $code[] = "";
  }

  $code[] = '  return $roles;';
  $code = implode("\n", $code);

  return array('og_features_default_roles' => $code);
}

/**
 * Implements hook_features_revert().
 */
function og_features_role_features_revert($module) {
  og_features_role_features_rebuild($module);
}

/**
 * Implements hook_features_rebuild().
 *
 * Iterates through default permissions and update the permissions map.
 *
 * @param string $module
 *   The modules whose default roles should be rebuild.
 */
function og_features_role_features_rebuild($module) {
  if ($defaults = features_get_default('og_features_role', $module)) {
    foreach ($defaults as $key => $value) {
      list($group_type, $bundle, $name) = explode(':', $key);
      $role = (object) $value;
      if ($rid = _og_features_role_exists($name, $group_type, $bundle)) {
        $role->rid = $rid;
      }
      og_role_save($role);
    }
  }
}

/**
 * Returns rid if role with $name exists, FALSE otherwise.
 *
 * @param string $name
 *   The role name
 * @param string $group_type
 *   The entity type
 * @param string $bundle
 *   The bundle
 *
 * @return mixed
 *   Integer indicating rid if the role exists FALSE otherwise.
 */
function _og_features_role_exists($name, $group_type, $bundle) {
  $roles = og_roles($group_type, $bundle, 0);
  if (in_array($name, $roles)) {
    $flipped = array_flip($roles);
    return $flipped[$name];
  }
  else {
    return FALSE;
  }
}

/**
 * Return a module name providing given role or FALSE.
 *
 * @param string $role_name
 *   A role name to return the declaring module for.
 *
 * @return mixed
 *   A string representing a module name implementing given role or FALSE.
 */
function _og_features_role_dependencies($role_name) {
  $roles = &drupal_static(__FUNCTION__, array());

  if (empty($roles)) {
    foreach (module_implements('og_default_roles') as $module) {
      $moduleroles = module_invoke($module, 'og_default_roles');
      foreach ($moduleroles as $role) {
        $roles[$role] = $module;
      }
    }
  }

  if (!empty($roles[$role_name])) {
    return $roles[$role_name];
  }
  else {
    return FALSE;
  }
}
