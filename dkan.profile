<?php
/**
 * @file
 * Additional setup tasks for DKAN.
 */

/**
 * Implements hook_install_tasks().
 */
function dkan_install_tasks() {
  $tasks = array();
  $tasks['dkan_additional_setup'] = array(
    'display_name' => 'Cleanup',
  );
  return $tasks;
}

/**
 * Implements hook_install_tasks().
 */
function dkan_additional_setup() {
  // Change block titles for selected blocks.
  db_query("UPDATE {block} SET title ='<none>' WHERE delta = 'main-menu' OR delta = 'login'");
  variable_set('node_access_needs_rebuild', FALSE);
  variable_set('gravatar_size', 190);

  // Make sure markdown editor installs correctly.
  module_load_include('install', 'markdowneditor', 'markdowneditor');
  _markdowneditor_insert_latest();
  $data = array(
    'pages' => "node/*\ncomment/*\nsystem/ajax",
    'eid' => 5,
  );
  drupal_write_record('bueditor_editors', $data, array('eid'));
  
  dkan_default_content_base_install();
  // Keeps us from getting notices "No module defines permission".
  module_enable(array('dkan_sitewide_roles_perms'));

  features_revert(array('dkan_sitewide_menu' => array('content_menu_links')));
  features_revert(array('dkan_sitewide_menu' => array('menu_links')));
  features_revert(array('dkan_dataset_content_types' => array('field_base', 'field_instance')));
  features_revert(array('dkan_dataset_groups' => array('field_base')));
  features_revert(array('dkan_dataset_groups' => array('search_api_index')));
  features_revert(array('dkan_sitewide_search_db' => array('search_api_index')));
  cache_clear_all();
  features_revert(array('dkan_sitewide_search_db' => array('search_api_server')));
  features_revert(array('dkan_sitewide_roles_perms' => array('user_permission', 'og_features_permission')));
  features_revert(array('dkan_sitewide' => array('variable')));
  unset($_SESSION['messages']['warning']);
  cache_clear_all();

  // Flush image styles.
  $image_styles = image_styles();
  foreach ( $image_styles as $image_style ) {
    image_style_flush($image_style);
  }

  // Set honeypot protection on user registration form
  variable_set('honeypot_form_user_register_form', 1);

  global $theme_key;

  // check to see if colorizer css file exists on site
  $source_path = drupal_get_path('theme', $theme_key) . '/';
  $source_file = $source_path . variable_get('colorizer_cssfile', '');
  if (!file_exists($source_file)) {
    return;
  }

  $instance = $theme_key;
  // allow other modules to change the instance we are updating
  // allows for group-specific color instances rather than tying to theme
  drupal_alter('colorizer_instance', $instance);

  $file = variable_get('colorizer_' . $instance . '_stylesheet', '');

  // recreate any missing colorize css files
  if (!file_exists($file)) {
    $palette = colorizer_get_palette($theme_key, $instance);
    if (!empty($palette)) {
      $file = colorizer_update_stylesheet($theme_key, $instance, $palette);
      // clear file status cache so file_exists will look for file again
      clearstatcache();
    }
  }
}
