<?php

/**
 * @file
 * dkan_harvest.views_default.inc
 */

/**
 * Implements hook_views_default_views().
 */
function dkan_harvest_views_default_views() {
  $export = array();

  $view = new view();
  $view->name = 'dkan_harvest_source_search';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'search_api_index_datasets';
  $view->human_name = 'DKAN Harvest Source Search';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'none';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['style_plugin'] = 'default';
  $handler->display->display_options['row_plugin'] = 'entity';
  $handler->display->display_options['row_options']['view_mode'] = 'search_result';
  /* Relationship: Indexed Node: Harvest Source */
  $handler->display->display_options['relationships']['field_harvest_source_ref']['id'] = 'field_harvest_source_ref';
  $handler->display->display_options['relationships']['field_harvest_source_ref']['table'] = 'search_api_index_datasets';
  $handler->display->display_options['relationships']['field_harvest_source_ref']['field'] = 'field_harvest_source_ref';
  /* Field: Indexed Node: Node ID */
  $handler->display->display_options['fields']['nid']['id'] = 'nid';
  $handler->display->display_options['fields']['nid']['table'] = 'search_api_index_datasets';
  $handler->display->display_options['fields']['nid']['field'] = 'nid';
  /* Sort criterion: Indexed Node: Date changed */
  $handler->display->display_options['sorts']['changed']['id'] = 'changed';
  $handler->display->display_options['sorts']['changed']['table'] = 'search_api_index_datasets';
  $handler->display->display_options['sorts']['changed']['field'] = 'changed';
  $handler->display->display_options['sorts']['changed']['order'] = 'DESC';
  $handler->display->display_options['sorts']['changed']['exposed'] = TRUE;
  $handler->display->display_options['sorts']['changed']['expose']['label'] = 'Date changed';
  /* Sort criterion: Indexed Node: Title */
  $handler->display->display_options['sorts']['title']['id'] = 'title';
  $handler->display->display_options['sorts']['title']['table'] = 'search_api_index_datasets';
  $handler->display->display_options['sorts']['title']['field'] = 'title';
  $handler->display->display_options['sorts']['title']['order'] = 'DESC';
  $handler->display->display_options['sorts']['title']['exposed'] = TRUE;
  $handler->display->display_options['sorts']['title']['expose']['label'] = 'Title';
  /* Contextual filter: Indexed Node: Harvest Source */
  $handler->display->display_options['arguments']['field_harvest_source_ref']['id'] = 'field_harvest_source_ref';
  $handler->display->display_options['arguments']['field_harvest_source_ref']['table'] = 'search_api_index_datasets';
  $handler->display->display_options['arguments']['field_harvest_source_ref']['field'] = 'field_harvest_source_ref';
  $handler->display->display_options['arguments']['field_harvest_source_ref']['default_action'] = 'default';
  $handler->display->display_options['arguments']['field_harvest_source_ref']['default_argument_type'] = 'node';
  $handler->display->display_options['arguments']['field_harvest_source_ref']['summary']['format'] = 'default_summary';
  $handler->display->display_options['arguments']['field_harvest_source_ref']['break_phrase'] = 0;
  $handler->display->display_options['arguments']['field_harvest_source_ref']['not'] = 0;
  /* Filter criterion: Search: Fulltext search */
  $handler->display->display_options['filters']['search_api_views_fulltext']['id'] = 'search_api_views_fulltext';
  $handler->display->display_options['filters']['search_api_views_fulltext']['table'] = 'search_api_index_datasets';
  $handler->display->display_options['filters']['search_api_views_fulltext']['field'] = 'search_api_views_fulltext';
  $handler->display->display_options['filters']['search_api_views_fulltext']['exposed'] = TRUE;
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['operator_id'] = 'search_api_views_fulltext_op';
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['label'] = 'Search';
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['operator'] = 'search_api_views_fulltext_op';
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['identifier'] = 'search_api_views_fulltext';
  $handler->display->display_options['filters']['search_api_views_fulltext']['expose']['remember_roles'] = array(
    2 => '2',
    3 => 0,
    1 => 0,
    5 => 0,
    4 => 0,
    6 => 0,
  );
  /* Filter criterion: Indexed Node: Status */
  $handler->display->display_options['filters']['status']['id'] = 'status';
  $handler->display->display_options['filters']['status']['table'] = 'search_api_index_datasets';
  $handler->display->display_options['filters']['status']['field'] = 'status';
  $handler->display->display_options['filters']['status']['value'] = array(
    1 => '1',
  );

  /* Display: DKAN Harvest Source Search */
  $handler = $view->new_display('panel_pane', 'DKAN Harvest Source Search', 'dkan_harvest_source_search_pane');
  $handler->display->display_options['exposed_block'] = TRUE;
  $handler->display->display_options['inherit_panels_path'] = '1';
  $export['dkan_harvest_source_search'] = $view;

  return $export;
}
