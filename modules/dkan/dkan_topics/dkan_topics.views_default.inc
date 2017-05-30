<?php

/**
 * @file
 * dkan_topics.views_default.inc
 */

/**
 * Implements hook_views_default_views().
 */
function dkan_topics_views_default_views() {
  $export = array();

  $view = new view();
  $view->name = 'dkan_topics_featured';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'taxonomy_term_data';
  $view->human_name = 'DKAN Featured Topics';
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
  $handler->display->display_options['pager']['options']['items_per_page'] = '12';
  $handler->display->display_options['pager']['options']['offset'] = '0';
  $handler->display->display_options['style_plugin'] = 'responsive_grid';
  $handler->display->display_options['style_options']['columns'] = '6';
  $handler->display->display_options['style_options']['column_classes'] = 'col-xs-12 col-sm-6 col-md-2';
  $handler->display->display_options['style_options']['row_classes'] = 'row container-12';
  $handler->display->display_options['row_plugin'] = 'fields';
  /* Field: Taxonomy term: Term ID */
  $handler->display->display_options['fields']['tid']['id'] = 'tid';
  $handler->display->display_options['fields']['tid']['table'] = 'taxonomy_term_data';
  $handler->display->display_options['fields']['tid']['field'] = 'tid';
  $handler->display->display_options['fields']['tid']['label'] = '';
  $handler->display->display_options['fields']['tid']['exclude'] = TRUE;
  $handler->display->display_options['fields']['tid']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['tid']['separator'] = '';
  /* Field: Taxonomy term: Icon Type */
  $handler->display->display_options['fields']['field_icon_type']['id'] = 'field_icon_type';
  $handler->display->display_options['fields']['field_icon_type']['table'] = 'field_data_field_icon_type';
  $handler->display->display_options['fields']['field_icon_type']['field'] = 'field_icon_type';
  $handler->display->display_options['fields']['field_icon_type']['label'] = '';
  $handler->display->display_options['fields']['field_icon_type']['exclude'] = TRUE;
  $handler->display->display_options['fields']['field_icon_type']['element_label_colon'] = FALSE;
  /* Field: Field: Image */
  $handler->display->display_options['fields']['field_image']['id'] = 'field_image';
  $handler->display->display_options['fields']['field_image']['table'] = 'field_data_field_image';
  $handler->display->display_options['fields']['field_image']['field'] = 'field_image';
  $handler->display->display_options['fields']['field_image']['label'] = '';
  $handler->display->display_options['fields']['field_image']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['field_image']['click_sort_column'] = 'fid';
  $handler->display->display_options['fields']['field_image']['settings'] = array(
    'image_style' => 'topic_medium',
    'image_link' => 'content',
  );
  /* Field: Taxonomy term: Icon Color */
  $handler->display->display_options['fields']['field_topic_icon_color']['id'] = 'field_topic_icon_color';
  $handler->display->display_options['fields']['field_topic_icon_color']['table'] = 'field_data_field_topic_icon_color';
  $handler->display->display_options['fields']['field_topic_icon_color']['field'] = 'field_topic_icon_color';
  $handler->display->display_options['fields']['field_topic_icon_color']['label'] = '';
  $handler->display->display_options['fields']['field_topic_icon_color']['exclude'] = TRUE;
  $handler->display->display_options['fields']['field_topic_icon_color']['alter']['trim_whitespace'] = TRUE;
  $handler->display->display_options['fields']['field_topic_icon_color']['alter']['strip_tags'] = TRUE;
  $handler->display->display_options['fields']['field_topic_icon_color']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['field_topic_icon_color']['element_wrapper_type'] = '0';
  $handler->display->display_options['fields']['field_topic_icon_color']['element_default_classes'] = FALSE;
  /* Field: Taxonomy term: Icon */
  $handler->display->display_options['fields']['field_topic_icon']['id'] = 'field_topic_icon';
  $handler->display->display_options['fields']['field_topic_icon']['table'] = 'field_data_field_topic_icon';
  $handler->display->display_options['fields']['field_topic_icon']['field'] = 'field_topic_icon';
  $handler->display->display_options['fields']['field_topic_icon']['label'] = '';
  $handler->display->display_options['fields']['field_topic_icon']['alter']['text'] = '<span style="color:[field_topic_icon_color]">[field_topic_icon]</span>';
  $handler->display->display_options['fields']['field_topic_icon']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['field_topic_icon']['element_wrapper_type'] = 'div';
  $handler->display->display_options['fields']['field_topic_icon']['element_wrapper_class'] = 'topic-icon';
  $handler->display->display_options['fields']['field_topic_icon']['element_default_classes'] = FALSE;
  /* Field: Taxonomy term: Name */
  $handler->display->display_options['fields']['name']['id'] = 'name';
  $handler->display->display_options['fields']['name']['table'] = 'taxonomy_term_data';
  $handler->display->display_options['fields']['name']['field'] = 'name';
  $handler->display->display_options['fields']['name']['label'] = '';
  $handler->display->display_options['fields']['name']['alter']['make_link'] = TRUE;
  $handler->display->display_options['fields']['name']['alter']['path'] = '/search/field_topic/[name]-[tid]';
  $handler->display->display_options['fields']['name']['alter']['absolute'] = TRUE;
  $handler->display->display_options['fields']['name']['alter']['replace_spaces'] = TRUE;
  $handler->display->display_options['fields']['name']['alter']['path_case'] = 'lower';
  $handler->display->display_options['fields']['name']['alter']['word_boundary'] = FALSE;
  $handler->display->display_options['fields']['name']['alter']['ellipsis'] = FALSE;
  $handler->display->display_options['fields']['name']['element_label_colon'] = FALSE;
  /* Sort criterion: Taxonomy term: Weight */
  $handler->display->display_options['sorts']['weight']['id'] = 'weight';
  $handler->display->display_options['sorts']['weight']['table'] = 'taxonomy_term_data';
  $handler->display->display_options['sorts']['weight']['field'] = 'weight';
  /* Filter criterion: Taxonomy vocabulary: Machine name */
  $handler->display->display_options['filters']['machine_name']['id'] = 'machine_name';
  $handler->display->display_options['filters']['machine_name']['table'] = 'taxonomy_vocabulary';
  $handler->display->display_options['filters']['machine_name']['field'] = 'machine_name';
  $handler->display->display_options['filters']['machine_name']['value'] = array(
    'dkan_topics' => 'dkan_topics',
  );

  /* Display: Featured Topic Content Pane */
  $handler = $view->new_display('panel_pane', 'Featured Topic Content Pane', 'panel_pane_1');
  $handler->display->display_options['pane_title'] = 'Featured Topics Grid';
  $handler->display->display_options['allow']['use_pager'] = 0;
  $handler->display->display_options['allow']['items_per_page'] = 'items_per_page';
  $handler->display->display_options['allow']['offset'] = 0;
  $handler->display->display_options['allow']['link_to_view'] = 0;
  $handler->display->display_options['allow']['more_link'] = 0;
  $handler->display->display_options['allow']['path_override'] = 0;
  $handler->display->display_options['allow']['title_override'] = 'title_override';
  $handler->display->display_options['allow']['exposed_form'] = 0;
  $handler->display->display_options['allow']['fields_override'] = 0;

  /* Display: Featured Topics Menu List */
  $handler = $view->new_display('block', 'Featured Topics Menu List', 'block_1');
  $handler->display->display_options['defaults']['pager'] = FALSE;
  $handler->display->display_options['pager']['type'] = 'some';
  $handler->display->display_options['pager']['options']['items_per_page'] = '14';
  $handler->display->display_options['pager']['options']['offset'] = '0';
  $handler->display->display_options['defaults']['style_plugin'] = FALSE;
  $handler->display->display_options['style_plugin'] = 'list';
  $handler->display->display_options['style_options']['row_class'] = 'topics-item';
  $handler->display->display_options['style_options']['default_row_class'] = FALSE;
  $handler->display->display_options['style_options']['row_class_special'] = FALSE;
  $handler->display->display_options['style_options']['class'] = 'dropdown-menu topics';
  $handler->display->display_options['style_options']['wrapper_class'] = '';
  $handler->display->display_options['defaults']['style_options'] = FALSE;
  $handler->display->display_options['defaults']['row_plugin'] = FALSE;
  $handler->display->display_options['row_plugin'] = 'fields';
  $handler->display->display_options['row_options']['default_field_elements'] = FALSE;
  $handler->display->display_options['defaults']['row_options'] = FALSE;
  $export['dkan_topics_featured'] = $view;

  return $export;
}
