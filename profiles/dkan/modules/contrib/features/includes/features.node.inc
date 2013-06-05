<?php

/**
 * Implements hook_features_api().
 */
function node_features_api() {
  return array(
    'node' => array(
      'name' => t('Content types'),
      'feature_source' => TRUE,
      'default_hook' => 'node_info',
    ),
  );
}

/**
 * Implements hook_features_export_options().
 */
function node_features_export_options() {
  return node_type_get_names();
}

/**
 * Implements hook_features_export.
 */
function node_features_export($data, &$export, $module_name = '') {
  $pipe = array();
  $map = features_get_default_map('node');

  foreach ($data as $type) {
    // Poll node module to determine who provides the node type.
    if ($info = node_type_get_type($type)) {
      // If this node type is provided by a different module, add it as a dependency
      if (isset($map[$type]) && $map[$type] != $module_name) {
        $export['dependencies'][$map[$type]] = $map[$type];
      }
      // Otherwise export the node type.
      elseif (in_array($info->base, array('node_content', 'features'))) {
        $export['features']['node'][$type] = $type;
        $export['dependencies']['node'] = 'node';
        $export['dependencies']['features'] = 'features';
      }

      $fields = field_info_instances('node', $type);
      foreach ($fields as $name => $field) {
        $pipe['field_instance'][] = "node-{$field['bundle']}-{$field['field_name']}";
      }
    }
  }

  return $pipe;
}

/**
 * Implements hook_features_export_render().
 */
function node_features_export_render($module, $data, $export = NULL) {
  $elements = array(
    'name' => TRUE,
    'base' => FALSE,
    'description' => TRUE,
    'has_title' => FALSE,
    'title_label' => TRUE,
    'help' => TRUE,
  );
  $output = array();
  $output[] = '  $items = array(';
  foreach ($data as $type) {
    if ($info = node_type_get_type($type)) {
      // Force module name to be 'features' if set to 'node. If we leave as
      // 'node' the content type will be assumed to be database-stored by
      // the node module.
      $info->base = ($info->base === 'node') ? 'features' : $info->base;
      $output[] = "    '{$type}' => array(";
      foreach ($elements as $key => $t) {
        if ($t) {
          $text = str_replace("'", "\'", $info->$key);
          $text = !empty($text) ? "t('{$text}')" : "''";
          $output[] = "      '{$key}' => {$text},";
        }
        else {
          $output[] = "      '{$key}' => '{$info->$key}',";
        }
      }
      $output[] = "    ),";
    }
  }
  $output[] = '  );';
  $output[] = '  return $items;';
  $output = implode("\n", $output);
  return array('node_info' => $output);
}

/**
 * Implements hook_features_revert().
 *
 * @param $module
 * name of module to revert content for
 */
function node_features_revert($module = NULL) {
  if ($default_types = features_get_default('node', $module)) {
    foreach ($default_types as $type_name => $type_info) {
      // Delete node types
      // We don't use node_type_delete() because we do not actually
      // want to delete the node type (and invoke hook_node_type()).
      // This can lead to bad consequences like CCK deleting field
      // storage in the DB.
      db_delete('node_type')
        ->condition('type', $type_name)
        ->execute();
    }
    node_types_rebuild();
    menu_rebuild();
  }
}

/**
 * Implements hook_features_disable().
 *
 * When a features module is disabled, modify any node types it provides so
 * they can be deleted manually through the content types UI.
 *
 * @param $module
 *   Name of module that has been disabled.
 */
function node_features_disable($module) {
  if ($default_types = features_get_default('node', $module)) {
    foreach ($default_types as $type_name => $type_info) {
      $type_info = node_type_load($type_name);
      $type_info->module = 'node';
      $type_info->custom = 1;
      $type_info->modified = 1;
      $type_info->locked = 0;
      node_type_save($type_info);
    }
  }
}

/**
 * Implements hook_features_enable().
 *
 * When a features module is enabled, modify any node types it provides so
 * they can no longer be deleted manually through the content types UI.
 *
 * @param $module
 *   Name of module that has been enabled.
 */
function node_features_enable($module) {
  if ($default_types = features_get_default('node', $module)) {
    foreach ($default_types as $type_name => $type_info) {
      // Ensure the type exists.
      if ($type_info = node_type_load($type_name)) {
        $type_info->module = $module;
        $type_info->custom = 0;
        $type_info->modified = 0;
        $type_info->locked = 1;
        node_type_save($type_info);
      }
    }
  }
}
