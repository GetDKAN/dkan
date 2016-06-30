<?php
/**
 * @file
 * Additional setup tasks for DKAN.
 */

/**
 * Implements hook_install_tasks().
 */
function dkan_install_tasks() {
  return array(
    'dkan_additional_setup' => array(
    'display_name' => t('DKAN final setup tasks'),
    'display' => TRUE,
    'type' => 'batch',
    'run' => INSTALL_TASK_RUN_IF_NOT_COMPLETED,
    ),
  );
}

/**
 * DKAN setup task. Runs a series of helper functions defined below.
 */
function dkan_additional_setup() {
  return array(
    'operations' => array(
      array('dkan_theme_config', array()),
      array('dkan_change_block_titles', array()),
      array('dkan_markdown_setup', array()),
      array('dkan_enable_optional_module', array('dkan_permissions')),
      array('dkan_enable_optional_module', array('dkan_default_topics')),
      array('dkan_revert_feature', array('dkan_sitewide_menu', array('content_menu_links', 'menu_links'))),
      array('dkan_revert_feature', array('dkan_dataset_content_types', array('field_base', 'field_instance'))),
      array('dkan_revert_feature', array('dkan_dataset_groups', array('field_base'))),
      array('dkan_revert_feature', array('dkan_dataset_groups_perms', array('og_features_permission'))),
      array('dkan_revert_feature', array('dkan_permissions', array('roles_permissions'))),
      array('dkan_revert_feature', array('dkan_sitewide', array('variable'))),
      array('dkan_revert_feature', array('dkan_sitewide_menu', array('custom_menu', 'menu_links'))),
      array('dkan_add_default_menu_links', array()),
      array('dkan_build_menu_links', array()),
      array('dkan_flush_image_styles', array()),
      array('dkan_colorizer_reset', array()),
      array('dkan_misc_variables_set', array()),
      array('dkan_group_link_delete', array()),
      array('dkan_install_default_content', array()),
      array('dkan_set_adminrole', array()),
    ),
  );
}

function dkan_theme_config(&$context) {
  $context['message'] = t('Setting theme options.');
  theme_enable(array('nuboot_radix'));
  theme_enable(array('seven'));
  variable_set('theme_default', 'nuboot_radix');
  variable_set('admin_theme', '0');

  // Disable the default Bartik theme
  theme_disable(array('bartik'));
  theme_disable(array('seven'));
}

/**
 * Change block titles for selected blocks.
 */
function dkan_change_block_titles(&$context) {
  $context['message'] = t('Changing block titles for selected blocks');
  db_query("UPDATE {block} SET title ='<none>' WHERE delta = 'main-menu' OR delta = 'login'");
  variable_set('node_access_needs_rebuild', FALSE);
  variable_set('gravatar_size', 190);
}

/**
 * Make sure markdown editor installs correctly.
 * @param $context
 */
function dkan_markdown_setup(&$context) {
  $context['message'] = t('Installing Markdown');
  module_load_include('install', 'markdowneditor', 'markdowneditor');
  _markdowneditor_insert_latest();
  $data = array(
    'pages' => "node/*\ncomment/*\nsystem/ajax",
    'eid' => 5,
  );
  drupal_write_record('bueditor_editors', $data, array('eid'));
  // Remove unsupported markdown options.
  dkan_delete_markdown_buttons($context);
}

/**
 * Enable a module on install that we don't want as a dependency for existing sites
 *
 * @param $module
 *   The module name
 *
 * @param $context
 */
function dkan_enable_optional_module($module, &$context) {
  module_enable(array($module));
  $module_info = system_get_info('module', $module);
  $context['message'] = t('Enabled %module', array('%module' => $module_info['name']));
}

/**
 * Revert particular feature components that have been overridden in the setup process
 *
 * @param $feature The feature module name
 * @param $components Array of components to revert
 * @param $context
 */
