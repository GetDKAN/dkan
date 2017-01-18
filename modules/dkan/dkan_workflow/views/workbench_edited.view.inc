<?php

/**
 * @file
 * Workbench_edited.view.inc.
 */

$view = new view();
$view->name = 'workbench_edited';
$view->description = 'Lists content edited by the user.';
$view->tag = 'Workbench';
$view->base_table = 'node_revision';
$view->human_name = 'Workbench: Edits by user';
$view->core = 7;
$view->api_version = '3.0';
$view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

/* Display: Defaults */
$handler = $view->new_display('default', 'Defaults', 'default');
$handler->display->display_options['title'] = 'My Edits';
$handler->display->display_options['use_more_always'] = TRUE;
$handler->display->display_options['use_more_text'] = 'view all';
$handler->display->display_options['access']['type'] = 'perm';
$handler->display->display_options['access']['perm'] = 'access workbench';
$handler->display->display_options['cache']['type'] = 'none';
$handler->display->display_options['query']['type'] = 'views_query';
$handler->display->display_options['query']['options']['query_comment'] = FALSE;
$handler->display->display_options['exposed_form']['type'] = 'basic';
$handler->display->display_options['pager']['type'] = 'full';
$handler->display->display_options['pager']['options']['items_per_page'] = '25';
$handler->display->display_options['pager']['options']['offset'] = '0';
$handler->display->display_options['pager']['options']['id'] = '0';
$handler->display->display_options['pager']['options']['expose']['items_per_page'] = TRUE;
$handler->display->display_options['pager']['options']['expose']['items_per_page_options'] = '10, 25, 50, 100, 200';
$handler->display->display_options['style_plugin'] = 'workflow_list';
$handler->display->display_options['style_options']['vbo_field'] = 'views_bulk_operations';
$handler->display->display_options['style_options']['vid_field'] = 'vid';
$handler->display->display_options['row_plugin'] = 'fields';
/* Footer: Global: Text area */
$handler->display->display_options['footer']['area']['id'] = 'area';
$handler->display->display_options['footer']['area']['table'] = 'views';
$handler->display->display_options['footer']['area']['field'] = 'area';
$handler->display->display_options['footer']['area']['empty'] = TRUE;
$handler->display->display_options['footer']['area']['content'] = 'Edited something recently and it\'s not in this list? If a content type isn\'t revisioned and you didn\'t create it, it will not show up in this list when you edit it. You can find it in the \'Content I Can Edit\' tab.';
$handler->display->display_options['footer']['area']['format'] = 'plain_text';
/* No results behavior: Global: Text area */
$handler->display->display_options['empty']['area']['id'] = 'area';
$handler->display->display_options['empty']['area']['table'] = 'views';
$handler->display->display_options['empty']['area']['field'] = 'area';
$handler->display->display_options['empty']['area']['content'] = 'You haven\'t created or edited any content.';
$handler->display->display_options['empty']['area']['format'] = 'plain_text';
/* Relationship: Content revision: User */
$handler->display->display_options['relationships']['uid']['id'] = 'uid';
$handler->display->display_options['relationships']['uid']['table'] = 'node_revision';
$handler->display->display_options['relationships']['uid']['field'] = 'uid';
/* Relationship: Content revision: Content */
$handler->display->display_options['relationships']['vid']['id'] = 'vid';
$handler->display->display_options['relationships']['vid']['table'] = 'node_revision';
$handler->display->display_options['relationships']['vid']['field'] = 'vid';
/* Field: Content: Nid */
$handler->display->display_options['fields']['nid']['id'] = 'nid';
$handler->display->display_options['fields']['nid']['table'] = 'node';
$handler->display->display_options['fields']['nid']['field'] = 'nid';
$handler->display->display_options['fields']['nid']['relationship'] = 'vid';
$handler->display->display_options['fields']['nid']['group_type'] = 'count';
$handler->display->display_options['fields']['nid']['label'] = '';
$handler->display->display_options['fields']['nid']['exclude'] = TRUE;
$handler->display->display_options['fields']['nid']['alter']['word_boundary'] = FALSE;
$handler->display->display_options['fields']['nid']['alter']['ellipsis'] = FALSE;
$handler->display->display_options['fields']['nid']['element_label_colon'] = FALSE;
/* Field: Content revision: Title */
$handler->display->display_options['fields']['title']['id'] = 'title';
$handler->display->display_options['fields']['title']['table'] = 'node_revision';
$handler->display->display_options['fields']['title']['field'] = 'title';
$handler->display->display_options['fields']['title']['relationship'] = 'vid';
$handler->display->display_options['fields']['title']['link_to_node'] = TRUE;
/* Field: Content: Type */
$handler->display->display_options['fields']['type']['id'] = 'type';
$handler->display->display_options['fields']['type']['table'] = 'node';
$handler->display->display_options['fields']['type']['field'] = 'type';
$handler->display->display_options['fields']['type']['relationship'] = 'vid';
$handler->display->display_options['fields']['type']['alter']['word_boundary'] = FALSE;
$handler->display->display_options['fields']['type']['alter']['ellipsis'] = FALSE;
/* Field: Content: Published */
$handler->display->display_options['fields']['status']['id'] = 'status';
$handler->display->display_options['fields']['status']['table'] = 'node';
$handler->display->display_options['fields']['status']['field'] = 'status';
$handler->display->display_options['fields']['status']['relationship'] = 'vid';
$handler->display->display_options['fields']['status']['alter']['word_boundary'] = FALSE;
$handler->display->display_options['fields']['status']['alter']['ellipsis'] = FALSE;
$handler->display->display_options['fields']['status']['not'] = 0;
/* Field: Content revision: Updated date */
$handler->display->display_options['fields']['timestamp']['id'] = 'timestamp';
$handler->display->display_options['fields']['timestamp']['table'] = 'node_revision';
$handler->display->display_options['fields']['timestamp']['field'] = 'timestamp';
$handler->display->display_options['fields']['timestamp']['label'] = 'Last updated';
$handler->display->display_options['fields']['timestamp']['date_format'] = 'time ago';
/* Field: Bulk operations: Content */
$handler->display->display_options['fields']['views_bulk_operations']['id'] = 'views_bulk_operations';
$handler->display->display_options['fields']['views_bulk_operations']['table'] = 'node';
$handler->display->display_options['fields']['views_bulk_operations']['field'] = 'views_bulk_operations';
$handler->display->display_options['fields']['views_bulk_operations']['relationship'] = 'vid';
$handler->display->display_options['fields']['views_bulk_operations']['label'] = '';
$handler->display->display_options['fields']['views_bulk_operations']['element_type'] = '0';
$handler->display->display_options['fields']['views_bulk_operations']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['views_bulk_operations']['element_wrapper_type'] = '0';
$handler->display->display_options['fields']['views_bulk_operations']['element_default_classes'] = FALSE;
$handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['display_type'] = '1';
$handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['enable_select_all_pages'] = 1;
$handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['row_clickable'] = 1;
$handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['force_single'] = 0;
$handler->display->display_options['fields']['views_bulk_operations']['vbo_settings']['entity_load_capacity'] = '10';
$handler->display->display_options['fields']['views_bulk_operations']['vbo_operations'] = array(
  'action::views_dkan_workflow_tree_set_moderation_state_action_draft' => array(
    'selected' => 1,
    'postpone_processing' => 0,
    'skip_confirmation' => 1,
    'override_label' => 1,
    'label' => 'Reject',
  ),
  'action::views_dkan_workflow_tree_set_moderation_state_action_needs_review' => array(
    'selected' => 1,
    'postpone_processing' => 0,
    'skip_confirmation' => 1,
    'override_label' => 1,
    'label' => 'Unpublish',
  ),
);
/* Field: Content revision: Vid */
$handler->display->display_options['fields']['vid']['id'] = 'vid';
$handler->display->display_options['fields']['vid']['table'] = 'node_revision';
$handler->display->display_options['fields']['vid']['field'] = 'vid';
$handler->display->display_options['fields']['vid']['label'] = '';
$handler->display->display_options['fields']['vid']['element_label_colon'] = FALSE;
/* Sort criterion: Content: Updated date */
$handler->display->display_options['sorts']['changed']['id'] = 'changed';
$handler->display->display_options['sorts']['changed']['table'] = 'node';
$handler->display->display_options['sorts']['changed']['field'] = 'changed';
$handler->display->display_options['sorts']['changed']['relationship'] = 'vid';
$handler->display->display_options['sorts']['changed']['order'] = 'DESC';
/* Filter criterion: User: Current */
$handler->display->display_options['filters']['uid_current']['id'] = 'uid_current';
$handler->display->display_options['filters']['uid_current']['table'] = 'users';
$handler->display->display_options['filters']['uid_current']['field'] = 'uid_current';
$handler->display->display_options['filters']['uid_current']['relationship'] = 'uid';
$handler->display->display_options['filters']['uid_current']['value'] = '1';
$handler->display->display_options['filters']['uid_current']['group'] = 1;
/* Filter criterion: Content revision: Title */
$handler->display->display_options['filters']['title']['id'] = 'title';
$handler->display->display_options['filters']['title']['table'] = 'node_revision';
$handler->display->display_options['filters']['title']['field'] = 'title';
$handler->display->display_options['filters']['title']['operator'] = 'contains';
$handler->display->display_options['filters']['title']['group'] = 1;
$handler->display->display_options['filters']['title']['exposed'] = TRUE;
$handler->display->display_options['filters']['title']['expose']['operator_id'] = 'title_1_op';
$handler->display->display_options['filters']['title']['expose']['label'] = 'Title';
$handler->display->display_options['filters']['title']['expose']['operator'] = 'title_1_op';
$handler->display->display_options['filters']['title']['expose']['identifier'] = 'title';
$handler->display->display_options['filters']['title']['expose']['remember'] = TRUE;
/* Filter criterion: Content: Type */
$handler->display->display_options['filters']['type']['id'] = 'type';
$handler->display->display_options['filters']['type']['table'] = 'node';
$handler->display->display_options['filters']['type']['field'] = 'type';
$handler->display->display_options['filters']['type']['relationship'] = 'vid';
$handler->display->display_options['filters']['type']['group'] = 1;
$handler->display->display_options['filters']['type']['exposed'] = TRUE;
$handler->display->display_options['filters']['type']['expose']['label'] = 'Type';
$handler->display->display_options['filters']['type']['expose']['identifier'] = 'type';
$handler->display->display_options['filters']['type']['expose']['remember'] = TRUE;
/* Filter criterion: Content: Published */
$handler->display->display_options['filters']['status']['id'] = 'status';
$handler->display->display_options['filters']['status']['table'] = 'node';
$handler->display->display_options['filters']['status']['field'] = 'status';
$handler->display->display_options['filters']['status']['relationship'] = 'vid';
$handler->display->display_options['filters']['status']['value'] = 'All';
$handler->display->display_options['filters']['status']['group'] = 1;
$handler->display->display_options['filters']['status']['exposed'] = TRUE;
$handler->display->display_options['filters']['status']['expose']['label'] = 'Published';
$handler->display->display_options['filters']['status']['expose']['identifier'] = 'published';
$handler->display->display_options['filters']['status']['expose']['remember'] = TRUE;
/* Filter criterion: Content: Type */
$handler->display->display_options['filters']['type_1']['id'] = 'type_1';
$handler->display->display_options['filters']['type_1']['table'] = 'node';
$handler->display->display_options['filters']['type_1']['field'] = 'type';
$handler->display->display_options['filters']['type_1']['relationship'] = 'vid';
$handler->display->display_options['filters']['type_1']['operator'] = 'not empty';

