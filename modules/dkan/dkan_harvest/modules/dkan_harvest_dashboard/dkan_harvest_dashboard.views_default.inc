<?php
/**
 * @file
 * dkan_harvest_dashboard.views_default.inc
 */

/**
 * Implements hook_views_default_views().
 */
function dkan_harvest_dashboard_views_default_views() {
  $export = array();

  $view = new view();
  $view->name = 'dkan_harvest_dashboard';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'node';
  $view->human_name = 'DKAN Harvest Dashboard';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'Harvest Dashboard';
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'perm';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '10';
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'views_bulk_operations' => 'views_bulk_operations',
    'title' => 'title',
    'field_dkan_harveset_type' => 'field_dkan_harveset_type',
    'dkan_harvest_harvest_date' => 'dkan_harvest_harvest_date',
    'dkan_harvest_harvest_count' => 'dkan_harvest_harvest_count',
    'dkan_harvest_harvest_status' => 'dkan_harvest_harvest_status',
  );
  $handler->display->display_options['style_options']['default'] = '-1';
  $handler->display->display_options['style_options']['info'] = array(
    'views_bulk_operations' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'title' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_dkan_harveset_type' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'dkan_harvest_harvest_date' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'dkan_harvest_harvest_count' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'dkan_harvest_harvest_status' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
  );
  /* Field: Bulk operations: Content */
  $handler->display->display_options['fields']['views_bulk_operations']['id'] = 'views_bulk_operations';
  $handler->display->display_options['fields']['views_bulk_operations']['table'] = 'node';
  $handler->display->display_options['fields']['views_bulk_operations']['field'] = 'views_bulk_operations';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['display_type'] = '0';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['enable_select_all_pages'] = 1;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['row_clickable'] = 1;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['force_single'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['entity_load_capacity'] = '10';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_operations'] = array(
    'rules_component::rules_dkan_harvest_source_cache' => array(
      'selected' => 1,
      'postpone_processing' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::views_bulk_operations_delete_item' => array(
      'selected' => 1,
      'postpone_processing' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'rules_component::rules_dkan_harvest_source_cache_and_migrate' => array(
      'selected' => 1,
      'postpone_processing' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'rules_component::rules_dkan_harvest_source_migrate' => array(
      'selected' => 1,
      'postpone_processing' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
  );
  /* Field: Content: Title */
  $handler->display->display_options['fields']['title']['id'] = 'title';
  $handler->display->display_options['fields']['title']['table'] = 'node';
  $handler->display->display_options['fields']['title']['field'] = 'title';
  $handler->display->display_options['fields']['title']['alter']['word_boundary'] = FALSE;
  $handler->display->display_options['fields']['title']['alter']['ellipsis'] = FALSE;
  /* Field: Content: Type */
  $handler->display->display_options['fields']['field_dkan_harveset_type']['id'] = 'field_dkan_harveset_type';
  $handler->display->display_options['fields']['field_dkan_harveset_type']['table'] = 'field_data_field_dkan_harveset_type';
  $handler->display->display_options['fields']['field_dkan_harveset_type']['field'] = 'field_dkan_harveset_type';
  /* Field: Dkan Harvest: Harvest Migrate Last Updated */
  $handler->display->display_options['fields']['dkan_harvest_harvest_date']['id'] = 'dkan_harvest_harvest_date';
  $handler->display->display_options['fields']['dkan_harvest_harvest_date']['table'] = 'dkan_harvest_dashboard';
  $handler->display->display_options['fields']['dkan_harvest_harvest_date']['field'] = 'dkan_harvest_harvest_date';
  $handler->display->display_options['fields']['dkan_harvest_harvest_date']['label'] = 'Last Updated';
  $handler->display->display_options['fields']['dkan_harvest_harvest_date']['empty'] = 'N/A';
  $handler->display->display_options['fields']['dkan_harvest_harvest_date']['date_format'] = 'time ago';
  $handler->display->display_options['fields']['dkan_harvest_harvest_date']['second_date_format'] = 'long';
  /* Field: Dkan Harvest: Harvest Migrate Datasets Count */
  $handler->display->display_options['fields']['dkan_harvest_harvest_count']['id'] = 'dkan_harvest_harvest_count';
  $handler->display->display_options['fields']['dkan_harvest_harvest_count']['table'] = 'dkan_harvest_dashboard';
  $handler->display->display_options['fields']['dkan_harvest_harvest_count']['field'] = 'dkan_harvest_harvest_count';
  $handler->display->display_options['fields']['dkan_harvest_harvest_count']['label'] = 'Datasets Count';
  /* Field: Dkan Harvest: Harvest Migrate Status */
  $handler->display->display_options['fields']['dkan_harvest_harvest_status']['id'] = 'dkan_harvest_harvest_status';
  $handler->display->display_options['fields']['dkan_harvest_harvest_status']['table'] = 'dkan_harvest_dashboard';
  $handler->display->display_options['fields']['dkan_harvest_harvest_status']['field'] = 'dkan_harvest_harvest_status';
  $handler->display->display_options['fields']['dkan_harvest_harvest_status']['label'] = 'Status';
  $handler->display->display_options['fields']['dkan_harvest_harvest_status']['element_class'] = '[dkan_harvest_harvest_status]';
  $handler->display->display_options['fields']['dkan_harvest_harvest_status']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['dkan_harvest_harvest_status']['element_wrapper_class'] = '[dkan_harvest_harvest_status]';
  $handler->display->display_options['fields']['dkan_harvest_harvest_status']['type'] = 'custom';
  $handler->display->display_options['fields']['dkan_harvest_harvest_status']['type_custom_true'] = 'ERROR';
  $handler->display->display_options['fields']['dkan_harvest_harvest_status']['type_custom_false'] = 'OK';
  $handler->display->display_options['fields']['dkan_harvest_harvest_status']['not'] = 0;
  /* Filter criterion: Content: Published */
  $handler->display->display_options['filters']['status']['id'] = 'status';
  $handler->display->display_options['filters']['status']['table'] = 'node';
  $handler->display->display_options['filters']['status']['field'] = 'status';
  $handler->display->display_options['filters']['status']['value'] = 1;
  $handler->display->display_options['filters']['status']['group'] = 1;
  $handler->display->display_options['filters']['status']['expose']['operator'] = FALSE;
  /* Filter criterion: Content: Type */
  $handler->display->display_options['filters']['type']['id'] = 'type';
  $handler->display->display_options['filters']['type']['table'] = 'node';
  $handler->display->display_options['filters']['type']['field'] = 'type';
  $handler->display->display_options['filters']['type']['value'] = array(
    'harvest_source' => 'harvest_source',
  );

  /* Display: Harvest Dashboard */
  $handler = $view->new_display('panel_pane', 'Harvest Dashboard', 'harvest_sources');
  $export['dkan_harvest_dashboard'] = $view;

  $view = new view();
  $view->name = 'dkan_harvest_datasets';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'node';
  $view->human_name = 'DKAN Harvested Datasets';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'Harvested Datasets';
  $handler->display->display_options['use_ajax'] = TRUE;
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'perm';
  $handler->display->display_options['access']['perm'] = 'admininster harvest';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'better_exposed_filters';
  $handler->display->display_options['exposed_form']['options']['bef'] = array(
    'general' => array(
      'input_required' => 0,
      'text_input_required' => array(
        'text_input_required' => array(
          'value' => 'Select any filter and click on Apply to see results',
          'format' => 'html',
        ),
      ),
      'allow_secondary' => 0,
      'secondary_label' => 'Advanced options',
      'secondary_collapse_override' => '0',
    ),
    'field_harvest_source_issued_value' => array(
      'bef_format' => 'default',
      'more_options' => array(
        'autosubmit' => 0,
        'is_secondary' => 0,
        'any_label' => '',
        'bef_filter_description' => '',
        'tokens' => array(
          'available' => array(
            0 => 'global_types',
          ),
        ),
        'rewrite' => array(
          'filter_rewrite_values' => '',
        ),
        'datepicker_options' => '',
      ),
    ),
    'field_harvest_souce_modified_value' => array(
      'bef_format' => 'default',
      'more_options' => array(
        'autosubmit' => 0,
        'is_secondary' => 0,
        'any_label' => '',
        'bef_filter_description' => '',
        'tokens' => array(
          'available' => array(
            0 => 'global_types',
          ),
        ),
        'rewrite' => array(
          'filter_rewrite_values' => '',
        ),
        'datepicker_options' => '',
      ),
    ),
    'field_orphan_value' => array(
      'bef_format' => 'default',
      'more_options' => array(
        'bef_select_all_none' => FALSE,
        'bef_collapsible' => 0,
        'autosubmit' => 0,
        'is_secondary' => 0,
        'any_label' => '',
        'bef_filter_description' => '',
        'tokens' => array(
          'available' => array(
            0 => 'global_types',
          ),
        ),
        'rewrite' => array(
          'filter_rewrite_values' => 'On|Orphans only
Off|Non-orphans only',
        ),
      ),
    ),
    'field_harvest_source_ref_target_id_1' => array(
      'bef_format' => 'default',
      'more_options' => array(
        'bef_select_all_none' => FALSE,
        'bef_collapsible' => 0,
        'autosubmit' => 0,
        'is_secondary' => 0,
        'any_label' => '',
        'bef_filter_description' => '',
        'tokens' => array(
          'available' => array(
            0 => 'global_types',
          ),
        ),
        'rewrite' => array(
          'filter_rewrite_values' => '',
        ),
      ),
    ),
  );
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'nid' => 'nid',
    'field_harvest_source_ref' => 'field_harvest_source_ref',
    'og_group_ref' => 'og_group_ref',
    'title' => 'title',
    'created' => 'created',
    'changed' => 'changed',
    'dkan_harvest_orphan' => 'dkan_harvest_orphan',
  );
  $handler->display->display_options['style_options']['default'] = '-1';
  $handler->display->display_options['style_options']['info'] = array(
    'nid' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_harvest_source_ref' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'og_group_ref' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'title' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'created' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'changed' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'dkan_harvest_orphan' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
  );
  /* No results behavior: Global: Text area */
  $handler->display->display_options['empty']['area']['id'] = 'area';
  $handler->display->display_options['empty']['area']['table'] = 'views';
  $handler->display->display_options['empty']['area']['field'] = 'area';
  $handler->display->display_options['empty']['area']['empty'] = TRUE;
  $handler->display->display_options['empty']['area']['content'] = 'No harvested datasets were found.';
  $handler->display->display_options['empty']['area']['format'] = 'html';
  /* Field: Bulk operations: Content */
  $handler->display->display_options['fields']['views_bulk_operations']['id'] = 'views_bulk_operations';
  $handler->display->display_options['fields']['views_bulk_operations']['table'] = 'node';
  $handler->display->display_options['fields']['views_bulk_operations']['field'] = 'views_bulk_operations';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['display_type'] = '0';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['enable_select_all_pages'] = 1;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['row_clickable'] = 1;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['force_single'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['entity_load_capacity'] = '10';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_operations'] = array(
    'action::views_bulk_operations_delete_item' => array(
      'selected' => 1,
      'postpone_processing' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::node_unpublish_action' => array(
      'selected' => 1,
      'postpone_processing' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
  );
  /* Field: Content: Nid */
  $handler->display->display_options['fields']['nid']['id'] = 'nid';
  $handler->display->display_options['fields']['nid']['table'] = 'node';
  $handler->display->display_options['fields']['nid']['field'] = 'nid';
  $handler->display->display_options['fields']['nid']['label'] = '';
  $handler->display->display_options['fields']['nid']['exclude'] = TRUE;
  $handler->display->display_options['fields']['nid']['element_label_colon'] = FALSE;
  /* Field: Content: Publisher */
  $handler->display->display_options['fields']['og_group_ref']['id'] = 'og_group_ref';
  $handler->display->display_options['fields']['og_group_ref']['table'] = 'og_membership';
  $handler->display->display_options['fields']['og_group_ref']['field'] = 'og_group_ref';
  $handler->display->display_options['fields']['og_group_ref']['label'] = 'Group';
  $handler->display->display_options['fields']['og_group_ref']['settings'] = array(
    'link' => 1,
  );
  $handler->display->display_options['fields']['og_group_ref']['delta_offset'] = '0';
  /* Field: Content: Title */
  $handler->display->display_options['fields']['title']['id'] = 'title';
  $handler->display->display_options['fields']['title']['table'] = 'node';
  $handler->display->display_options['fields']['title']['field'] = 'title';
  $handler->display->display_options['fields']['title']['alter']['word_boundary'] = FALSE;
  $handler->display->display_options['fields']['title']['alter']['ellipsis'] = FALSE;
  /* Field: Content: Harvest Source Issued */
  $handler->display->display_options['fields']['field_harvest_source_issued']['id'] = 'field_harvest_source_issued';
  $handler->display->display_options['fields']['field_harvest_source_issued']['table'] = 'field_data_field_harvest_source_issued';
  $handler->display->display_options['fields']['field_harvest_source_issued']['field'] = 'field_harvest_source_issued';
  $handler->display->display_options['fields']['field_harvest_source_issued']['settings'] = array(
    'format_type' => 'iso_8601_date',
    'fromto' => 'both',
    'multiple_number' => '',
    'multiple_from' => '',
    'multiple_to' => '',
    'show_remaining_days' => 0,
  );
  /* Field: Content: Harvest Source Modified */
  $handler->display->display_options['fields']['field_harvest_souce_modified']['id'] = 'field_harvest_souce_modified';
  $handler->display->display_options['fields']['field_harvest_souce_modified']['table'] = 'field_data_field_harvest_souce_modified';
  $handler->display->display_options['fields']['field_harvest_souce_modified']['field'] = 'field_harvest_souce_modified';
  $handler->display->display_options['fields']['field_harvest_souce_modified']['settings'] = array(
    'format_type' => 'iso_8601_date',
    'fromto' => 'both',
    'multiple_number' => '',
    'multiple_from' => '',
    'multiple_to' => '',
    'show_remaining_days' => 0,
  );
  /* Field: Content: Orphan */
  $handler->display->display_options['fields']['field_orphan']['id'] = 'field_orphan';
  $handler->display->display_options['fields']['field_orphan']['table'] = 'field_data_field_orphan';
  $handler->display->display_options['fields']['field_orphan']['field'] = 'field_orphan';
  $handler->display->display_options['fields']['field_orphan']['type'] = 'list_key';
  /* Field: Content: Published */
  $handler->display->display_options['fields']['status']['id'] = 'status';
  $handler->display->display_options['fields']['status']['table'] = 'node';
  $handler->display->display_options['fields']['status']['field'] = 'status';
  $handler->display->display_options['fields']['status']['not'] = 0;
  /* Sort criterion: Content: Post date */
  $handler->display->display_options['sorts']['created']['id'] = 'created';
  $handler->display->display_options['sorts']['created']['table'] = 'node';
  $handler->display->display_options['sorts']['created']['field'] = 'created';
  $handler->display->display_options['sorts']['created']['order'] = 'DESC';
  /* Filter criterion: Content: Type */
  $handler->display->display_options['filters']['type']['id'] = 'type';
  $handler->display->display_options['filters']['type']['table'] = 'node';
  $handler->display->display_options['filters']['type']['field'] = 'type';
  $handler->display->display_options['filters']['type']['value'] = array(
    'dataset' => 'dataset',
  );
  $handler->display->display_options['filters']['type']['group'] = 1;
  /* Filter criterion: Content: Harvest Source (field_harvest_source_ref) */
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id']['id'] = 'field_harvest_source_ref_target_id';
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id']['table'] = 'field_data_field_harvest_source_ref';
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id']['field'] = 'field_harvest_source_ref_target_id';
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id']['operator'] = 'not empty';
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id']['value'] = array(
    'min' => '',
    'max' => '',
    'value' => '',
  );
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id']['group'] = 1;
  /* Filter criterion: Content: Harvest Source Issued (field_harvest_source_issued) */
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['id'] = 'field_harvest_source_issued_value';
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['table'] = 'field_data_field_harvest_source_issued';
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['field'] = 'field_harvest_source_issued_value';
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['operator'] = 'between';
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['group'] = 1;
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['exposed'] = TRUE;
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['expose']['operator_id'] = 'field_harvest_source_issued_value_op';
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['expose']['label'] = 'Harvest Source Issued';
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['expose']['operator'] = 'field_harvest_source_issued_value_op';
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['expose']['identifier'] = 'field_harvest_source_issued_value';
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['expose']['remember_roles'] = array(
    2 => '2',
    3 => 0,
    1 => 0,
    5 => 0,
    4 => 0,
    6 => 0,
  );
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['form_type'] = 'date_popup';
  /* Filter criterion: Content: Harvest Source Modified (field_harvest_souce_modified) */
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['id'] = 'field_harvest_souce_modified_value';
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['table'] = 'field_data_field_harvest_souce_modified';
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['field'] = 'field_harvest_souce_modified_value';
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['operator'] = 'between';
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['group'] = 1;
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['exposed'] = TRUE;
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['expose']['operator_id'] = 'field_harvest_souce_modified_value_op';
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['expose']['label'] = 'Harvest Source Modified';
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['expose']['operator'] = 'field_harvest_souce_modified_value_op';
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['expose']['identifier'] = 'field_harvest_souce_modified_value';
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['expose']['remember_roles'] = array(
    2 => '2',
    3 => 0,
    1 => 0,
    5 => 0,
    4 => 0,
    6 => 0,
  );
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['form_type'] = 'date_popup';
  /* Filter criterion: Content: Orphan (field_orphan) */
  $handler->display->display_options['filters']['field_orphan_value']['id'] = 'field_orphan_value';
  $handler->display->display_options['filters']['field_orphan_value']['table'] = 'field_data_field_orphan';
  $handler->display->display_options['filters']['field_orphan_value']['field'] = 'field_orphan_value';
  $handler->display->display_options['filters']['field_orphan_value']['value'] = array(
    'all' => 'all',
    0 => '0',
    1 => '1',
  );
  $handler->display->display_options['filters']['field_orphan_value']['group'] = 1;
  $handler->display->display_options['filters']['field_orphan_value']['exposed'] = TRUE;
  $handler->display->display_options['filters']['field_orphan_value']['expose']['operator_id'] = 'field_orphan_value_op';
  $handler->display->display_options['filters']['field_orphan_value']['expose']['label'] = 'Orphan Status';
  $handler->display->display_options['filters']['field_orphan_value']['expose']['operator'] = 'field_orphan_value_op';
  $handler->display->display_options['filters']['field_orphan_value']['expose']['identifier'] = 'field_orphan_value';
  $handler->display->display_options['filters']['field_orphan_value']['expose']['remember_roles'] = array(
    2 => '2',
    3 => 0,
    1 => 0,
    5 => 0,
    4 => 0,
    6 => 0,
  );
  $handler->display->display_options['filters']['field_orphan_value']['expose']['reduce'] = TRUE;

  /* Display: Harvested Datasets */
  $handler = $view->new_display('panel_pane', 'Harvested Datasets', 'harvest_datasets');
  $handler->display->display_options['defaults']['style_plugin'] = FALSE;
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'views_bulk_operations' => 'views_bulk_operations',
    'nid' => 'nid',
    'og_group_ref' => 'og_group_ref',
    'title' => 'title',
    'field_harvest_source_issued' => 'field_harvest_source_issued',
    'field_harvest_souce_modified' => 'field_harvest_souce_modified',
    'field_orphan' => 'field_orphan',
    'status' => 'status',
    'field_harvest_source_ref' => 'field_harvest_source_ref',
  );
  $handler->display->display_options['style_options']['default'] = '-1';
  $handler->display->display_options['style_options']['info'] = array(
    'views_bulk_operations' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'nid' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'og_group_ref' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'title' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_harvest_source_issued' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_harvest_souce_modified' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_orphan' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'status' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_harvest_source_ref' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
  );
  $handler->display->display_options['defaults']['style_options'] = FALSE;
  $handler->display->display_options['defaults']['row_plugin'] = FALSE;
  $handler->display->display_options['defaults']['row_options'] = FALSE;
  $handler->display->display_options['defaults']['fields'] = FALSE;
  /* Field: Bulk operations: Content */
  $handler->display->display_options['fields']['views_bulk_operations']['id'] = 'views_bulk_operations';
  $handler->display->display_options['fields']['views_bulk_operations']['table'] = 'node';
  $handler->display->display_options['fields']['views_bulk_operations']['field'] = 'views_bulk_operations';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['display_type'] = '0';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['enable_select_all_pages'] = 1;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['row_clickable'] = 1;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['force_single'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['entity_load_capacity'] = '10';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo_operations'] = array(
    'action::views_bulk_operations_delete_item' => array(
      'selected' => 1,
      'postpone_processing' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::node_unpublish_action' => array(
      'selected' => 1,
      'postpone_processing' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
  );
  /* Field: Content: Nid */
  $handler->display->display_options['fields']['nid']['id'] = 'nid';
  $handler->display->display_options['fields']['nid']['table'] = 'node';
  $handler->display->display_options['fields']['nid']['field'] = 'nid';
  $handler->display->display_options['fields']['nid']['label'] = '';
  $handler->display->display_options['fields']['nid']['exclude'] = TRUE;
  $handler->display->display_options['fields']['nid']['element_label_colon'] = FALSE;
  /* Field: Content: Publisher */
  $handler->display->display_options['fields']['og_group_ref']['id'] = 'og_group_ref';
  $handler->display->display_options['fields']['og_group_ref']['table'] = 'og_membership';
  $handler->display->display_options['fields']['og_group_ref']['field'] = 'og_group_ref';
  $handler->display->display_options['fields']['og_group_ref']['label'] = 'Group';
  $handler->display->display_options['fields']['og_group_ref']['settings'] = array(
    'link' => 1,
  );
  $handler->display->display_options['fields']['og_group_ref']['delta_offset'] = '0';
  /* Field: Content: Title */
  $handler->display->display_options['fields']['title']['id'] = 'title';
  $handler->display->display_options['fields']['title']['table'] = 'node';
  $handler->display->display_options['fields']['title']['field'] = 'title';
  $handler->display->display_options['fields']['title']['alter']['word_boundary'] = FALSE;
  $handler->display->display_options['fields']['title']['alter']['ellipsis'] = FALSE;
  /* Field: Content: Harvest Source Issued */
  $handler->display->display_options['fields']['field_harvest_source_issued']['id'] = 'field_harvest_source_issued';
  $handler->display->display_options['fields']['field_harvest_source_issued']['table'] = 'field_data_field_harvest_source_issued';
  $handler->display->display_options['fields']['field_harvest_source_issued']['field'] = 'field_harvest_source_issued';
  $handler->display->display_options['fields']['field_harvest_source_issued']['settings'] = array(
    'format_type' => 'iso_8601_date',
    'fromto' => 'both',
    'multiple_number' => '',
    'multiple_from' => '',
    'multiple_to' => '',
    'show_remaining_days' => 0,
  );
  /* Field: Content: Harvest Source Modified */
  $handler->display->display_options['fields']['field_harvest_souce_modified']['id'] = 'field_harvest_souce_modified';
  $handler->display->display_options['fields']['field_harvest_souce_modified']['table'] = 'field_data_field_harvest_souce_modified';
  $handler->display->display_options['fields']['field_harvest_souce_modified']['field'] = 'field_harvest_souce_modified';
  $handler->display->display_options['fields']['field_harvest_souce_modified']['settings'] = array(
    'format_type' => 'iso_8601_date',
    'fromto' => 'both',
    'multiple_number' => '',
    'multiple_from' => '',
    'multiple_to' => '',
    'show_remaining_days' => 0,
  );
  /* Field: Content: Orphan */
  $handler->display->display_options['fields']['field_orphan']['id'] = 'field_orphan';
  $handler->display->display_options['fields']['field_orphan']['table'] = 'field_data_field_orphan';
  $handler->display->display_options['fields']['field_orphan']['field'] = 'field_orphan';
  $handler->display->display_options['fields']['field_orphan']['type'] = 'list_key';
  /* Field: Content: Published */
  $handler->display->display_options['fields']['status']['id'] = 'status';
  $handler->display->display_options['fields']['status']['table'] = 'node';
  $handler->display->display_options['fields']['status']['field'] = 'status';
  $handler->display->display_options['fields']['status']['not'] = 0;
  /* Field: Content: Harvest Source */
  $handler->display->display_options['fields']['field_harvest_source_ref']['id'] = 'field_harvest_source_ref';
  $handler->display->display_options['fields']['field_harvest_source_ref']['table'] = 'field_data_field_harvest_source_ref';
  $handler->display->display_options['fields']['field_harvest_source_ref']['field'] = 'field_harvest_source_ref';
  $handler->display->display_options['fields']['field_harvest_source_ref']['label'] = 'Source';
  $handler->display->display_options['fields']['field_harvest_source_ref']['settings'] = array(
    'link' => 0,
  );
  $handler->display->display_options['defaults']['filter_groups'] = FALSE;
  $handler->display->display_options['defaults']['filters'] = FALSE;
  /* Filter criterion: Content: Harvest Source Issued (field_harvest_source_issued) */
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['id'] = 'field_harvest_source_issued_value';
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['table'] = 'field_data_field_harvest_source_issued';
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['field'] = 'field_harvest_source_issued_value';
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['operator'] = 'between';
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['group'] = 1;
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['exposed'] = TRUE;
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['expose']['operator_id'] = 'field_harvest_source_issued_value_op';
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['expose']['label'] = 'Harvest Source Issued';
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['expose']['operator'] = 'field_harvest_source_issued_value_op';
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['expose']['identifier'] = 'field_harvest_source_issued_value';
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['expose']['remember_roles'] = array(
    2 => '2',
    3 => 0,
    1 => 0,
    5 => 0,
    4 => 0,
    6 => 0,
  );
  $handler->display->display_options['filters']['field_harvest_source_issued_value']['form_type'] = 'date_popup';
  /* Filter criterion: Content: Harvest Source Modified (field_harvest_souce_modified) */
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['id'] = 'field_harvest_souce_modified_value';
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['table'] = 'field_data_field_harvest_souce_modified';
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['field'] = 'field_harvest_souce_modified_value';
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['operator'] = 'between';
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['group'] = 1;
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['exposed'] = TRUE;
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['expose']['operator_id'] = 'field_harvest_souce_modified_value_op';
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['expose']['label'] = 'Harvest Source Modified';
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['expose']['operator'] = 'field_harvest_souce_modified_value_op';
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['expose']['identifier'] = 'field_harvest_souce_modified_value';
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['expose']['remember_roles'] = array(
    2 => '2',
    3 => 0,
    1 => 0,
    5 => 0,
    4 => 0,
    6 => 0,
  );
  $handler->display->display_options['filters']['field_harvest_souce_modified_value']['form_type'] = 'date_popup';
  /* Filter criterion: Content: Type */
  $handler->display->display_options['filters']['type']['id'] = 'type';
  $handler->display->display_options['filters']['type']['table'] = 'node';
  $handler->display->display_options['filters']['type']['field'] = 'type';
  $handler->display->display_options['filters']['type']['value'] = array(
    'dataset' => 'dataset',
  );
  $handler->display->display_options['filters']['type']['group'] = 1;
  /* Filter criterion: Content: Harvest Source (field_harvest_source_ref) */
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id']['id'] = 'field_harvest_source_ref_target_id';
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id']['table'] = 'field_data_field_harvest_source_ref';
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id']['field'] = 'field_harvest_source_ref_target_id';
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id']['operator'] = 'not empty';
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id']['value'] = array(
    'min' => '',
    'max' => '',
    'value' => '',
  );
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id']['group'] = 1;
  /* Filter criterion: Content: Orphan (field_orphan) */
  $handler->display->display_options['filters']['field_orphan_value']['id'] = 'field_orphan_value';
  $handler->display->display_options['filters']['field_orphan_value']['table'] = 'field_data_field_orphan';
  $handler->display->display_options['filters']['field_orphan_value']['field'] = 'field_orphan_value';
  $handler->display->display_options['filters']['field_orphan_value']['value'] = array(
    'all' => 'all',
    0 => '0',
    1 => '1',
  );
  $handler->display->display_options['filters']['field_orphan_value']['group'] = 1;
  $handler->display->display_options['filters']['field_orphan_value']['exposed'] = TRUE;
  $handler->display->display_options['filters']['field_orphan_value']['expose']['operator_id'] = 'field_orphan_value_op';
  $handler->display->display_options['filters']['field_orphan_value']['expose']['label'] = 'Orphan Status';
  $handler->display->display_options['filters']['field_orphan_value']['expose']['operator'] = 'field_orphan_value_op';
  $handler->display->display_options['filters']['field_orphan_value']['expose']['identifier'] = 'field_orphan_value';
  $handler->display->display_options['filters']['field_orphan_value']['expose']['remember_roles'] = array(
    2 => '2',
    3 => 0,
    1 => 0,
    5 => 0,
    4 => 0,
    6 => 0,
  );
  $handler->display->display_options['filters']['field_orphan_value']['expose']['reduce'] = TRUE;
  /* Filter criterion: Content: Harvest Source (field_harvest_source_ref) */
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id_1']['id'] = 'field_harvest_source_ref_target_id_1';
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id_1']['table'] = 'field_data_field_harvest_source_ref';
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id_1']['field'] = 'field_harvest_source_ref_target_id';
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id_1']['group'] = 1;
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id_1']['exposed'] = TRUE;
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id_1']['expose']['operator_id'] = 'field_harvest_source_ref_target_id_1_op';
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id_1']['expose']['label'] = 'Source';
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id_1']['expose']['operator'] = 'field_harvest_source_ref_target_id_1_op';
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id_1']['expose']['identifier'] = 'field_harvest_source_ref_target_id_1';
  $handler->display->display_options['filters']['field_harvest_source_ref_target_id_1']['expose']['remember_roles'] = array(
    2 => '2',
    3 => 0,
    1 => 0,
    5 => 0,
    4 => 0,
    6 => 0,
  );

  /* Display: Source Page */
  $handler = $view->new_display('page', 'Source Page', 'harvest_datasets_source_page');
  $handler->display->display_options['defaults']['style_plugin'] = FALSE;
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'views_bulk_operations' => 'views_bulk_operations',
    'nid' => 'nid',
    'og_group_ref' => 'og_group_ref',
    'title' => 'title',
    'created' => 'created',
    'changed' => 'changed',
    'field_orphan' => 'field_orphan',
    'status' => 'status',
  );
  $handler->display->display_options['style_options']['default'] = '-1';
  $handler->display->display_options['style_options']['info'] = array(
    'views_bulk_operations' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'nid' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'og_group_ref' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'title' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'created' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'changed' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_orphan' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'status' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
  );
  $handler->display->display_options['defaults']['style_options'] = FALSE;
  $handler->display->display_options['defaults']['row_plugin'] = FALSE;
  $handler->display->display_options['defaults']['row_options'] = FALSE;
  $handler->display->display_options['defaults']['arguments'] = FALSE;
  /* Contextual filter: Content: Harvest Source (field_harvest_source_ref) */
  $handler->display->display_options['arguments']['field_harvest_source_ref_target_id']['id'] = 'field_harvest_source_ref_target_id';
  $handler->display->display_options['arguments']['field_harvest_source_ref_target_id']['table'] = 'field_data_field_harvest_source_ref';
  $handler->display->display_options['arguments']['field_harvest_source_ref_target_id']['field'] = 'field_harvest_source_ref_target_id';
  $handler->display->display_options['arguments']['field_harvest_source_ref_target_id']['default_action'] = 'not found';
  $handler->display->display_options['arguments']['field_harvest_source_ref_target_id']['title_enable'] = TRUE;
  $handler->display->display_options['arguments']['field_harvest_source_ref_target_id']['title'] = 'Manage Harvested Datasets';
  $handler->display->display_options['arguments']['field_harvest_source_ref_target_id']['default_argument_type'] = 'fixed';
  $handler->display->display_options['arguments']['field_harvest_source_ref_target_id']['summary']['number_of_records'] = '0';
  $handler->display->display_options['arguments']['field_harvest_source_ref_target_id']['summary']['format'] = 'default_summary';
  $handler->display->display_options['arguments']['field_harvest_source_ref_target_id']['summary_options']['items_per_page'] = '25';
  $handler->display->display_options['arguments']['field_harvest_source_ref_target_id']['specify_validation'] = TRUE;
  $handler->display->display_options['arguments']['field_harvest_source_ref_target_id']['validate']['type'] = 'node';
  $handler->display->display_options['arguments']['field_harvest_source_ref_target_id']['validate_options']['types'] = array(
    'harvest_source' => 'harvest_source',
  );
  $handler->display->display_options['arguments']['field_harvest_source_ref_target_id']['validate_options']['access_op'] = 'update';
  $handler->display->display_options['path'] = 'node/%/datasets';
  $handler->display->display_options['menu']['type'] = 'tab';
  $handler->display->display_options['menu']['title'] = 'Manage Datasets';
  $handler->display->display_options['menu']['weight'] = 2;
  $handler->display->display_options['menu']['context'] = 0;
  $handler->display->display_options['menu']['context_only_inline'] = 0;
  $export['dkan_harvest_datasets'] = $view;

  return $export;
}