function dkan_revert_feature($feature, $components, &$context) {
  $context['message'] = t('Reverting feature %feature_name', array('%feature_name' => $feature));
  features_revert(array($feature => $components));
  cache_clear_all();
}


/**
 * Import default menu links.
 *
 * @param $context
 */
function dkan_add_default_menu_links(&$context) {
  $menu_links = array();
  // Exported menu link: main-menu_about:node/1
  $menu_links['main-menu_about:node/1'] = array(
    'menu_name' => 'main-menu',
    'link_path' => 'node/1',
    'router_path' => 'node/%',
    'link_title' => 'About',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'main-menu_about:node/1',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => 0,
    'customized' => 1,
  );
  // Exported menu link: main-menu_dataset:search/type/dataset
  $menu_links['main-menu_dataset:search/type/dataset'] = array(
    'menu_name' => 'main-menu',
    'link_path' => 'search/type/dataset',
    'router_path' => 'search/type/dataset',
    'link_title' => 'Datasets',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'main-menu_dataset:search/type/dataset',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -1,
    'customized' => 1,
  );
  // To Do: Add 'Dashboards' link to main menu when new default content is deployed.
  // Exported menu link: main-menu_dataset:search/type/data_dashboard
  // $menu_links['main-menu_dashboard:search/type/data_dashboard'] = array(
  //   'menu_name' => 'main-menu',
  //   'link_path' => 'search/type/data_dashboard',
  //   'router_path' => 'search/type/data_dashboard',
  //   'link_title' => 'Dashboards',
  //   'options' => array(
  //     'attributes' => array(
  //       'title' => '',
  //     ),
  //     'identifier' => 'main-menu_dashboard:search/type/data_dashboard',
  //   ),
  //   'module' => 'menu',
  //   'hidden' => 0,
  //   'external' => 0,
  //   'has_children' => 0,
  //   'expanded' => 0,
  //   'weight' => 4,
  //   'customized' => 1,
  // );
  // Exported menu link: main-menu_stories:stories
  $menu_links['main-menu_stories:stories'] = array(
    'menu_name' => 'main-menu',
    'link_path' => 'stories',
    'router_path' => 'stories',
    'link_title' => 'Stories',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'main-menu_stories:stories',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => 3,
    'customized' => 1,
  );
  // Exported menu link: main-menu_groups:groups
  $menu_links['main-menu_groups:groups'] = array(
    'menu_name' => 'main-menu',
    'link_path' => 'groups',
    'router_path' => 'groups',
    'link_title' => 'Groups',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'main-menu_groups:groups',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => 2,
    'customized' => 1,
  );
  t('About');
  t('Datasets');
  //t('Dashboards');
  t('Stories');
  t('Groups');

  foreach ($menu_links as $menu_link) {
    menu_link_save($menu_link);
  }
}

/**
 * Build menu links
 *
 * @param $context
 */
function dkan_build_menu_links(&$context) {
  $context['message'] = t('Building menu links and assigning custom admin menus to roles');
  $menu_links = features_get_default('menu_links', 'dkan_sitewide_menu');
  menu_links_features_rebuild_ordered($menu_links, TRUE);
  unset($_SESSION['messages']['warning']);
  cache_clear_all();
 }

/**
 * Flush the image styles
 *
 * @param $context
 */
function dkan_flush_image_styles(&$context) {
  $context['message'] = t('Flushing image styles');
  cache_clear_all();
  $image_styles = image_styles();
  foreach ( $image_styles as $image_style ) {
    image_style_flush($image_style);
  }
}

/**
 * Reset colorizer cache so that background colors and other colorizer settings are not blank at first page view
 *
 * @param $context
 */
function dkan_colorizer_reset(&$context) {
  $context['message'] = t('Resetting colorizer cache');
  global $theme_key;
  $instance = $theme_key;
  drupal_alter('colorizer_instance', $instance);
  $palette = colorizer_get_palette($theme_key, $instance);
  $file = colorizer_update_stylesheet($theme_key, $theme_key, $palette);
  clearstatcache();
}

