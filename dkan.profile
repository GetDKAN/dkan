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
  $tasks['dkan_sitewide_roles_perms_set_admin_role'] = array(
    'display_name' => 'Set admin role',
  );
  return $tasks;
}

/**
 * Implements hook_install_tasks().
 */
function dkan_additional_setup() {
  global $theme_key;

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

  // Keeps us from getting notices "No module defines permission".
  module_enable(array('dkan_sitewide_roles_perms'));

  features_revert(array('dkan_sitewide_menu' => array('content_menu_links')));
  features_revert(array('dkan_sitewide_menu' => array('menu_links')));
  features_revert(array('dkan_dataset_content_types' => array('field_base', 'field_instance')));
  features_revert(array('dkan_dataset_groups' => array('field_base')));
  cache_clear_all();
  features_revert(array('dkan_sitewide_roles_perms' => array('user_permission', 'og_features_permission')));
  features_revert(array('dkan_sitewide' => array('variable')));
  features_revert(array('dkan_data_story_storyteller_role' => array('user_role', 'roles_permissions')));
  features_revert(array('dkan_sitewide_profile_page' => array('menu_custom', 'menu_links')));
  $menu_links = features_get_default('menu_links', 'dkan_sitewide_profile_page');
  menu_links_features_rebuild_ordered($menu_links, TRUE);

  unset($_SESSION['messages']['warning']);
  cache_clear_all();

  // Flush image styles.
  $image_styles = image_styles();
  foreach ( $image_styles as $image_style ) {
    image_style_flush($image_style);
  }

  // Set honeypot protection on user registration form
  variable_set('honeypot_form_user_register_form', 1);

  //Fix the problem with colorizer and the first time access.
  $instance = $theme_key;
  drupal_alter('colorizer_instance', $instance);
  $palette = colorizer_get_palette($theme_key, $instance);
  $file = colorizer_update_stylesheet($theme_key, $theme_key, $palette);
  clearstatcache();
  dkan_default_content_base_install();
}
