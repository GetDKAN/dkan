<?php

/**
 * @file
 * dkan_sitewide_menu.features.menu_links.inc
 * @codingStandardsIgnoreStart
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
    'weight' => -50,
    'customized' => 1,
  );
  // Exported menu link: menu-command-center-menu_appearance:admin/appearance/settings.
  $menu_links['menu-command-center-menu_appearance:admin/appearance/settings'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/appearance/settings',
    'router_path' => 'admin/appearance/settings',
    'link_title' => 'Appearance',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_appearance:admin/appearance/settings',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 1,
    'expanded' => 0,
    'weight' => -46,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_site-configuration:admin/config',
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
    'weight' => -38,
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
    'parent_identifier' => 'menu-command-center-menu_appearance:admin/appearance/settings',
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
    'weight' => -49,
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
    'weight' => -50,
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
    'weight' => -45,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_add-content:node/add',
  );
  // Exported menu link: menu-command-center-menu_data-dashboards:admin/dkan/data-dashboards.
  $menu_links['menu-command-center-menu_data-dashboards:admin/dkan/data-dashboards'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/dkan/data-dashboards',
    'router_path' => 'admin/dkan/data-dashboards',
    'link_title' => 'Data Dashboards',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_data-dashboards:admin/dkan/data-dashboards',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -50,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_dkan:admin/dkan',
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
    'weight' => -49,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_dkan:admin/dkan',
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
    'weight' => -46,
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
  // Exported menu link: menu-command-center-menu_datasets:admin/dkan/harvest/dashboard/datasets.
  $menu_links['menu-command-center-menu_datasets:admin/dkan/harvest/dashboard/datasets'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/dkan/harvest/dashboard/datasets',
    'router_path' => 'admin/dkan/harvest/dashboard/datasets',
    'link_title' => 'Datasets',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_datasets:admin/dkan/harvest/dashboard/datasets',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -50,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_dkan-harvest-dashboard:admin/dkan/harvest/dashboard',
  );
  // Exported menu link: menu-command-center-menu_dcat-validation:admin/config/services/odsm/validate/dcat.
  $menu_links['menu-command-center-menu_dcat-validation:admin/config/services/odsm/validate/dcat'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/config/services/odsm/validate/dcat',
    'router_path' => 'admin/config/services/odsm/validate/dcat',
    'link_title' => 'DCAT validation',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_dcat-validation:admin/config/services/odsm/validate/dcat',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -49,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_open-data-schema-mapper:admin/config/services/odsm',
  );
  // Exported menu link: menu-command-center-menu_dkan-dataset-api:admin/dkan/dataset_api.
  $menu_links['menu-command-center-menu_dkan-dataset-api:admin/dkan/dataset_api'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/dkan/dataset_api',
    'router_path' => 'admin/dkan/dataset_api',
    'link_title' => 'DKAN Dataset API',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_dkan-dataset-api:admin/dkan/dataset_api',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -47,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_dkan:admin/dkan',
  );
  // Exported menu link: menu-command-center-menu_dkan-dataset-forms:admin/dkan/dataset_forms.
  $menu_links['menu-command-center-menu_dkan-dataset-forms:admin/dkan/dataset_forms'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/dkan/dataset_forms',
    'router_path' => 'admin/dkan/dataset_forms',
    'link_title' => 'DKAN Dataset Forms',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_dkan-dataset-forms:admin/dkan/dataset_forms',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -46,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_dkan:admin/dkan',
  );
  // Exported menu link: menu-command-center-menu_dkan-harvest-dashboard:admin/dkan/harvest/dashboard.
  $menu_links['menu-command-center-menu_dkan-harvest-dashboard:admin/dkan/harvest/dashboard'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/dkan/harvest/dashboard',
    'router_path' => 'admin/dkan/harvest/dashboard',
    'link_title' => 'DKAN Harvest Dashboard',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_dkan-harvest-dashboard:admin/dkan/harvest/dashboard',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 1,
    'expanded' => 1,
    'weight' => -45,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_dkan:admin/dkan',
  );
  // Exported menu link: menu-command-center-menu_dkan:admin/dkan.
  $menu_links['menu-command-center-menu_dkan:admin/dkan'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/dkan',
    'router_path' => 'admin/dkan',
    'link_title' => 'DKAN',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_dkan:admin/dkan',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 1,
    'expanded' => 0,
    'weight' => -46,
    'customized' => 1,
  );
  // Exported menu link: menu-command-center-menu_featured-groups-sort-order:admin/dkan/featured-groups-sort-order.
  $menu_links['menu-command-center-menu_featured-groups-sort-order:admin/dkan/featured-groups-sort-order'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/dkan/featured-groups-sort-order',
    'router_path' => 'admin/dkan/featured-groups-sort-order',
    'link_title' => 'Featured Groups Sort Order',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_featured-groups-sort-order:admin/dkan/featured-groups-sort-order',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -44,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_dkan:admin/dkan',
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
    'weight' => -48,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_appearance:admin/appearance/settings',
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
  // Exported menu link: menu-command-center-menu_harvest-source:node/add/harvest-source.
  $menu_links['menu-command-center-menu_harvest-source:node/add/harvest-source'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'node/add/harvest-source',
    'router_path' => 'node/add/harvest-source',
    'link_title' => 'Harvest Source',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_harvest-source:node/add/harvest-source',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -44,
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
    'weight' => -49,
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
    'weight' => -49,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_site-configuration:admin/config',
  );
  // Exported menu link: menu-command-center-menu_open-data-schema-mapper:admin/config/services/odsm.
  $menu_links['menu-command-center-menu_open-data-schema-mapper:admin/config/services/odsm'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/config/services/odsm',
    'router_path' => 'admin/config/services/odsm',
    'link_title' => 'Open Data Schema Mapper',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_open-data-schema-mapper:admin/config/services/odsm',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 1,
    'expanded' => 0,
    'weight' => -47,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_site-configuration:admin/config',
  );
  // Exported menu link: menu-command-center-menu_page:node/add/page.
  $menu_links['menu-command-center-menu_page:node/add/page'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'node/add/page',
    'router_path' => 'node/add/page',
    'link_title' => 'Page',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_page:node/add/page',
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
    'weight' => -47,
    'customized' => 1,
  );
  // Exported menu link: menu-command-center-menu_project-open-data-validation:admin/config/services/odsm/validate/pod.
  $menu_links['menu-command-center-menu_project-open-data-validation:admin/config/services/odsm/validate/pod'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/config/services/odsm/validate/pod',
    'router_path' => 'admin/config/services/odsm/validate/pod',
    'link_title' => 'Project Open Data Validation',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_project-open-data-validation:admin/config/services/odsm/validate/pod',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -48,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_open-data-schema-mapper:admin/config/services/odsm',
  );
  // Exported menu link: menu-command-center-menu_recline-configuration:admin/dkan/recline.
  $menu_links['menu-command-center-menu_recline-configuration:admin/dkan/recline'] = array(
    'menu_name' => 'menu-command-center-menu',
    'link_path' => 'admin/dkan/recline',
    'router_path' => 'admin/dkan/recline',
    'link_title' => 'Recline Configuration',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'menu-command-center-menu_recline-configuration:admin/dkan/recline',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -43,
    'customized' => 1,
    'parent_identifier' => 'menu-command-center-menu_dkan:admin/dkan',
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
    'weight' => -45,
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
    'weight' => -48,
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
    'weight' => -6,
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
    'weight' => -48,
    'customized' => 1,
  );

  // Translatables
  // Included for use with string extractors like potx.
  t('Add content');
  t('Appearance');
  t('Chart');
  t('Charts');
  t('Colorizer');
  t('Content');
  t('Create user');
  t('DCAT validation');
  t('DKAN');
  t('DKAN Dataset API');
  t('DKAN Dataset Forms');
  t('DKAN Harvest Dashboard');
  t('Data Dashboard');
  t('Data Dashboards');
  t('Data Previews');
  t('Data Story');
  t('Dataset');
  t('Datasets');
  t('Featured Groups Sort Order');
  t('Fonts');
  t('Group');
  t('Harvest Source');
  t('Manage Users');
  t('Menus');
  t('Open Data Schema Mapper');
  t('Page');
  t('People');
  t('Project Open Data Validation');
  t('Recline Configuration');
  t('Resource');
  t('Site Configuration');
  t('Site Information');
  t('Taxonomy');
  t('Visualization');
  t('Visualizations');

  return $menu_links;
}
/* @codingStandardsIgnoreEnd */