/**
 * Set a number of miscellaneous variables
 *
 * @param $context
 */
function dkan_misc_variables_set(&$context) {
  $context['message'] = t('Setting misc DKAN variables');
  variable_set('honeypot_form_user_register_form', 1);
  variable_set('site_frontpage', 'welcome');
  variable_set('page_manager_node_view_disabled', FALSE);
  variable_set('page_manager_node_edit_disabled', FALSE);
  variable_set('page_manager_user_view_disabled', FALSE);
  // variable_set('page_manager_override_anyway', 'TRUE');
  variable_set('jquery_update_jquery_version', '1.7');
  // Disable selected views enabled by contributed modules.
  $views_disable = array(
    'og_extras_nodes' => TRUE,
    'feeds_log' => TRUE,
    'groups_page' => TRUE,
    'og_extras_groups' => TRUE,
    'og_extras_members' => TRUE,
    'dataset' => TRUE,
  );
  variable_set('views_defaults', $views_disable);
}

function dkan_install_default_content(&$context) {
  $context['message'] = t('Creating default content');
  dkan_default_content_base_install();
}

/**
 * Set the user admin role
 *
 * @param $context
 */
function dkan_set_adminrole(&$context) {
    $context['message'] = t('Setting user admin role');
    if (!variable_get('user_admin_role')) {
        if ($role = user_role_load_by_name('administrator')) {
          variable_set('user_admin_role', $role->rid);
          return t('User admin role reset to "administrator."');
        }
        else {
          return t('Administrator role not found. Skipping update.');
        }
    }
    else {
        return t('User admin role already set. Skipping update.');
    }
}

/**
 * Remove unsupported markdown options.
 *
 * @param $context
 */
function dkan_delete_markdown_buttons(&$context) {
  $context['message'] = t('Removing unsupported Markdown buttons');
  $eid = db_query('SELECT eid FROM {bueditor_editors} WHERE name = :name', array(':name' => 'Markdowneditor'))->fetchField();
  db_delete('bueditor_buttons')
    ->condition('title', 'Insert a table')
    ->condition('eid', $eid)
    ->execute();

  db_delete('bueditor_buttons')
    ->condition('title', 'Insert an abbreviation (word or acronym with definition)')
    ->condition('eid', $eid)
    ->execute();

  db_delete('bueditor_buttons')
    ->condition('title', 'Insert a footnote')
    ->condition('eid', $eid)
    ->execute();

  db_delete('bueditor_buttons')
    ->condition('title', 'Insert a horizontal ruler (horizontal line)')
    ->condition('eid', $eid)
    ->execute();

  db_delete('bueditor_buttons')
    ->condition('title', 'Teaser break')
    ->condition('eid', $eid)
    ->execute();

  db_delete('bueditor_buttons')
    ->condition('title', 'Insert a definition list')
    ->condition('eid', $eid)
    ->execute();

  db_delete('bueditor_buttons')
    ->condition('title', 'Format selected text as code')
    ->condition('eid', $eid)
    ->execute();

  db_delete('bueditor_buttons')
    ->condition('title', 'Format selected text as a code block')
    ->condition('eid', $eid)
    ->execute();

  // Update markdown linebreak button with html.
  db_update('bueditor_buttons')
    ->fields(array('content' => '<br>'))
    ->condition('title', 'Insert a line break', '=')
    ->condition('eid', $eid)
    ->execute();
}

/**
 * The groups view in og_extras creates a menu item even when the view is disabled.
 * This will delete the extra menu item until the og_extras is removed from the code base.
 *
 * @param $context
 */
function dkan_group_link_delete(&$context) {
  $context['message'] = t('Removing og_extra groups link');
  db_query('DELETE FROM {menu_links} WHERE link_path = :link_path LIMIT 1', array(':link_path' => 'groups'));
}