/* Display: Page */
$handler = $view->new_display('page', 'Page', 'page_1');
$handler->display->display_options['defaults']['hide_admin_links'] = FALSE;
$handler->display->display_options['path'] = 'admin/workbench/content/edited';
$handler->display->display_options['menu']['type'] = 'tab';
$handler->display->display_options['menu']['title'] = 'My Edits';
$handler->display->display_options['menu']['weight'] = '1';

/* Display: Block */
$handler = $view->new_display('block', 'Block', 'block_1');
$handler->display->display_options['defaults']['hide_admin_links'] = FALSE;
$handler->display->display_options['defaults']['use_more'] = FALSE;
$handler->display->display_options['use_more'] = TRUE;
$handler->display->display_options['defaults']['pager'] = FALSE;
$handler->display->display_options['pager']['type'] = 'some';
$handler->display->display_options['pager']['options']['items_per_page'] = '5';
$handler->display->display_options['pager']['options']['offset'] = '0';
$handler->display->display_options['defaults']['header'] = FALSE;
/* Header: Global: Text area */
$handler->display->display_options['header']['area']['id'] = 'area';
$handler->display->display_options['header']['area']['table'] = 'views';
$handler->display->display_options['header']['area']['field'] = 'area';
$handler->display->display_options['header']['area']['content'] = 'Five of the most recently updated pieces of content.';
$handler->display->display_options['header']['area']['format'] = 'plain_text';
$handler->display->display_options['defaults']['footer'] = FALSE;
$handler->display->display_options['defaults']['filter_groups'] = FALSE;
$handler->display->display_options['defaults']['filters'] = FALSE;
/* Filter criterion: User: Current */
$handler->display->display_options['filters']['uid_current']['id'] = 'uid_current';
$handler->display->display_options['filters']['uid_current']['table'] = 'users';
$handler->display->display_options['filters']['uid_current']['field'] = 'uid_current';
$handler->display->display_options['filters']['uid_current']['relationship'] = 'uid';
$handler->display->display_options['filters']['uid_current']['value'] = '1';
/* Filter criterion: Content: Type */
$handler->display->display_options['filters']['type']['id'] = 'type';
$handler->display->display_options['filters']['type']['table'] = 'node';
$handler->display->display_options['filters']['type']['field'] = 'type';
$handler->display->display_options['filters']['type']['relationship'] = 'vid';
$handler->display->display_options['filters']['type']['operator'] = 'not empty';
