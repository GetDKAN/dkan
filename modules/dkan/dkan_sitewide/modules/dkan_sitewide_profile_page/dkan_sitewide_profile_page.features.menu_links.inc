<?php
/**
 * @file
 * dkan_sitewide_profile_page.features.menu_links.inc
 */

/**
 * Implements hook_menu_default_menu_links().
 */
function dkan_sitewide_profile_page_menu_default_menu_links() {
  $menu_links = array();

  // Exported menu link: menu-command-center-menu_add-chart:admin/structure/entity-type/visualization/ve_chart/add
  $menu_links['menu-command-center-menu_add-chart:admin/structure/entity-type/visualization/ve_chart/add'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/structure/entity-type/visualization/ve_chart/add',
    'router_path' => 'admin/structure/entity-type/visualization',
    'link_title' => 'Add chart',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_add-chart:admin/structure/entity-type/visualization/ve_chart/add',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -50,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_visualizations:admin/structure/entity-type/visualization',
  );
  // Exported menu link: menu-command-center-menu_add-choropleth:admin/structure/entity-type/visualization/choropleth_visualization/add
  $menu_links['menu-command-center-menu_add-choropleth:admin/structure/entity-type/visualization/choropleth_visualization/add'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/structure/entity-type/visualization/choropleth_visualization/add',
    'router_path' => 'admin/structure/entity-type/visualization',
    'link_title' => 'Add choropleth',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_add-choropleth:admin/structure/entity-type/visualization/choropleth_visualization/add',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -48,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_visualizations:admin/structure/entity-type/visualization',
  );
  // Exported menu link: menu-command-center-menu_add-content:node/add
  $menu_links['menu-command-center-menu_add-content:node/add'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'node/add',
    'router_path' => 'node/add',
    'link_title' => 'Add content',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_add-content:node/add',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -50,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_content:admin/content',
  );
  // Exported menu link: menu-command-center-menu_add-dataset:node/add/dataset
  $menu_links['menu-command-center-menu_add-dataset:node/add/dataset'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'node/add/dataset',
    'router_path' => 'node/add/dataset',
    'link_title' => 'Add dataset',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_add-dataset:node/add/dataset',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -50,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_datasets:dataset',
  );
  // Exported menu link: menu-command-center-menu_add-group:node/add/group
  $menu_links['menu-command-center-menu_add-group:node/add/group'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'node/add/group',
    'router_path' => 'node/add/group',
    'link_title' => 'Add group',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_add-group:node/add/group',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -50,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_groups:groups',
  );
  // Exported menu link: menu-command-center-menu_content:admin/content
  $menu_links['menu-command-center-menu_content:admin/content'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/content',
    'router_path' => 'admin/content',
    'link_title' => 'Content',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_content:admin/content',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 1,
    'expanded' => 0,
    'weight' => -49,
    'customized' => 1,
  );
  // Exported menu link: menu-command-center-menu_datasets:dataset
  $menu_links['menu-command-center-menu_datasets:dataset'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'dataset',
    'router_path' => 'dataset',
    'link_title' => 'Datasets',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_datasets:dataset',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 1,
    'expanded' => 0,
    'weight' => -48,
    'customized' => 1,
  );
  // Exported menu link: menu-command-center-menu_groups:groups
  $menu_links['menu-command-center-menu_groups:groups'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'groups',
    'router_path' => 'groups',
    'link_title' => 'Groups',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_groups:groups',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 1,
    'expanded' => 0,
    'weight' => -47,
    'customized' => 1,
  );
  // Exported menu link: menu-command-center-menu_profile-settings:menutoken/5547e8fd0c1a4
  $menu_links['menu-command-center-menu_profile-settings:menutoken/5547e8fd0c1a4'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'menutoken/5547e8fd0c1a4',
    'router_path' => 'menutoken/%',
    'link_title' => 'Profile Settings',
    'options' => array(
      'menu_token_link_path' => 'user/[current-user:uid]/edit',
      'menu_token_data' => array(
        'user' => array(
          'type' => 'user',
          'plugin' => 'user_context',
          'options' => array(),
        ),
      ),
      'menu_token_options' => array(
        'clear' => 0,
      ),
      'attributes' => array(
        'title' => '',
      ),
      'alter' => TRUE,
      'identifier' => 'menu-command-center-menu_profile-settings:menutoken/5547e8fd0c1a4',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -43,
    'customized' => 1,
  );
  // Exported menu link: menu-command-center-menu_profile:user
  $menu_links['menu-command-center-menu_profile:user'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'user',
    'router_path' => 'user',
    'link_title' => 'Profile',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_profile:user',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -50,
    'customized' => 1,
  );
  // Exported menu link: menu-command-center-menu_site-preferences:admin/config/system/site-information
  $menu_links['menu-command-center-menu_site-preferences:admin/config/system/site-information'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/config/system/site-information',
    'router_path' => 'admin/config/system/site-information',
    'link_title' => 'Site Preferences',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_site-preferences:admin/config/system/site-information',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -44,
    'customized' => 1,
  );
  // Exported menu link: menu-command-center-menu_users:admin/people
  $menu_links['menu-command-center-menu_users:admin/people'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/people',
    'router_path' => 'admin/people',
    'link_title' => 'Users',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_users:admin/people',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -45,
    'customized' => 1,
  );
  // Exported menu link: menu-command-center-menu_view-all:admin/structure/entity-type/visualization
  $menu_links['menu-command-center-menu_view-all:admin/structure/entity-type/visualization'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/structure/entity-type/visualization',
    'router_path' => 'admin/structure/entity-type/visualization',
    'link_title' => 'View all',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_view-all:admin/structure/entity-type/visualization',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -46,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_visualizations:admin/structure/entity-type/visualization',
  );
  // Exported menu link: menu-command-center-menu_view-charts:admin/structure/entity-type/visualization/ve_chart
  $menu_links['menu-command-center-menu_view-charts:admin/structure/entity-type/visualization/ve_chart'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/structure/entity-type/visualization/ve_chart',
    'router_path' => 'admin/structure/entity-type/visualization',
    'link_title' => 'View charts',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_view-charts:admin/structure/entity-type/visualization/ve_chart',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -49,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_visualizations:admin/structure/entity-type/visualization',
  );
  // Exported menu link: menu-command-center-menu_view-choropleth:admin/structure/entity-type/visualization/choropleth_visualization
  $menu_links['menu-command-center-menu_view-choropleth:admin/structure/entity-type/visualization/choropleth_visualization'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/structure/entity-type/visualization/choropleth_visualization',
    'router_path' => 'admin/structure/entity-type/visualization',
    'link_title' => 'View choropleth',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_view-choropleth:admin/structure/entity-type/visualization/choropleth_visualization',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -47,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_visualizations:admin/structure/entity-type/visualization',
  );
  // Exported menu link: menu-command-center-menu_view-contents:admin/content
  $menu_links['menu-command-center-menu_view-contents:admin/content'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/content',
    'router_path' => 'admin/content',
    'link_title' => 'Manage content',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_view-contents:admin/content',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -49,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_content:admin/content',
  );
  // Exported menu link: menu-command-center-menu_view-datasets:dataset
  $menu_links['menu-command-center-menu_view-datasets:dataset'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'dataset',
    'router_path' => 'dataset',
    'link_title' => 'View datasets',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_view-datasets:dataset',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -49,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_datasets:dataset',
  );
  // Exported menu link: menu-command-center-menu_view-groups:groups
  $menu_links['menu-command-center-menu_view-groups:groups'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'groups',
    'router_path' => 'groups',
    'link_title' => 'View groups',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_view-groups:groups',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -49,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_groups:groups',
  );
  // Exported menu link: menu-command-center-menu_visualizations:admin/structure/entity-type/visualization
  $menu_links['menu-command-center-menu_visualizations:admin/structure/entity-type/visualization'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/structure/entity-type/visualization',
    'router_path' => 'admin/structure/entity-type/visualization',
    'link_title' => 'Visualizations',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_visualizations:admin/structure/entity-type/visualization',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 1,
    'expanded' => 0,
    'weight' => -46,
    'customized' => 1,
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('Add chart');
  t('Add choropleth');
  t('Add content');
  t('Add dataset');
  t('Add group');
  t('Content');
  t('Datasets');
  t('Groups');
  t('Profile');
  t('Profile Settings');
  t('Site Preferences');
  t('Users');
  t('View all');
  t('View charts');
  t('View choropleth');
  t('View contents');
  t('View datasets');
  t('View groups');
  t('Visualizations');

  return $menu_links;
}
