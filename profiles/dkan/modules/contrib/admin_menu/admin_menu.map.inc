<?php

/**
 * @file
 * Implements hook_admin_menu_map() on behalf of core modules.
 *
 * @todo Replace all/most of those API functions with direct DB queries;
 *   we only need the menu arguments (keys), not fully loaded objects.
 */

/**
 * Implements hook_admin_menu_map() on behalf of Filter module.
 */
function filter_admin_menu_map() {
  if (!user_access('administer filters')) {
    return;
  }
  $map['admin/config/content/formats/%filter_format'] = array(
    'parent' => 'admin/config/content/formats',
    'arguments' => array(
      array('%filter_format' => array_keys(filter_formats())),
    ),
  );
  return $map;
}

/**
 * Implements hook_admin_menu_map() on behalf of Menu module.
 */
function menu_admin_menu_map() {
  if (!user_access('administer menu')) {
    return;
  }
  $map['admin/structure/menu/manage/%menu'] = array(
    'parent' => 'admin/structure/menu',
    'arguments' => array(
      array('%menu' => array_keys(menu_get_menus())),
    ),
  );
  return $map;
}

/**
 * Implements hook_admin_menu_map() on behalf of Node module.
 */
function node_admin_menu_map() {
  if (!user_access('administer content types')) {
    return;
  }
  $map['admin/structure/types/manage/%node_type'] = array(
    'parent' => 'admin/structure/types',
    'arguments' => array(
      array('%node_type' => array_keys(node_type_get_types())),
    ),
  );
  return $map;
}

/**
 * Implements hook_admin_menu_map() on behalf of Field UI module.
 */
function field_ui_admin_menu_map() {
  $map = array();
  foreach (entity_get_info() as $obj_type => $info) {
    foreach ($info['bundles'] as $bundle_name => $bundle_info) {
      if (isset($bundle_info['admin'])) {
        $arguments = array();
        switch ($obj_type) {
          case 'comment':
            $fields = array();
            foreach (field_info_instances($obj_type, $bundle_name) as $field) {
              $fields[] = $field['field_name'];
            }
            // @todo Make Comment module expose the original node type bundle,
            //   pretty please.
            if (drupal_substr($bundle_name, 0, 13) == 'comment_node_') {
              $bundle_name = drupal_substr($bundle_name, 13);
            }
            // @todo Doesn't work yet. Why?
            $arguments = array(
              '%comment_node_type' => array($bundle_name),
              '%field_ui_menu' => $fields,
            );
            break;

          case 'node':
            $fields = array();
            foreach (field_info_instances($obj_type, $bundle_name) as $field) {
              $fields[] = $field['field_name'];
            }
            $arguments = array(
              '%node_type' => array($bundle_name),
              '%field_ui_menu' => $fields,
            );
            break;

          case 'taxonomy_term':
            $fields = array();
            foreach (field_info_instances($obj_type, $bundle_name) as $field) {
              $fields[] = $field['field_name'];
            }
            // Map machine_name to vid.
            $arguments = array(
              '%taxonomy_vocabulary_machine_name' => array($bundle_name),
              '%field_ui_menu' => $fields,
            );
            break;

          case 'user':
            $arguments = array(
              '%field_ui_menu' => array_keys(field_info_fields('user')),
            );
            break;
        }
        if (!empty($arguments)) {
          $path = $bundle_info['admin']['path'];
          $map["$path/fields/%field_ui_menu"]['parent'] = "$path/fields";
          $map["$path/fields/%field_ui_menu"]['arguments'][] = $arguments;
        }
      }
    }
  }
  return $map;
}

/**
 * Implements hook_admin_menu_map() on behalf of Taxonomy module.
 */
function taxonomy_admin_menu_map() {
  if (!user_access('administer taxonomy')) {
    return;
  }
  $map['admin/structure/taxonomy/%taxonomy_vocabulary_machine_name'] = array(
    'parent' => 'admin/structure/taxonomy',
    'arguments' => array(
      array('%taxonomy_vocabulary_machine_name' => array_keys(taxonomy_vocabulary_get_names())),
    ),
  );
  return $map;
}

/**
 * Implements hook_admin_menu_map() on behalf of Views UI module.
 */
function views_ui_admin_menu_map() {
  if (!user_access('administer views')) {
    return;
  }
  $views = array();
  foreach (views_get_enabled_views() as $name => $view) {
    $views[] = $name;
  }
  if (empty($views)) {
    return;
  }
  $map['admin/structure/views/view/%views_ui_cache'] = array(
    'parent' => 'admin/structure/views',
    'arguments' => array(
      array('%views_ui_cache' => $views),
    ),
  );
  return $map;
}

