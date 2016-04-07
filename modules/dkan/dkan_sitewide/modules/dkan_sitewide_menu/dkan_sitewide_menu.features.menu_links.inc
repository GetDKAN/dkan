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
    'weight' => -49,
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
    'weight' => -47,
    'customized' => 1,
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
    'has_children' => 1,
    'expanded' => 0,
    'weight' => -49,
    'customized' => 1,
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
    'weight' => -48,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_add-content:node/add',
  );
  // Exported menu link: menu-command-center-menu_charts:admin/structure/entity-type/visualization/ve_chart
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
  // Exported menu link: menu-command-center-menu_charts:admin/structure/entity-type/visualization/ve_chart/add
  $menu_links['menu-command-center-menu_charts:admin/structure/entity-type/visualization/ve_chart/add'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/structure/entity-type/visualization/ve_chart/add',
    'router_path' => 'admin/structure/entity-type/visualization/ve_chart/add',
    'link_title' => 'Charts',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_charts:admin/structure/entity-type/visualization/ve_chart/add',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -50,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_visualizations:admin/structure/entity-type/visualization/ve_chart/add',
  );
  // Exported menu link: menu-command-center-menu_choropleth:admin/structure/entity-type/visualization/choropleth_visualization
  $menu_links['menu-command-center-menu_choropleth:admin/structure/entity-type/visualization/choropleth_visualization'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/structure/entity-type/visualization/choropleth_visualization',
    'router_path' => 'admin/structure/entity-type/visualization/choropleth_visualization',
    'link_title' => 'Choropleth',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_choropleth:admin/structure/entity-type/visualization/choropleth_visualization',
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
  // Exported menu link: menu-command-center-menu_choropleth:admin/structure/entity-type/visualization/choropleth_visualization/add
  $menu_links['menu-command-center-menu_choropleth:admin/structure/entity-type/visualization/choropleth_visualization/add'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/structure/entity-type/visualization/choropleth_visualization/add',
    'router_path' => 'admin/structure/entity-type/visualization/choropleth_visualization/add',
    'link_title' => 'Choropleth',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_choropleth:admin/structure/entity-type/visualization/choropleth_visualization/add',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -47,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_visualizations:admin/structure/entity-type/visualization/ve_chart/add',
  );
  // Exported menu link: menu-command-center-menu_colorizer:admin/appearance/colorizer
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
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -48,
    'customized' => 1,
  );
  // Exported menu link: menu-command-center-menu_create-user:admin/people/create
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
  // Exported menu link: menu-command-center-menu_data-dashboard:node/add/data-dashboard
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
  // Exported menu link: menu-command-center-menu_data-previews:admin/dkan/dataset_preview
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
  // Exported menu link: menu-command-center-menu_data-story:node/add/asdas
  $menu_links['menu-command-center-menu_data-story:node/add/asdas'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'node/add/asdas',
    'router_path' => 'node/add',
    'link_title' => 'Data Story',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_data-story:node/add/asdas',
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
  // Exported menu link: menu-command-center-menu_dataset:node/add/dataset
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
  // Exported menu link: menu-command-center-menu_fonts:admin/appearance/fontyourface
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
  // Exported menu link: menu-command-center-menu_geojson:admin/structure/entity-type/geo_file/geojson/add
  $menu_links['menu-command-center-menu_geojson:admin/structure/entity-type/geo_file/geojson/add'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/structure/entity-type/geo_file/geojson/add',
    'router_path' => 'admin/structure/entity-type/geo_file/geojson/add',
    'link_title' => 'GeoJSON',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_geojson:admin/structure/entity-type/geo_file/geojson/add',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -49,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_visualizations:admin/structure/entity-type/visualization/ve_chart/add',
  );
  // Exported menu link: menu-command-center-menu_geojson:admin/structure/entity-type/visualization/geojson_visualization
  $menu_links['menu-command-center-menu_geojson:admin/structure/entity-type/visualization/geojson_visualization'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/structure/entity-type/visualization/geojson_visualization',
    'router_path' => 'admin/structure/entity-type/visualization/geojson_visualization',
    'link_title' => 'GeoJSON',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_geojson:admin/structure/entity-type/visualization/geojson_visualization',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => 0,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_visualizations:admin/structure/entity-type/visualization',
  );
  // Exported menu link: menu-command-center-menu_manage-users:admin/people
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
  // Exported menu link: menu-command-center-menu_menus:admin/structure/menu
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
  // Exported menu link: menu-command-center-menu_my-workbench:admin/workbench
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
  // Exported menu link: menu-command-center-menu_people:admin/people
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
  // Exported menu link: menu-command-center-menu_resource:node/add/resource
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
  // Exported menu link: menu-command-center-menu_site-configuration:admin/config
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
  // Exported menu link: menu-command-center-menu_site-information:admin/config/system/site-information
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
  // Exported menu link: menu-command-center-menu_taxonomy:admin/structure/taxonomy
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
  // Exported menu link: menu-command-center-menu_theme-settings:admin/appearance/settings
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
  // Exported menu link: menu-command-center-menu_visualizations:admin/structure/entity-type/visualization/ve_chart/add
  $menu_links['menu-command-center-menu_visualizations:admin/structure/entity-type/visualization/ve_chart/add'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/structure/entity-type/visualization/ve_chart/add',
    'router_path' => 'admin/structure/entity-type/visualization/ve_chart/add',
    'link_title' => 'Visualizations',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_visualizations:admin/structure/entity-type/visualization/ve_chart/add',
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

  // Translatables
  // Included for use with string extractors like potx.
  t('About');
  t('Add content');
  t('Add dataset');
  t('Add group');
  t('Charts');
  t('Choropleth');
  t('Colorizer');
  t('Content');
  t('Create user');
  t('Data Dashboard');
  t('Data Previews');
  t('Data Story');
  t('Dataset');
  t('Datasets');
  t('Fonts');
  t('GeoJSON');
  t('Manage Users');
  t('Menus');
  t('My Workbench');
  t('People');
  t('Resource');
  t('Site Configuration');
  t('Site Information');
  t('Taxonomy');
  t('Theme Settings');
  t('Visualizations');

  return $menu_links;
}
