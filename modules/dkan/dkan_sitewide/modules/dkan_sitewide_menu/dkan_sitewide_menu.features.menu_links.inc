<?php
/**
 * @file
 * dkan_sitewide_menu.features.menu_links.inc
 */

/**
 * Implements hook_menu_default_menu_links().
 */
function dkan_sitewide_menu_menu_default_menu_links() {
  $menu_links = array();

  // Exported menu link: menu-command-center-menu_add-content:node/add.
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
    'has_children' => 1,
    'expanded' => 0,
    'weight' => -49,
    'customized' => 1,
  );
  // Exported menu link: menu-command-center-menu_chart:admin/structure/entity-type/visualization/ve_chart/add.
  $menu_links['menu-command-center-menu_chart:admin/structure/entity-type/visualization/ve_chart/add'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/structure/entity-type/visualization/ve_chart/add',
    'router_path' => 'admin/structure/entity-type/visualization/ve_chart/add',
    'link_title' => 'Chart',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_chart:admin/structure/entity-type/visualization/ve_chart/add',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -50,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_visualization:admin/structure/entity-type/visualization/ve_chart/add',
  );
  // Exported menu link: menu-command-center-menu_charts:admin/structure/entity-type/visualization/ve_chart.
  $menu_links['menu-command-center-menu_charts:admin/structure/entity-type/visualization/ve_chart'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/structure/entity-type/visualization/ve_chart',
    'router_path' => 'admin/structure/entity-type/visualization/ve_chart',
    'link_title' => 'Charts',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_charts:admin/structure/entity-type/visualization/ve_chart',
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
  // Exported menu link: menu-command-center-menu_colorizer:admin/appearance/colorizer.
  $menu_links['menu-command-center-menu_colorizer:admin/appearance/colorizer'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/appearance/colorizer',
    'router_path' => 'admin/appearance/colorizer',
    'link_title' => 'Colorizer',
    'options' => array(
      'attributes' => array(
        'title' => 'Customize site colors',
      ),
      'identifier' => 'menu-command-center-menu_colorizer:admin/appearance/colorizer',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -49,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_site-configuration:admin/config',
  );
  // Exported menu link: menu-command-center-menu_content:admin/content.
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
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -48,
    'customized' => 1,
  );
  // Exported menu link: menu-command-center-menu_create-user:admin/people/create.
  $menu_links['menu-command-center-menu_create-user:admin/people/create'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/people/create',
    'router_path' => 'admin/people/create',
    'link_title' => 'Create user',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_create-user:admin/people/create',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => 0,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_people:admin/people',
  );
  // Exported menu link: menu-command-center-menu_data-dashboard:node/add/data-dashboard.
  $menu_links['menu-command-center-menu_data-dashboard:node/add/data-dashboard'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'node/add/data-dashboard',
    'router_path' => 'node/add/data-dashboard',
    'link_title' => 'Data Dashboard',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_data-dashboard:node/add/data-dashboard',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -46,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_add-content:node/add',
  );
  // Exported menu link: menu-command-center-menu_data-previews:admin/dkan/dataset_preview.
  $menu_links['menu-command-center-menu_data-previews:admin/dkan/dataset_preview'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/dkan/dataset_preview',
    'router_path' => 'admin/dkan/dataset_preview',
    'link_title' => 'Data Previews',
    'options' => array(
      'attributes' => array(
        'title' => 'Manage options for local and external DKAN resource previews',
      ),
      'identifier' => 'menu-command-center-menu_data-previews:admin/dkan/dataset_preview',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -48,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_site-configuration:admin/config',
  );
  // Exported menu link: menu-command-center-menu_data-story:node/add/dkan-data-story.
  $menu_links['menu-command-center-menu_data-story:node/add/dkan-data-story'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'node/add/dkan-data-story',
    'router_path' => 'node/add',
    'link_title' => 'Data Story',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_data-story:node/add/dkan-data-story',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -47,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_add-content:node/add',
  );
  // Exported menu link: menu-command-center-menu_dataset:node/add/dataset.
  $menu_links['menu-command-center-menu_dataset:node/add/dataset'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'node/add/dataset',
    'router_path' => 'node/add/dataset',
    'link_title' => 'Dataset',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_dataset:node/add/dataset',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -50,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_add-content:node/add',
  );
  // Exported menu link: menu-command-center-menu_fonts:admin/appearance/fontyourface.
  $menu_links['menu-command-center-menu_fonts:admin/appearance/fontyourface'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/appearance/fontyourface',
    'router_path' => 'admin/appearance/fontyourface',
    'link_title' => 'Fonts',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_fonts:admin/appearance/fontyourface',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -47,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_site-configuration:admin/config',
  );
  // Exported menu link: menu-command-center-menu_group:node/add/group.
  $menu_links['menu-command-center-menu_group:node/add/group'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'node/add/group',
    'router_path' => 'node/add/group',
    'link_title' => 'Group',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_group:node/add/group',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -48,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_add-content:node/add',
  );
  // Exported menu link: menu-command-center-menu_manage-users:admin/people.
  $menu_links['menu-command-center-menu_manage-users:admin/people'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/people',
    'router_path' => 'admin/people',
    'link_title' => 'Manage Users',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_manage-users:admin/people',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => 0,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_people:admin/people',
  );
  // Exported menu link: menu-command-center-menu_menus:admin/structure/menu.
  $menu_links['menu-command-center-menu_menus:admin/structure/menu'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/structure/menu',
    'router_path' => 'admin/structure/menu',
    'link_title' => 'Menus',
    'options' => array(
      'attributes' => array(
        'title' => 'Customize site navigation menus',
      ),
      'identifier' => 'menu-command-center-menu_menus:admin/structure/menu',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -46,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_site-configuration:admin/config',
  );
  // Exported menu link: menu-command-center-menu_my-workbench:admin/workbench.
  $menu_links['menu-command-center-menu_my-workbench:admin/workbench'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/workbench',
    'router_path' => 'admin',
    'link_title' => 'My Workbench',
    'options' => array(
      'identifier' => 'menu-command-center-menu_my-workbench:admin/workbench',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -50,
    'customized' => 0,
  );
  // Exported menu link: menu-command-center-menu_people:admin/people.
  $menu_links['menu-command-center-menu_people:admin/people'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/people',
    'router_path' => 'admin/people',
    'link_title' => 'People',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_people:admin/people',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 1,
    'expanded' => 0,
    'weight' => -45,
    'customized' => 1,
  );
  // Exported menu link: menu-command-center-menu_resource:node/add/resource.
  $menu_links['menu-command-center-menu_resource:node/add/resource'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'node/add/resource',
    'router_path' => 'node/add/resource',
    'link_title' => 'Resource',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_resource:node/add/resource',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -49,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_add-content:node/add',
  );
  // Exported menu link: menu-command-center-menu_site-configuration:admin/config.
  $menu_links['menu-command-center-menu_site-configuration:admin/config'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/config',
    'router_path' => 'admin/config',
    'link_title' => 'Site Configuration',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_site-configuration:admin/config',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 1,
    'expanded' => 0,
    'weight' => -44,
    'customized' => 1,
  );
  // Exported menu link: menu-command-center-menu_site-information:admin/config/system/site-information.
  $menu_links['menu-command-center-menu_site-information:admin/config/system/site-information'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/config/system/site-information',
    'router_path' => 'admin/config/system/site-information',
    'link_title' => 'Site Information',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_site-information:admin/config/system/site-information',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -50,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_site-configuration:admin/config',
  );
  // Exported menu link: menu-command-center-menu_taxonomy:admin/structure/taxonomy.
  $menu_links['menu-command-center-menu_taxonomy:admin/structure/taxonomy'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/structure/taxonomy',
    'router_path' => 'admin/structure/taxonomy',
    'link_title' => 'Taxonomy',
    'options' => array(
      'attributes' => array(
        'title' => 'Manage tags, topics and other categorization',
      ),
      'identifier' => 'menu-command-center-menu_taxonomy:admin/structure/taxonomy',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -45,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_site-configuration:admin/config',
  );
  // Exported menu link: menu-command-center-menu_theme-settings:admin/appearance/settings.
  $menu_links['menu-command-center-menu_theme-settings:admin/appearance/settings'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/appearance/settings',
    'router_path' => 'admin/appearance/settings',
    'link_title' => 'Theme Settings',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_theme-settings:admin/appearance/settings',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -44,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_site-configuration:admin/config',
  );
  // Exported menu link: menu-command-center-menu_visualization:admin/structure/entity-type/visualization/ve_chart/add.
  $menu_links['menu-command-center-menu_visualization:admin/structure/entity-type/visualization/ve_chart/add'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/structure/entity-type/visualization/ve_chart/add',
    'router_path' => 'admin/structure/entity-type/visualization/ve_chart/add',
    'link_title' => 'Visualization',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_visualization:admin/structure/entity-type/visualization/ve_chart/add',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 1,
    'expanded' => 0,
    'weight' => -45,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_add-content:node/add',
  );
  // Exported menu link: menu-command-center-menu_visualizations:admin/structure/entity-type/visualization.
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
  t('Add content');
  t('Chart');
  t('Charts');
  t('Colorizer');
  t('Content');
  t('Create user');
  t('Data Dashboard');
  t('Data Previews');
  t('Data Story');
  t('Dataset');
  t('Fonts');
  t('Group');
  t('Manage Users');
  t('Menus');
  t('My Workbench');
  t('People');
  t('Resource');
  t('Site Configuration');
  t('Site Information');
  t('Taxonomy');
  t('Theme Settings');
  t('Visualization');
  t('Visualizations');

  return $menu_links;
}
