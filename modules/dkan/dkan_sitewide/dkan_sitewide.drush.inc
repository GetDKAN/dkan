<?php
/**
 * @file
 * Drush commands for dkan profile.
 */

/**
 * Implements hook_drush_command().
 */
function dkan_sitewide_drush_command() {
  $correct_args = array('panels', 'context');
  $correct_args = implode(', ', $correct_args);
  return array(
    'dkan_sitewide_layout_manager' => array(
      'description' => 'sets DKAN Layout Manager',
      'aliases' => array('dkan_layout', 'dkanlayout'),
      'arguments' => array(
        'operation' => 'The type of operation (' . $correct_args . ').',
      ),
    ),
  );
}

/**
 * Implements drush_command().
 */
function drush_dkan_sitewide_layout_manager($operation = 'panels') {
  $correct_args = array('panels', 'context');
  if (!in_array($operation, $correct_args)) {
    $correct_args = implode(', ', $correct_args);
    $error_message = dt(
      '"@operation" is not a valid operation. Please choose between @options.',
      array(
        '@operation' => $operation,
        '@options' => $correct_args,
      )
    );
    return drush_set_error($error_message);
  }
  $function = 'dkan_sitewide_set_' . $operation . '_as_layout_manager';
  $function();
  node_access_rebuild();
  cache_clear_all();
}

/**
 * Sets panels as the layout manager for dkan pages.
 */
function dkan_sitewide_set_panels_as_layout_manager() {
  // Disable modules not needed for panels.
  dkan_sitewide_disable_modules(array('dkan_sitewide_context', 'dkan_sitewide_demo_front'));
  // Enable Modules needed for panels.
  dkan_sitewide_enable_modules(array('dkan_sitewide_panels'));
  // Enable themes.
  theme_enable(array('nuboot_radix'));
  // Revert features.
  dkan_sitewide_revert_features(array('dkan_sitewide_panels'));
  // Set Variables.
  dkan_sitewide_set_variables(array(
    'theme_default' => 'nuboot_radix',
    'admin_theme' => 'nuboot_radix',
  ));
  // Move nuboot colorizer settings to nuboot_radix.
  dkan_sitewide_migrate_theme_settings('nuboot', 'nuboot_radix');
  // Disable themes.
  theme_disable(array('nuboot'));
}

/**
 * Sets context as the layout manager for dkan pages.
 */
function dkan_sitewide_set_context_as_layout_manager() {
  // Disable modules not needed for panels.
  dkan_sitewide_disable_modules(array('dkan_sitewide_panels'));
  // Enable Modules needed for panels.
  dkan_sitewide_enable_modules(array('dkan_sitewide_context', 'dkan_sitewide_demo_front'));
  // Enable themes.
  theme_enable(array('nuboot'));
  // Revert features.
  dkan_sitewide_revert_features(array('dkan_sitewide_context', 'dkan_sitewide_demo_front'));
  // Set Variables.
  dkan_sitewide_set_variables(array(
    'theme_default' => 'nuboot',
    'admin_theme' => 'nuboot',
  ));
  // Move nuboot colorizer settings to nuboot_radix.
  dkan_sitewide_migrate_theme_settings('nuboot_radix', 'nuboot');
  // Disable themes.
  theme_disable(array('nuboot_radix'));
}

/**
 * Helper to enable a set of modules.
 */
function dkan_sitewide_enable_modules($module_list = array()) {
  module_enable($module_list);
  cache_clear_all();
  drush_log(t('@modules enabled', array('@modules' => implode(' ', $module_list))), 'success');
}

/**
 * Helper to disable a set of modules.
 */
function dkan_sitewide_disable_modules($module_list = array()) {
  module_disable($module_list);
  cache_clear_all();
  drush_log(t('@modules disabled', array('@modules' => implode(' ', $module_list))), 'success');
}

/**
 * Helper to revert a set of features.
 */
function dkan_sitewide_revert_features($features = array()) {
  foreach ($features as $feature_name) {
    $feature = features_get_features($feature_name);
    $components = array_keys($feature->info['features']);
    features_revert(array($feature_name => $components));
  }
  drush_log(t('@features reverted', array('@features' => implode(' ', $features))), 'success');
}

/**
 * Helper to set values for a set of variables.
 */
function dkan_sitewide_set_variables($variables = array()) {
  foreach ($variables as $variable => $value) {
    if (strpos($key, 'theme')) {
      theme_enable(array($value));
    }
    variable_set($variable, $value);
  }
  drush_log(t('@variables set', array('@variables' => implode(' ', array_keys($variables)))), 'success');
}

/**
 * Helper to migrate settings from one theme to another.
 */
function dkan_sitewide_migrate_theme_settings($from = 'nuboot', $to = 'nuboot_radix') {
  // Move nuboot colorizer settings to nuboot_radix.
  $palette = variable_get('colorizer_' . $from . '_palette', FALSE);
  if ($palette) {
    variable_set('colorizer_' . $to . '_palette', $palette);
  }
  // Move nuboot settings to nuboot_radix
  $settings = variable_get('theme_' . $from . '_settings', FALSE);
  if ($settings) {
    variable_set('theme_' . $to . '_settings', $settings);
  }
  drush_log(t('@from settings migrated to @to', array('@from' => $from, '@to' => $to)), 'success');
}
