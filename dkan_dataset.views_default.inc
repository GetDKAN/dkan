<?php
/**
 * @file
 * dkan_dataset.views_default.inc
 */

/**
 * Implements hook_views_default_views().
 */
function dkan_dataset_views_default_views() {
  $export = array();

  $view = new view();
  $view->name = 'dkan_dataset_json';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'node';
  $view->human_name = 'dkan_dataset_json';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'perm';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['style_plugin'] = 'default';
  $handler->display->display_options['row_plugin'] = 'fields';
  /* Field: Content: Title */
  $handler->display->display_options['fields']['title']['id'] = 'title';
  $handler->display->display_options['fields']['title']['table'] = 'node';
  $handler->display->display_options['fields']['title']['field'] = 'title';
  $handler->display->display_options['fields']['title']['label'] = 'title';
  $handler->display->display_options['fields']['title']['alter']['word_boundary'] = FALSE;
  $handler->display->display_options['fields']['title']['alter']['ellipsis'] = FALSE;
  $handler->display->display_options['fields']['title']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['title']['link_to_node'] = FALSE;
  /* Field: Content: Body */
  $handler->display->display_options['fields']['body']['id'] = 'body';
  $handler->display->display_options['fields']['body']['table'] = 'field_data_body';
  $handler->display->display_options['fields']['body']['field'] = 'body';
  $handler->display->display_options['fields']['body']['label'] = 'description';
  /* Field: Content: Additional Info */
  $handler->display->display_options['fields']['field_additional_info']['id'] = 'field_additional_info';
  $handler->display->display_options['fields']['field_additional_info']['table'] = 'field_data_field_additional_info';
  $handler->display->display_options['fields']['field_additional_info']['field'] = 'field_additional_info';
  $handler->display->display_options['fields']['field_additional_info']['label'] = 'additional_info';
  $handler->display->display_options['fields']['field_additional_info']['click_sort_column'] = 'first';
  $handler->display->display_options['fields']['field_additional_info']['settings'] = array(
    'style' => 'inline',
    'first' => array(
      'hidden' => 0,
      'format' => '_none',
      'prefix' => '',
      'suffix' => ':&nbsp;',
    ),
    'second' => array(
      'hidden' => 0,
      'format' => '_none',
      'prefix' => '',
      'suffix' => '',
    ),
  );
  $handler->display->display_options['fields']['field_additional_info']['delta_offset'] = '0';
  /* Field: Content: Author */
  $handler->display->display_options['fields']['field_author']['id'] = 'field_author';
  $handler->display->display_options['fields']['field_author']['table'] = 'field_data_field_author';
  $handler->display->display_options['fields']['field_author']['field'] = 'field_author';
  $handler->display->display_options['fields']['field_author']['label'] = 'author';
  /* Field: Content: Data Dictionary */
  $handler->display->display_options['fields']['field_data_dictionary']['id'] = 'field_data_dictionary';
  $handler->display->display_options['fields']['field_data_dictionary']['table'] = 'field_data_field_data_dictionary';
  $handler->display->display_options['fields']['field_data_dictionary']['field'] = 'field_data_dictionary';
  $handler->display->display_options['fields']['field_data_dictionary']['label'] = 'data_dictionary';
  /* Field: Content: Frequency */
  $handler->display->display_options['fields']['field_frequency']['id'] = 'field_frequency';
  $handler->display->display_options['fields']['field_frequency']['table'] = 'field_data_field_frequency';
  $handler->display->display_options['fields']['field_frequency']['field'] = 'field_frequency';
  $handler->display->display_options['fields']['field_frequency']['label'] = 'frequency';
  /* Field: Content: Granularity */
  $handler->display->display_options['fields']['field_granularity']['id'] = 'field_granularity';
  $handler->display->display_options['fields']['field_granularity']['table'] = 'field_data_field_granularity';
  $handler->display->display_options['fields']['field_granularity']['field'] = 'field_granularity';
  $handler->display->display_options['fields']['field_granularity']['label'] = 'granularity';
  /* Field: Content: Groups audience */
  $handler->display->display_options['fields']['og_group_ref']['id'] = 'og_group_ref';
  $handler->display->display_options['fields']['og_group_ref']['table'] = 'og_membership';
  $handler->display->display_options['fields']['og_group_ref']['field'] = 'og_group_ref';
  $handler->display->display_options['fields']['og_group_ref']['label'] = 'publisher';
  $handler->display->display_options['fields']['og_group_ref']['settings'] = array(
    'link' => 0,
  );
  $handler->display->display_options['fields']['og_group_ref']['delta_offset'] = '0';
  /* Field: Content: License */
  $handler->display->display_options['fields']['field_license']['id'] = 'field_license';
  $handler->display->display_options['fields']['field_license']['table'] = 'field_data_field_license';
  $handler->display->display_options['fields']['field_license']['field'] = 'field_license';
  $handler->display->display_options['fields']['field_license']['label'] = 'license';
  $handler->display->display_options['fields']['field_license']['type'] = 'select_or_other_formatter';
  /* Field: Content: Spatial / Geographical Coverage Area */
  $handler->display->display_options['fields']['field_spatial']['id'] = 'field_spatial';
  $handler->display->display_options['fields']['field_spatial']['table'] = 'field_data_field_spatial';
  $handler->display->display_options['fields']['field_spatial']['field'] = 'field_spatial';
  $handler->display->display_options['fields']['field_spatial']['label'] = 'spatial';
  $handler->display->display_options['fields']['field_spatial']['click_sort_column'] = 'wkt';
  $handler->display->display_options['fields']['field_spatial']['settings'] = array(
    'data' => 'full',
  );
  /* Field: Content: Spatial / Geographical Coverage Location */
  $handler->display->display_options['fields']['field_spatial_geographical_cover']['id'] = 'field_spatial_geographical_cover';
  $handler->display->display_options['fields']['field_spatial_geographical_cover']['table'] = 'field_data_field_spatial_geographical_cover';
  $handler->display->display_options['fields']['field_spatial_geographical_cover']['field'] = 'field_spatial_geographical_cover';
  $handler->display->display_options['fields']['field_spatial_geographical_cover']['label'] = 'spatial';
  /* Field: Content: Tags */
  $handler->display->display_options['fields']['field_tags']['id'] = 'field_tags';
  $handler->display->display_options['fields']['field_tags']['table'] = 'field_data_field_tags';
  $handler->display->display_options['fields']['field_tags']['field'] = 'field_tags';
  $handler->display->display_options['fields']['field_tags']['label'] = 'tags';
  $handler->display->display_options['fields']['field_tags']['type'] = 'taxonomy_term_reference_plain';
  $handler->display->display_options['fields']['field_tags']['delta_offset'] = '0';
  /* Field: Content: Temporal Coverage */
  $handler->display->display_options['fields']['field_temporal_coverage']['id'] = 'field_temporal_coverage';
  $handler->display->display_options['fields']['field_temporal_coverage']['table'] = 'field_data_field_temporal_coverage';
  $handler->display->display_options['fields']['field_temporal_coverage']['field'] = 'field_temporal_coverage';
  $handler->display->display_options['fields']['field_temporal_coverage']['label'] = 'temporal_coverage';
  $handler->display->display_options['fields']['field_temporal_coverage']['settings'] = array(
    'format_type' => 'long',
    'fromto' => 'both',
    'multiple_number' => '',
    'multiple_from' => '',
    'multiple_to' => '',
  );
  /* Field: Content: Node UUID */
  $handler->display->display_options['fields']['uuid']['id'] = 'uuid';
  $handler->display->display_options['fields']['uuid']['table'] = 'node';
  $handler->display->display_options['fields']['uuid']['field'] = 'uuid';
  $handler->display->display_options['fields']['uuid']['label'] = 'id';
  /* Field: Content: Post date */
  $handler->display->display_options['fields']['created']['id'] = 'created';
  $handler->display->display_options['fields']['created']['table'] = 'node';
  $handler->display->display_options['fields']['created']['field'] = 'created';
  $handler->display->display_options['fields']['created']['label'] = 'created';
  $handler->display->display_options['fields']['created']['date_format'] = 'long';
  /* Field: Content: Updated date */
  $handler->display->display_options['fields']['changed']['id'] = 'changed';
  $handler->display->display_options['fields']['changed']['table'] = 'node';
  $handler->display->display_options['fields']['changed']['field'] = 'changed';
  $handler->display->display_options['fields']['changed']['label'] = 'updated';
  $handler->display->display_options['fields']['changed']['date_format'] = 'long';
  /* Sort criterion: Content: Post date */
  $handler->display->display_options['sorts']['created']['id'] = 'created';
  $handler->display->display_options['sorts']['created']['table'] = 'node';
  $handler->display->display_options['sorts']['created']['field'] = 'created';
  $handler->display->display_options['sorts']['created']['order'] = 'DESC';
  /* Contextual filter: Content: Nid */
  $handler->display->display_options['arguments']['nid']['id'] = 'nid';
  $handler->display->display_options['arguments']['nid']['table'] = 'node';
  $handler->display->display_options['arguments']['nid']['field'] = 'nid';
  $handler->display->display_options['arguments']['nid']['default_action'] = 'default';
  $handler->display->display_options['arguments']['nid']['default_argument_type'] = 'raw';
  $handler->display->display_options['arguments']['nid']['default_argument_options']['index'] = '1';
  $handler->display->display_options['arguments']['nid']['summary']['number_of_records'] = '0';
  $handler->display->display_options['arguments']['nid']['summary']['format'] = 'default_summary';
  $handler->display->display_options['arguments']['nid']['summary_options']['items_per_page'] = '25';
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
    'dataset' => 'dataset',
  );
  $export['dkan_dataset_json'] = $view;

  $view = new view();
  $view->name = 'dkan_dataset_resources_json';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'node';
  $view->human_name = 'dkan_dataset_resources_json';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'perm';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'some';
  $handler->display->display_options['pager']['options']['items_per_page'] = '10';
  $handler->display->display_options['style_plugin'] = 'views_json';
  $handler->display->display_options['style_options']['root_object'] = 'resources';
  $handler->display->display_options['style_options']['top_child_object'] = '';
  $handler->display->display_options['style_options']['plaintext_output'] = 0;
  $handler->display->display_options['style_options']['remove_newlines'] = 0;
  $handler->display->display_options['style_options']['jsonp_prefix'] = '';
  $handler->display->display_options['style_options']['using_views_api_mode'] = 0;
  $handler->display->display_options['style_options']['object_arrays'] = 0;
  $handler->display->display_options['style_options']['numeric_strings'] = 0;
  $handler->display->display_options['style_options']['bigint_string'] = 0;
  $handler->display->display_options['style_options']['pretty_print'] = 0;
  $handler->display->display_options['style_options']['unescaped_slashes'] = 0;
  $handler->display->display_options['style_options']['unescaped_unicode'] = 0;
  $handler->display->display_options['style_options']['char_encoding'] = array();
  /* Field: Content: Title */
  $handler->display->display_options['fields']['title']['id'] = 'title';
  $handler->display->display_options['fields']['title']['table'] = 'node';
  $handler->display->display_options['fields']['title']['field'] = 'title';
  $handler->display->display_options['fields']['title']['label'] = 'title';
  $handler->display->display_options['fields']['title']['alter']['word_boundary'] = FALSE;
  $handler->display->display_options['fields']['title']['alter']['ellipsis'] = FALSE;
  $handler->display->display_options['fields']['title']['link_to_node'] = FALSE;
  /* Field: Content: Body */
  $handler->display->display_options['fields']['body']['id'] = 'body';
  $handler->display->display_options['fields']['body']['table'] = 'field_data_body';
  $handler->display->display_options['fields']['body']['field'] = 'body';
  $handler->display->display_options['fields']['body']['label'] = 'description';
  /* Field: Content: Node UUID */
  $handler->display->display_options['fields']['uuid']['id'] = 'uuid';
  $handler->display->display_options['fields']['uuid']['table'] = 'node';
  $handler->display->display_options['fields']['uuid']['field'] = 'uuid';
  $handler->display->display_options['fields']['uuid']['label'] = 'id';
  /* Field: Content: Path */
  $handler->display->display_options['fields']['path']['id'] = 'path';
  $handler->display->display_options['fields']['path']['table'] = 'node';
  $handler->display->display_options['fields']['path']['field'] = 'path';
  $handler->display->display_options['fields']['path']['label'] = 'url';
  /* Field: Content: Format */
  $handler->display->display_options['fields']['field_format']['id'] = 'field_format';
  $handler->display->display_options['fields']['field_format']['table'] = 'field_data_field_format';
  $handler->display->display_options['fields']['field_format']['field'] = 'field_format';
  $handler->display->display_options['fields']['field_format']['label'] = 'format';
  $handler->display->display_options['fields']['field_format']['type'] = 'taxonomy_term_reference_plain';
  /* Field: Content: Link API */
  $handler->display->display_options['fields']['field_link_api']['id'] = 'field_link_api';
  $handler->display->display_options['fields']['field_link_api']['table'] = 'field_data_field_link_api';
  $handler->display->display_options['fields']['field_link_api']['field'] = 'field_link_api';
  $handler->display->display_options['fields']['field_link_api']['label'] = 'link_to_api';
  $handler->display->display_options['fields']['field_link_api']['click_sort_column'] = 'url';
  $handler->display->display_options['fields']['field_link_api']['type'] = 'link_absolute';
  /* Field: Content: Link File */
  $handler->display->display_options['fields']['field_link_remote_file']['id'] = 'field_link_remote_file';
  $handler->display->display_options['fields']['field_link_remote_file']['table'] = 'field_data_field_link_remote_file';
  $handler->display->display_options['fields']['field_link_remote_file']['field'] = 'field_link_remote_file';
  $handler->display->display_options['fields']['field_link_remote_file']['label'] = 'file_link';
  $handler->display->display_options['fields']['field_link_remote_file']['click_sort_column'] = 'fid';
  /* Field: Content: Post date */
  $handler->display->display_options['fields']['created']['id'] = 'created';
  $handler->display->display_options['fields']['created']['table'] = 'node';
  $handler->display->display_options['fields']['created']['field'] = 'created';
  $handler->display->display_options['fields']['created']['label'] = 'created';
  $handler->display->display_options['fields']['created']['date_format'] = 'long';
  /* Field: Content: Updated date */
  $handler->display->display_options['fields']['changed']['id'] = 'changed';
  $handler->display->display_options['fields']['changed']['table'] = 'node';
  $handler->display->display_options['fields']['changed']['field'] = 'changed';
  $handler->display->display_options['fields']['changed']['label'] = 'modified';
  $handler->display->display_options['fields']['changed']['date_format'] = 'long';
  /* Sort criterion: Content: Post date */
  $handler->display->display_options['sorts']['created']['id'] = 'created';
  $handler->display->display_options['sorts']['created']['table'] = 'node';
  $handler->display->display_options['sorts']['created']['field'] = 'created';
  $handler->display->display_options['sorts']['created']['order'] = 'DESC';
  /* Contextual filter: Content: Dataset (field_dataset_ref) */
  $handler->display->display_options['arguments']['field_dataset_ref_target_id']['id'] = 'field_dataset_ref_target_id';
  $handler->display->display_options['arguments']['field_dataset_ref_target_id']['table'] = 'field_data_field_dataset_ref';
  $handler->display->display_options['arguments']['field_dataset_ref_target_id']['field'] = 'field_dataset_ref_target_id';
  $handler->display->display_options['arguments']['field_dataset_ref_target_id']['default_action'] = 'default';
  $handler->display->display_options['arguments']['field_dataset_ref_target_id']['default_argument_type'] = 'raw';
  $handler->display->display_options['arguments']['field_dataset_ref_target_id']['default_argument_options']['index'] = '1';
  $handler->display->display_options['arguments']['field_dataset_ref_target_id']['summary']['number_of_records'] = '0';
  $handler->display->display_options['arguments']['field_dataset_ref_target_id']['summary']['format'] = 'default_summary';
  $handler->display->display_options['arguments']['field_dataset_ref_target_id']['summary_options']['items_per_page'] = '25';
  /* Filter criterion: Content: Published */
  $handler->display->display_options['filters']['status']['id'] = 'status';
  $handler->display->display_options['filters']['status']['table'] = 'node';
  $handler->display->display_options['filters']['status']['field'] = 'status';
  $handler->display->display_options['filters']['status']['value'] = 1;
  $handler->display->display_options['filters']['status']['group'] = 1;
  $handler->display->display_options['filters']['status']['expose']['operator'] = FALSE;
  $export['dkan_dataset_resources_json'] = $view;

  return $export;
}
