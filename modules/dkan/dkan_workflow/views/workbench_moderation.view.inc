<?php

/**
 * @file
 * Workbench_moderation.view.inc.
 */

$view = new view();
$view->name = 'workbench_moderation';
$view->description = 'Lists content by moderation state.';
$view->tag = 'Workbench Moderation';
$view->base_table = 'node_revision';
$view->human_name = 'Workbench Moderation: Content';
$view->core = 7;
$view->api_version = '3.0';
$view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

/* Display: Defaults */
$handler = $view->new_display('default', 'Defaults', 'default');
$handler->display->display_options['title'] = 'Content I\'ve edited';
$handler->display->display_options['use_more_always'] = TRUE;
$handler->display->display_options['use_more_text'] = 'view all';
$handler->display->display_options['access']['type'] = 'perm';
$handler->display->display_options['access']['perm'] = 'use workbench_moderation my drafts tab';
$handler->display->display_options['cache']['type'] = 'none';
$handler->display->display_options['query']['type'] = 'views_query';
$handler->display->display_options['query']['options']['query_comment'] = FALSE;
$handler->display->display_options['exposed_form']['type'] = 'basic';
$handler->display->display_options['exposed_form']['options']['reset_button'] = TRUE;
$handler->display->display_options['pager']['type'] = 'full';
$handler->display->display_options['pager']['options']['items_per_page'] = '25';
$handler->display->display_options['pager']['options']['offset'] = '0';
$handler->display->display_options['pager']['options']['id'] = '0';
$handler->display->display_options['pager']['options']['quantity'] = '9';
$handler->display->display_options['pager']['options']['expose']['items_per_page_options'] = '10, 25, 50, 100, 200';
$handler->display->display_options['style_plugin'] = 'workflow_list';
$handler->display->display_options['style_options']['vbo_field'] = 'views_bulk_operations_1';
$handler->display->display_options['style_options']['vid_field'] = 'vid';
$handler->display->display_options['row_plugin'] = 'fields';
$handler->display->display_options['row_options']['default_field_elements'] = FALSE;
/* No results behavior: Global: Text area */
$handler->display->display_options['empty']['area']['id'] = 'area';
$handler->display->display_options['empty']['area']['table'] = 'views';
$handler->display->display_options['empty']['area']['field'] = 'area';
$handler->display->display_options['empty']['area']['content'] = 'You haven\'t created or edited any content.';
$handler->display->display_options['empty']['area']['format'] = '1';
/* Relationship: Workbench Moderation: Node */
$handler->display->display_options['relationships']['nid']['id'] = 'nid';
$handler->display->display_options['relationships']['nid']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['relationships']['nid']['field'] = 'nid';
$handler->display->display_options['relationships']['nid']['required'] = TRUE;
/* Relationship: Content revision: User */
$handler->display->display_options['relationships']['uid']['id'] = 'uid';
$handler->display->display_options['relationships']['uid']['table'] = 'node_revision';
$handler->display->display_options['relationships']['uid']['field'] = 'uid';
/* Field: Bulk operations: Content */
$handler->display->display_options['fields']['views_bulk_operations_1']['id'] = 'views_bulk_operations_1';
$handler->display->display_options['fields']['views_bulk_operations_1']['table'] = 'node';
$handler->display->display_options['fields']['views_bulk_operations_1']['field'] = 'views_bulk_operations';
$handler->display->display_options['fields']['views_bulk_operations_1']['relationship'] = 'nid';
$handler->display->display_options['fields']['views_bulk_operations_1']['label'] = '';
$handler->display->display_options['fields']['views_bulk_operations_1']['element_type'] = '0';
$handler->display->display_options['fields']['views_bulk_operations_1']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['views_bulk_operations_1']['element_wrapper_type'] = '0';
$handler->display->display_options['fields']['views_bulk_operations_1']['element_default_classes'] = FALSE;
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['display_type'] = '1';
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['enable_select_all_pages'] = 1;
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['force_single'] = 0;
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['entity_load_capacity'] = '10';
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_operations'] = array(
  'action::views_dkan_workflow_tree_set_moderation_state_action_needs_review' => array(
    'selected' => 1,
    'postpone_processing' => 0,
    'skip_confirmation' => 1,
    'override_label' => 1,
    'label' => 'Submit for review',
  ),
);
/* Field: Content revision: Vid */
$handler->display->display_options['fields']['vid']['id'] = 'vid';
$handler->display->display_options['fields']['vid']['table'] = 'node_revision';
$handler->display->display_options['fields']['vid']['field'] = 'vid';
$handler->display->display_options['fields']['vid']['relationship'] = 'nid';
$handler->display->display_options['fields']['vid']['exclude'] = TRUE;
/* Sort criterion: Content: Updated date */
$handler->display->display_options['sorts']['changed']['id'] = 'changed';
$handler->display->display_options['sorts']['changed']['table'] = 'node';
$handler->display->display_options['sorts']['changed']['field'] = 'changed';
$handler->display->display_options['sorts']['changed']['relationship'] = 'nid';
$handler->display->display_options['sorts']['changed']['order'] = 'DESC';
/* Filter criterion: User: Current */
$handler->display->display_options['filters']['uid_current']['id'] = 'uid_current';
$handler->display->display_options['filters']['uid_current']['table'] = 'users';
$handler->display->display_options['filters']['uid_current']['field'] = 'uid_current';
$handler->display->display_options['filters']['uid_current']['relationship'] = 'uid';
$handler->display->display_options['filters']['uid_current']['value'] = '1';
/* Filter criterion: Content: Title */
$handler->display->display_options['filters']['title']['id'] = 'title';
$handler->display->display_options['filters']['title']['table'] = 'node';
$handler->display->display_options['filters']['title']['field'] = 'title';
$handler->display->display_options['filters']['title']['relationship'] = 'nid';
$handler->display->display_options['filters']['title']['operator'] = 'contains';
$handler->display->display_options['filters']['title']['exposed'] = TRUE;
$handler->display->display_options['filters']['title']['expose']['operator_id'] = 'title_op';
$handler->display->display_options['filters']['title']['expose']['label'] = 'Title';
$handler->display->display_options['filters']['title']['expose']['operator'] = 'title_op';
$handler->display->display_options['filters']['title']['expose']['identifier'] = 'title';
$handler->display->display_options['filters']['title']['expose']['remember'] = TRUE;
/* Filter criterion: Workbench Moderation: State */
$handler->display->display_options['filters']['state']['id'] = 'state';
$handler->display->display_options['filters']['state']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['state']['field'] = 'state';
$handler->display->display_options['filters']['state']['value'] = array(
  'all' => 'all',
  'draft' => 'draft',
  'needs_review' => 'needs_review',
  'published' => 'published',
);
$handler->display->display_options['filters']['state']['exposed'] = TRUE;
$handler->display->display_options['filters']['state']['expose']['operator_id'] = 'state_op';
$handler->display->display_options['filters']['state']['expose']['label'] = 'State';
$handler->display->display_options['filters']['state']['expose']['operator'] = 'state_op';
$handler->display->display_options['filters']['state']['expose']['identifier'] = 'state';
$handler->display->display_options['filters']['state']['expose']['remember'] = TRUE;
$handler->display->display_options['filters']['state']['expose']['reduce'] = TRUE;
/* Filter criterion: Workbench Moderation: Current */
$handler->display->display_options['filters']['is_current']['id'] = 'is_current';
$handler->display->display_options['filters']['is_current']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['is_current']['field'] = 'is_current';

/* Display: Drafts page */
$handler = $view->new_display('page', 'Drafts page', 'drafts_page');
$handler->display->display_options['defaults']['title'] = FALSE;
$handler->display->display_options['title'] = 'My drafts';
$handler->display->display_options['defaults']['hide_admin_links'] = FALSE;
$handler->display->display_options['defaults']['empty'] = FALSE;
/* No results behavior: Global: Text area */
$handler->display->display_options['empty']['area']['id'] = 'area';
$handler->display->display_options['empty']['area']['table'] = 'views';
$handler->display->display_options['empty']['area']['field'] = 'area';
$handler->display->display_options['empty']['area']['content'] = 'This list displays content you have created that is not published. Currently there is no such content.';
$handler->display->display_options['empty']['area']['format'] = 'plain_text';
$handler->display->display_options['defaults']['fields'] = FALSE;
/* Field: Bulk operations: Content */
$handler->display->display_options['fields']['views_bulk_operations_1']['id'] = 'views_bulk_operations_1';
$handler->display->display_options['fields']['views_bulk_operations_1']['table'] = 'node';
$handler->display->display_options['fields']['views_bulk_operations_1']['field'] = 'views_bulk_operations';
$handler->display->display_options['fields']['views_bulk_operations_1']['relationship'] = 'nid';
$handler->display->display_options['fields']['views_bulk_operations_1']['label'] = '';
$handler->display->display_options['fields']['views_bulk_operations_1']['element_type'] = '0';
$handler->display->display_options['fields']['views_bulk_operations_1']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['views_bulk_operations_1']['element_wrapper_type'] = '0';
$handler->display->display_options['fields']['views_bulk_operations_1']['element_default_classes'] = FALSE;
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['display_type'] = '1';
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['enable_select_all_pages'] = 1;
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['force_single'] = 0;
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['entity_load_capacity'] = '10';
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_operations'] = array(
  'action::views_dkan_workflow_tree_set_moderation_state_action_needs_review' => array(
    'selected' => 1,
    'postpone_processing' => 0,
    'skip_confirmation' => 1,
    'override_label' => 1,
    'label' => 'Submit for review',
  ),
);
/* Field: Content revision: Vid */
$handler->display->display_options['fields']['vid']['id'] = 'vid';
$handler->display->display_options['fields']['vid']['table'] = 'node_revision';
$handler->display->display_options['fields']['vid']['field'] = 'vid';
$handler->display->display_options['fields']['vid']['relationship'] = 'nid';
$handler->display->display_options['fields']['vid']['exclude'] = TRUE;
$handler->display->display_options['defaults']['filter_groups'] = FALSE;
$handler->display->display_options['defaults']['filters'] = FALSE;
/* Filter criterion: User: Current */
$handler->display->display_options['filters']['uid_current']['id'] = 'uid_current';
$handler->display->display_options['filters']['uid_current']['table'] = 'users';
$handler->display->display_options['filters']['uid_current']['field'] = 'uid_current';
$handler->display->display_options['filters']['uid_current']['relationship'] = 'uid';
$handler->display->display_options['filters']['uid_current']['value'] = '1';
$handler->display->display_options['filters']['uid_current']['group'] = 1;
/* Filter criterion: Workbench Moderation: Current */
$handler->display->display_options['filters']['is_current']['id'] = 'is_current';
$handler->display->display_options['filters']['is_current']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['is_current']['field'] = 'is_current';
$handler->display->display_options['filters']['is_current']['value'] = '1';
$handler->display->display_options['filters']['is_current']['group'] = 1;
/* Filter criterion: Workbench Moderation: State */
$handler->display->display_options['filters']['state_1']['id'] = 'state_1';
$handler->display->display_options['filters']['state_1']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['state_1']['field'] = 'state';
$handler->display->display_options['filters']['state_1']['value'] = array(
  'draft' => 'draft',
);
$handler->display->display_options['filters']['state_1']['group'] = 1;
/* Filter criterion: Content: Title */
$handler->display->display_options['filters']['title']['id'] = 'title';
$handler->display->display_options['filters']['title']['table'] = 'node';
$handler->display->display_options['filters']['title']['field'] = 'title';
$handler->display->display_options['filters']['title']['relationship'] = 'nid';
$handler->display->display_options['filters']['title']['operator'] = 'contains';
$handler->display->display_options['filters']['title']['group'] = 1;
$handler->display->display_options['filters']['title']['exposed'] = TRUE;
$handler->display->display_options['filters']['title']['expose']['operator_id'] = 'title_op';
$handler->display->display_options['filters']['title']['expose']['label'] = 'Title';
$handler->display->display_options['filters']['title']['expose']['operator'] = 'title_op';
$handler->display->display_options['filters']['title']['expose']['identifier'] = 'title';
$handler->display->display_options['filters']['title']['expose']['remember'] = TRUE;
$handler->display->display_options['filters']['title']['expose']['remember_roles'] = array(
  2 => '2',
  1 => 0,
  200153887 => 0,
  30037204 => 0,
  132006037 => 0,
  210770034 => 0,
  190180509 => 0,
  169051924 => 0,
);
$handler->display->display_options['filters']['title']['expose']['autocomplete_items'] = '10';
$handler->display->display_options['filters']['title']['expose']['autocomplete_min_chars'] = '0';
$handler->display->display_options['filters']['title']['expose']['autocomplete_raw_suggestion'] = 1;
$handler->display->display_options['filters']['title']['expose']['autocomplete_raw_dropdown'] = 1;
$handler->display->display_options['filters']['title']['expose']['autocomplete_dependent'] = 0;
/* Filter criterion: Content: Type */
$handler->display->display_options['filters']['type']['id'] = 'type';
$handler->display->display_options['filters']['type']['table'] = 'node';
$handler->display->display_options['filters']['type']['field'] = 'type';
$handler->display->display_options['filters']['type']['relationship'] = 'nid';
$handler->display->display_options['filters']['type']['operator'] = 'empty';
$handler->display->display_options['filters']['type']['value'] = array(
  'all' => 'all',
  'dkan_data_story' => 'dkan_data_story',
  'data_dashboard' => 'data_dashboard',
  'dataset' => 'dataset',
  'group' => 'group',
  'page' => 'page',
  'resource' => 'resource',
);
$handler->display->display_options['filters']['type']['group'] = 1;
$handler->display->display_options['filters']['type']['exposed'] = TRUE;
$handler->display->display_options['filters']['type']['expose']['operator_id'] = 'type_op';
$handler->display->display_options['filters']['type']['expose']['label'] = 'Type';
$handler->display->display_options['filters']['type']['expose']['operator'] = 'type_op';
$handler->display->display_options['filters']['type']['expose']['identifier'] = 'type';
$handler->display->display_options['filters']['type']['expose']['remember'] = TRUE;
$handler->display->display_options['filters']['type']['expose']['remember_roles'] = array(
  2 => '2',
  1 => 0,
  200153887 => 0,
  30037204 => 0,
  132006037 => 0,
  210770034 => 0,
  190180509 => 0,
  169051924 => 0,
);
/* Filter criterion: Content: Groups audience (og_group_ref) (reference filter) */
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['id'] = 'og_group_ref_target_id_entityreference_filter';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['table'] = 'og_membership';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['field'] = 'og_group_ref_target_id_entityreference_filter';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['relationship'] = 'nid';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['group'] = 1;
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['exposed'] = TRUE;
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['operator_id'] = 'og_group_ref_target_id_entityreference_filter_op';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['label'] = 'Groups';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['operator'] = 'og_group_ref_target_id_entityreference_filter_op';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['identifier'] = 'og_group_ref_target_id_entityreference_filter';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['remember_roles'] = array(
  2 => '2',
  1 => 0,
  200153887 => 0,
  30037204 => 0,
  132006037 => 0,
  210770034 => 0,
  190180509 => 0,
  169051924 => 0,
);
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['reference_display'] = 'entity_reference_groups_list:entityreference_1';
$handler->display->display_options['path'] = 'admin/workbench/drafts-active';
$handler->display->display_options['menu']['type'] = 'tab';
$handler->display->display_options['menu']['title'] = 'My drafts';
$handler->display->display_options['menu']['weight'] = '20';
$handler->display->display_options['menu']['context'] = 0;
$handler->display->display_options['menu']['context_only_inline'] = 0;
$handler->display->display_options['tab_options']['weight'] = '0';

/* Display: Needs review page */
$handler = $view->new_display('page', 'Needs review page', 'needs_review_page');
$handler->display->display_options['defaults']['title'] = FALSE;
$handler->display->display_options['title'] = 'Needs review';
$handler->display->display_options['defaults']['hide_admin_links'] = FALSE;
$handler->display->display_options['defaults']['access'] = FALSE;
$handler->display->display_options['access']['type'] = 'perm';
$handler->display->display_options['access']['perm'] = 'use workbench_moderation needs review tab';
$handler->display->display_options['defaults']['empty'] = FALSE;
/* No results behavior: Global: Text area */
$handler->display->display_options['empty']['area']['id'] = 'area';
$handler->display->display_options['empty']['area']['table'] = 'views';
$handler->display->display_options['empty']['area']['field'] = 'area';
$handler->display->display_options['empty']['area']['content'] = 'This list displays content in the "Needs review" state that you can moderate. Currently there is no such content.';
$handler->display->display_options['empty']['area']['format'] = 'plain_text';
$handler->display->display_options['defaults']['fields'] = FALSE;
/* Field: Bulk operations: Content */
$handler->display->display_options['fields']['views_bulk_operations_1']['id'] = 'views_bulk_operations_1';
$handler->display->display_options['fields']['views_bulk_operations_1']['table'] = 'node';
$handler->display->display_options['fields']['views_bulk_operations_1']['field'] = 'views_bulk_operations';
$handler->display->display_options['fields']['views_bulk_operations_1']['relationship'] = 'nid';
$handler->display->display_options['fields']['views_bulk_operations_1']['label'] = '';
$handler->display->display_options['fields']['views_bulk_operations_1']['element_type'] = '0';
$handler->display->display_options['fields']['views_bulk_operations_1']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['views_bulk_operations_1']['element_wrapper_type'] = '0';
$handler->display->display_options['fields']['views_bulk_operations_1']['element_default_classes'] = FALSE;
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['display_type'] = '1';
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['enable_select_all_pages'] = 1;
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['force_single'] = 0;
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['entity_load_capacity'] = '10';
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_operations'] = array(
  'action::views_dkan_workflow_tree_set_moderation_state_action_draft' => array(
    'selected' => 1,
    'postpone_processing' => 0,
    'skip_confirmation' => 1,
    'override_label' => 1,
    'label' => 'Reject',
  ),
  'action::views_dkan_workflow_tree_set_moderation_state_action_published' => array(
    'selected' => 1,
    'postpone_processing' => 0,
    'skip_confirmation' => 1,
    'override_label' => 1,
    'label' => 'Publish',
  ),
);
/* Field: Content revision: Vid */
$handler->display->display_options['fields']['vid']['id'] = 'vid';
$handler->display->display_options['fields']['vid']['table'] = 'node_revision';
$handler->display->display_options['fields']['vid']['field'] = 'vid';
$handler->display->display_options['fields']['vid']['relationship'] = 'nid';
$handler->display->display_options['fields']['vid']['exclude'] = TRUE;
$handler->display->display_options['defaults']['filter_groups'] = FALSE;
$handler->display->display_options['defaults']['filters'] = FALSE;
/* Filter criterion: Workbench Moderation: Current */
$handler->display->display_options['filters']['is_current']['id'] = 'is_current';
$handler->display->display_options['filters']['is_current']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['is_current']['field'] = 'is_current';
$handler->display->display_options['filters']['is_current']['value'] = '1';
$handler->display->display_options['filters']['is_current']['group'] = 1;
/* Filter criterion: Workbench Moderation: State */
$handler->display->display_options['filters']['state']['id'] = 'state';
$handler->display->display_options['filters']['state']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['state']['field'] = 'state';
$handler->display->display_options['filters']['state']['value'] = array(
  'needs_review' => 'needs_review',
);
$handler->display->display_options['filters']['state']['group'] = 1;
$handler->display->display_options['filters']['state']['expose']['label'] = 'Workbench Moderation: State';
$handler->display->display_options['filters']['state']['expose']['operator'] = 'state_op';
$handler->display->display_options['filters']['state']['expose']['identifier'] = 'state';
/* Filter criterion: Content: Title */
$handler->display->display_options['filters']['title']['id'] = 'title';
$handler->display->display_options['filters']['title']['table'] = 'node';
$handler->display->display_options['filters']['title']['field'] = 'title';
$handler->display->display_options['filters']['title']['relationship'] = 'nid';
$handler->display->display_options['filters']['title']['operator'] = 'contains';
$handler->display->display_options['filters']['title']['group'] = 1;
$handler->display->display_options['filters']['title']['exposed'] = TRUE;
$handler->display->display_options['filters']['title']['expose']['operator_id'] = 'title_op';
$handler->display->display_options['filters']['title']['expose']['label'] = 'Title';
$handler->display->display_options['filters']['title']['expose']['operator'] = 'title_op';
$handler->display->display_options['filters']['title']['expose']['identifier'] = 'title';
$handler->display->display_options['filters']['title']['expose']['remember'] = TRUE;
$handler->display->display_options['filters']['title']['expose']['remember_roles'] = array(
  2 => '2',
  1 => 0,
  200153887 => 0,
  30037204 => 0,
  132006037 => 0,
  210770034 => 0,
  190180509 => 0,
  169051924 => 0,
);
$handler->display->display_options['filters']['title']['expose']['autocomplete_items'] = '10';
$handler->display->display_options['filters']['title']['expose']['autocomplete_min_chars'] = '0';
$handler->display->display_options['filters']['title']['expose']['autocomplete_raw_suggestion'] = 1;
$handler->display->display_options['filters']['title']['expose']['autocomplete_raw_dropdown'] = 1;
$handler->display->display_options['filters']['title']['expose']['autocomplete_dependent'] = 0;
/* Filter criterion: Content: Type */
$handler->display->display_options['filters']['type']['id'] = 'type';
$handler->display->display_options['filters']['type']['table'] = 'node';
$handler->display->display_options['filters']['type']['field'] = 'type';
$handler->display->display_options['filters']['type']['relationship'] = 'nid';
$handler->display->display_options['filters']['type']['value'] = array(
  'all' => 'all',
  'dkan_data_story' => 'dkan_data_story',
  'data_dashboard' => 'data_dashboard',
  'dataset' => 'dataset',
  'group' => 'group',
  'page' => 'page',
  'resource' => 'resource',
);
$handler->display->display_options['filters']['type']['group'] = 1;
$handler->display->display_options['filters']['type']['exposed'] = TRUE;
$handler->display->display_options['filters']['type']['expose']['operator_id'] = 'type_op';
$handler->display->display_options['filters']['type']['expose']['label'] = 'Type';
$handler->display->display_options['filters']['type']['expose']['operator'] = 'type_op';
$handler->display->display_options['filters']['type']['expose']['identifier'] = 'type';
$handler->display->display_options['filters']['type']['expose']['remember'] = TRUE;
$handler->display->display_options['filters']['type']['expose']['remember_roles'] = array(
  2 => '2',
  1 => 0,
  200153887 => 0,
  30037204 => 0,
  132006037 => 0,
  210770034 => 0,
  190180509 => 0,
  169051924 => 0,
);
/* Filter criterion: Content: Groups audience (og_group_ref) (reference filter) */
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['id'] = 'og_group_ref_target_id_entityreference_filter';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['table'] = 'og_membership';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['field'] = 'og_group_ref_target_id_entityreference_filter';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['relationship'] = 'nid';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['group'] = 1;
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['exposed'] = TRUE;
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['operator_id'] = 'og_group_ref_target_id_entityreference_filter_op';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['label'] = 'Groups';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['operator'] = 'og_group_ref_target_id_entityreference_filter_op';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['identifier'] = 'og_group_ref_target_id_entityreference_filter';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['remember'] = TRUE;
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['remember_roles'] = array(
  2 => '2',
  1 => 0,
  200153887 => 0,
  30037204 => 0,
  132006037 => 0,
  210770034 => 0,
  190180509 => 0,
  169051924 => 0,
);
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['reference_display'] = 'entity_reference_groups_list:entityreference_1';
$handler->display->display_options['path'] = 'admin/workbench/needs-review-active';
$handler->display->display_options['menu']['type'] = 'tab';
$handler->display->display_options['menu']['title'] = 'Needs review';
$handler->display->display_options['menu']['weight'] = '30';
$handler->display->display_options['menu']['context'] = 0;
$handler->display->display_options['menu']['context_only_inline'] = 0;

/* Display: Stale drafts */
$handler = $view->new_display('page', 'Stale drafts', 'page_stale_drafts');
$handler->display->display_options['defaults']['title'] = FALSE;
$handler->display->display_options['title'] = 'Stale drafts';
$handler->display->display_options['display_description'] = 'All content stuck in Draft for 72 hours or more';
$handler->display->display_options['defaults']['hide_admin_links'] = FALSE;
$handler->display->display_options['defaults']['access'] = FALSE;
$handler->display->display_options['access']['type'] = 'perm';
$handler->display->display_options['access']['perm'] = 'access stale drafts page';
$handler->display->display_options['defaults']['empty'] = FALSE;
/* No results behavior: Global: Text area */
$handler->display->display_options['empty']['area']['id'] = 'area';
$handler->display->display_options['empty']['area']['table'] = 'views';
$handler->display->display_options['empty']['area']['field'] = 'area';
$handler->display->display_options['empty']['area']['content'] = 'This list displays content in the "Needs review" state that you can moderate. Currently there is no such content.';
$handler->display->display_options['empty']['area']['format'] = 'plain_text';
$handler->display->display_options['defaults']['fields'] = FALSE;
/* Field: Bulk operations: Content */
$handler->display->display_options['fields']['views_bulk_operations_1']['id'] = 'views_bulk_operations_1';
$handler->display->display_options['fields']['views_bulk_operations_1']['table'] = 'node';
$handler->display->display_options['fields']['views_bulk_operations_1']['field'] = 'views_bulk_operations';
$handler->display->display_options['fields']['views_bulk_operations_1']['relationship'] = 'nid';
$handler->display->display_options['fields']['views_bulk_operations_1']['label'] = '';
$handler->display->display_options['fields']['views_bulk_operations_1']['element_type'] = '0';
$handler->display->display_options['fields']['views_bulk_operations_1']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['views_bulk_operations_1']['element_wrapper_type'] = '0';
$handler->display->display_options['fields']['views_bulk_operations_1']['element_default_classes'] = FALSE;
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['display_type'] = '1';
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['enable_select_all_pages'] = 1;
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['force_single'] = 0;
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['entity_load_capacity'] = '10';
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_operations'] = array(
  'action::views_dkan_workflow_tree_set_moderation_state_action_draft' => array(
    'selected' => 1,
    'postpone_processing' => 0,
    'skip_confirmation' => 1,
    'override_label' => 1,
    'label' => 'Reject',
  ),
  'action::views_dkan_workflow_tree_set_moderation_state_action_published' => array(
    'selected' => 1,
    'postpone_processing' => 0,
    'skip_confirmation' => 1,
    'override_label' => 1,
    'label' => 'Publish',
  ),
);
/* Field: Content revision: Vid */
$handler->display->display_options['fields']['vid']['id'] = 'vid';
$handler->display->display_options['fields']['vid']['table'] = 'node_revision';
$handler->display->display_options['fields']['vid']['field'] = 'vid';
$handler->display->display_options['fields']['vid']['relationship'] = 'nid';
$handler->display->display_options['fields']['vid']['exclude'] = TRUE;
$handler->display->display_options['defaults']['filter_groups'] = FALSE;
$handler->display->display_options['defaults']['filters'] = FALSE;
/* Filter criterion: Workbench Moderation: Current */
$handler->display->display_options['filters']['is_current']['id'] = 'is_current';
$handler->display->display_options['filters']['is_current']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['is_current']['field'] = 'is_current';
$handler->display->display_options['filters']['is_current']['value'] = '1';
$handler->display->display_options['filters']['is_current']['group'] = 1;
/* Filter criterion: Workbench Moderation: State */
$handler->display->display_options['filters']['state']['id'] = 'state';
$handler->display->display_options['filters']['state']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['state']['field'] = 'state';
$handler->display->display_options['filters']['state']['value'] = array(
  'draft' => 'draft',
);
$handler->display->display_options['filters']['state']['group'] = 1;
$handler->display->display_options['filters']['state']['expose']['label'] = 'Workbench Moderation: State';
$handler->display->display_options['filters']['state']['expose']['operator'] = 'state_op';
$handler->display->display_options['filters']['state']['expose']['identifier'] = 'state';
/* Filter criterion: Content: Title */
$handler->display->display_options['filters']['title']['id'] = 'title';
$handler->display->display_options['filters']['title']['table'] = 'node';
$handler->display->display_options['filters']['title']['field'] = 'title';
$handler->display->display_options['filters']['title']['relationship'] = 'nid';
$handler->display->display_options['filters']['title']['operator'] = 'contains';
$handler->display->display_options['filters']['title']['group'] = 1;
$handler->display->display_options['filters']['title']['exposed'] = TRUE;
$handler->display->display_options['filters']['title']['expose']['operator_id'] = 'title_op';
$handler->display->display_options['filters']['title']['expose']['label'] = 'Title';
$handler->display->display_options['filters']['title']['expose']['operator'] = 'title_op';
$handler->display->display_options['filters']['title']['expose']['identifier'] = 'title';
$handler->display->display_options['filters']['title']['expose']['remember'] = TRUE;
$handler->display->display_options['filters']['title']['expose']['remember_roles'] = array(
  2 => '2',
  1 => 0,
  200153887 => 0,
  30037204 => 0,
  132006037 => 0,
  210770034 => 0,
  190180509 => 0,
  169051924 => 0,
);
$handler->display->display_options['filters']['title']['expose']['autocomplete_items'] = '10';
$handler->display->display_options['filters']['title']['expose']['autocomplete_min_chars'] = '0';
$handler->display->display_options['filters']['title']['expose']['autocomplete_raw_suggestion'] = 1;
$handler->display->display_options['filters']['title']['expose']['autocomplete_raw_dropdown'] = 1;
$handler->display->display_options['filters']['title']['expose']['autocomplete_dependent'] = 0;
/* Filter criterion: Workbench Moderation: Timestamp */
$handler->display->display_options['filters']['stamp']['id'] = 'stamp';
$handler->display->display_options['filters']['stamp']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['stamp']['field'] = 'stamp';
$handler->display->display_options['filters']['stamp']['operator'] = '<';
$handler->display->display_options['filters']['stamp']['value']['value'] = '-72 hours';
$handler->display->display_options['filters']['stamp']['value']['type'] = 'offset';
$handler->display->display_options['filters']['stamp']['group'] = 1;
/* Filter criterion: Content: Type */
$handler->display->display_options['filters']['type']['id'] = 'type';
$handler->display->display_options['filters']['type']['table'] = 'node';
$handler->display->display_options['filters']['type']['field'] = 'type';
$handler->display->display_options['filters']['type']['relationship'] = 'nid';
$handler->display->display_options['filters']['type']['value'] = array(
  'dkan_data_story' => 'dkan_data_story',
  'data_dashboard' => 'data_dashboard',
  'dataset' => 'dataset',
  'group' => 'group',
  'page' => 'page',
  'resource' => 'resource',
);
$handler->display->display_options['filters']['type']['group'] = 1;
$handler->display->display_options['filters']['type']['exposed'] = TRUE;
$handler->display->display_options['filters']['type']['expose']['operator_id'] = 'type_op';
$handler->display->display_options['filters']['type']['expose']['label'] = 'Type';
$handler->display->display_options['filters']['type']['expose']['operator'] = 'type_op';
$handler->display->display_options['filters']['type']['expose']['identifier'] = 'type';
$handler->display->display_options['filters']['type']['expose']['remember'] = TRUE;
$handler->display->display_options['filters']['type']['expose']['remember_roles'] = array(
  2 => '2',
  1 => 0,
  200153887 => 0,
  30037204 => 0,
  132006037 => 0,
  210770034 => 0,
  190180509 => 0,
  169051924 => 0,
);
/* Filter criterion: Content: Groups audience (og_group_ref) (reference filter) */
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['id'] = 'og_group_ref_target_id_entityreference_filter';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['table'] = 'og_membership';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['field'] = 'og_group_ref_target_id_entityreference_filter';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['relationship'] = 'nid';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['group'] = 1;
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['exposed'] = TRUE;
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['operator_id'] = 'og_group_ref_target_id_entityreference_filter_op';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['label'] = 'Groups';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['operator'] = 'og_group_ref_target_id_entityreference_filter_op';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['identifier'] = 'og_group_ref_target_id_entityreference_filter';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['remember'] = TRUE;
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['remember_roles'] = array(
  2 => '2',
  1 => 0,
  200153887 => 0,
  30037204 => 0,
  132006037 => 0,
  210770034 => 0,
  190180509 => 0,
  169051924 => 0,
);
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['reference_display'] = 'entity_reference_groups_list:entityreference_1';
$handler->display->display_options['path'] = 'admin/workbench/drafts-stale';
$handler->display->display_options['menu']['type'] = 'tab';
$handler->display->display_options['menu']['title'] = 'Stale drafts';
$handler->display->display_options['menu']['description'] = 'All content stuck in Draft for 72 hours or more';
$handler->display->display_options['menu']['weight'] = '40';
$handler->display->display_options['menu']['context'] = 0;
$handler->display->display_options['menu']['context_only_inline'] = 0;

/* Display: Stale reviews */
$handler = $view->new_display('page', 'Stale reviews', 'page_stale_reviews');
$handler->display->display_options['defaults']['title'] = FALSE;
$handler->display->display_options['title'] = 'Needs review';
$handler->display->display_options['display_description'] = 'All content stuck in Needs Review for 48 hours or more';
$handler->display->display_options['defaults']['hide_admin_links'] = FALSE;
$handler->display->display_options['defaults']['access'] = FALSE;
$handler->display->display_options['access']['type'] = 'perm';
$handler->display->display_options['access']['perm'] = 'access stale reviews page';
$handler->display->display_options['defaults']['empty'] = FALSE;
/* No results behavior: Global: Text area */
$handler->display->display_options['empty']['area']['id'] = 'area';
$handler->display->display_options['empty']['area']['table'] = 'views';
$handler->display->display_options['empty']['area']['field'] = 'area';
$handler->display->display_options['empty']['area']['content'] = 'This list displays content in the "Needs review" state that you can moderate. Currently there is no such content.';
$handler->display->display_options['empty']['area']['format'] = 'plain_text';
$handler->display->display_options['defaults']['fields'] = FALSE;
/* Field: Bulk operations: Content */
$handler->display->display_options['fields']['views_bulk_operations_1']['id'] = 'views_bulk_operations_1';
$handler->display->display_options['fields']['views_bulk_operations_1']['table'] = 'node';
$handler->display->display_options['fields']['views_bulk_operations_1']['field'] = 'views_bulk_operations';
$handler->display->display_options['fields']['views_bulk_operations_1']['relationship'] = 'nid';
$handler->display->display_options['fields']['views_bulk_operations_1']['label'] = '';
$handler->display->display_options['fields']['views_bulk_operations_1']['element_type'] = '0';
$handler->display->display_options['fields']['views_bulk_operations_1']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['views_bulk_operations_1']['element_wrapper_type'] = '0';
$handler->display->display_options['fields']['views_bulk_operations_1']['element_default_classes'] = FALSE;
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['display_type'] = '1';
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['enable_select_all_pages'] = 1;
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['force_single'] = 0;
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_settings']['entity_load_capacity'] = '10';
$handler->display->display_options['fields']['views_bulk_operations_1']['vbo_operations'] = array(
  'action::views_dkan_workflow_tree_set_moderation_state_action_draft' => array(
    'selected' => 1,
    'postpone_processing' => 0,
    'skip_confirmation' => 1,
    'override_label' => 1,
    'label' => 'Reject',
  ),
  'action::views_dkan_workflow_tree_set_moderation_state_action_published' => array(
    'selected' => 1,
    'postpone_processing' => 0,
    'skip_confirmation' => 1,
    'override_label' => 1,
    'label' => 'Publish',
  ),
);
/* Field: Content revision: Vid */
$handler->display->display_options['fields']['vid']['id'] = 'vid';
$handler->display->display_options['fields']['vid']['table'] = 'node_revision';
$handler->display->display_options['fields']['vid']['field'] = 'vid';
$handler->display->display_options['fields']['vid']['relationship'] = 'nid';
$handler->display->display_options['fields']['vid']['exclude'] = TRUE;
$handler->display->display_options['defaults']['filter_groups'] = FALSE;
$handler->display->display_options['defaults']['filters'] = FALSE;
/* Filter criterion: Workbench Moderation: Current */
$handler->display->display_options['filters']['is_current']['id'] = 'is_current';
$handler->display->display_options['filters']['is_current']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['is_current']['field'] = 'is_current';
$handler->display->display_options['filters']['is_current']['value'] = '1';
$handler->display->display_options['filters']['is_current']['group'] = 1;
/* Filter criterion: Workbench Moderation: State */
$handler->display->display_options['filters']['state']['id'] = 'state';
$handler->display->display_options['filters']['state']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['state']['field'] = 'state';
$handler->display->display_options['filters']['state']['value'] = array(
  'needs_review' => 'needs_review',
);
$handler->display->display_options['filters']['state']['group'] = 1;
$handler->display->display_options['filters']['state']['expose']['label'] = 'Workbench Moderation: State';
$handler->display->display_options['filters']['state']['expose']['operator'] = 'state_op';
$handler->display->display_options['filters']['state']['expose']['identifier'] = 'state';
/* Filter criterion: Content: Title */
$handler->display->display_options['filters']['title']['id'] = 'title';
$handler->display->display_options['filters']['title']['table'] = 'node';
$handler->display->display_options['filters']['title']['field'] = 'title';
$handler->display->display_options['filters']['title']['relationship'] = 'nid';
$handler->display->display_options['filters']['title']['operator'] = 'contains';
$handler->display->display_options['filters']['title']['group'] = 1;
$handler->display->display_options['filters']['title']['exposed'] = TRUE;
$handler->display->display_options['filters']['title']['expose']['operator_id'] = 'title_op';
$handler->display->display_options['filters']['title']['expose']['label'] = 'Title';
$handler->display->display_options['filters']['title']['expose']['operator'] = 'title_op';
$handler->display->display_options['filters']['title']['expose']['identifier'] = 'title';
$handler->display->display_options['filters']['title']['expose']['remember'] = TRUE;
$handler->display->display_options['filters']['title']['expose']['remember_roles'] = array(
  2 => '2',
  1 => 0,
  200153887 => 0,
  30037204 => 0,
  132006037 => 0,
  210770034 => 0,
  190180509 => 0,
  169051924 => 0,
);
$handler->display->display_options['filters']['title']['expose']['autocomplete_items'] = '10';
$handler->display->display_options['filters']['title']['expose']['autocomplete_min_chars'] = '0';
$handler->display->display_options['filters']['title']['expose']['autocomplete_raw_suggestion'] = 1;
$handler->display->display_options['filters']['title']['expose']['autocomplete_raw_dropdown'] = 1;
$handler->display->display_options['filters']['title']['expose']['autocomplete_dependent'] = 0;
/* Filter criterion: Workbench Moderation: Timestamp */
$handler->display->display_options['filters']['stamp']['id'] = 'stamp';
$handler->display->display_options['filters']['stamp']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['stamp']['field'] = 'stamp';
$handler->display->display_options['filters']['stamp']['operator'] = '<';
$handler->display->display_options['filters']['stamp']['value']['value'] = '-48 hours';
$handler->display->display_options['filters']['stamp']['value']['type'] = 'offset';
$handler->display->display_options['filters']['stamp']['group'] = 1;
/* Filter criterion: Content: Type */
$handler->display->display_options['filters']['type']['id'] = 'type';
$handler->display->display_options['filters']['type']['table'] = 'node';
$handler->display->display_options['filters']['type']['field'] = 'type';
$handler->display->display_options['filters']['type']['relationship'] = 'nid';
$handler->display->display_options['filters']['type']['value'] = array(
  'dkan_data_story' => 'dkan_data_story',
  'data_dashboard' => 'data_dashboard',
  'dataset' => 'dataset',
  'group' => 'group',
  'page' => 'page',
  'resource' => 'resource',
);
$handler->display->display_options['filters']['type']['group'] = 1;
$handler->display->display_options['filters']['type']['exposed'] = TRUE;
$handler->display->display_options['filters']['type']['expose']['operator_id'] = 'type_op';
$handler->display->display_options['filters']['type']['expose']['label'] = 'Type';
$handler->display->display_options['filters']['type']['expose']['operator'] = 'type_op';
$handler->display->display_options['filters']['type']['expose']['identifier'] = 'type';
$handler->display->display_options['filters']['type']['expose']['remember'] = TRUE;
$handler->display->display_options['filters']['type']['expose']['remember_roles'] = array(
  2 => '2',
  1 => 0,
  200153887 => 0,
  30037204 => 0,
  132006037 => 0,
  210770034 => 0,
  190180509 => 0,
  169051924 => 0,
);
/* Filter criterion: Content: Groups audience (og_group_ref) (reference filter) */
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['id'] = 'og_group_ref_target_id_entityreference_filter';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['table'] = 'og_membership';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['field'] = 'og_group_ref_target_id_entityreference_filter';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['relationship'] = 'nid';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['group'] = 1;
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['exposed'] = TRUE;
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['operator_id'] = 'og_group_ref_target_id_entityreference_filter_op';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['label'] = 'Groups';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['operator'] = 'og_group_ref_target_id_entityreference_filter_op';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['identifier'] = 'og_group_ref_target_id_entityreference_filter';
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['remember'] = TRUE;
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['expose']['remember_roles'] = array(
  2 => '2',
  1 => 0,
  200153887 => 0,
  30037204 => 0,
  132006037 => 0,
  210770034 => 0,
  190180509 => 0,
  169051924 => 0,
);
$handler->display->display_options['filters']['og_group_ref_target_id_entityreference_filter']['reference_display'] = 'entity_reference_groups_list:entityreference_1';
$handler->display->display_options['path'] = 'admin/workbench/needs-review-stale';
$handler->display->display_options['menu']['type'] = 'tab';
$handler->display->display_options['menu']['title'] = 'Stale reviews';
$handler->display->display_options['menu']['description'] = 'All content stuck in Needs Review for 48 hours or more';
$handler->display->display_options['menu']['weight'] = '50';
$handler->display->display_options['menu']['context'] = 0;
$handler->display->display_options['menu']['context_only_inline'] = 0;

/* Display: My Drafts Badge */
$handler = $view->new_display('menu_badge', 'My Drafts Badge', 'my_drafts_badge');
$handler->display->display_options['use_more_always'] = FALSE;
$handler->display->display_options['group_by'] = TRUE;
$handler->display->display_options['access']['type'] = 'none';
$handler->display->display_options['cache']['type'] = 'none';
$handler->display->display_options['query']['type'] = 'views_query';
$handler->display->display_options['exposed_form']['type'] = 'basic';
$handler->display->display_options['pager']['type'] = 'some';
$handler->display->display_options['style_plugin'] = 'default';
$handler->display->display_options['row_plugin'] = 'fields';
/* Relationship: Workbench Moderation: Node */
$handler->display->display_options['relationships']['nid']['id'] = 'nid';
$handler->display->display_options['relationships']['nid']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['relationships']['nid']['field'] = 'nid';
$handler->display->display_options['relationships']['nid']['required'] = TRUE;
/* Relationship: Content revision: User */
$handler->display->display_options['relationships']['uid']['id'] = 'uid';
$handler->display->display_options['relationships']['uid']['table'] = 'node_revision';
$handler->display->display_options['relationships']['uid']['field'] = 'uid';
/* Field: Content revision: Vid */
$handler->display->display_options['fields']['vid']['id'] = 'vid';
$handler->display->display_options['fields']['vid']['table'] = 'node_revision';
$handler->display->display_options['fields']['vid']['field'] = 'vid';
$handler->display->display_options['fields']['vid']['relationship'] = 'nid';
$handler->display->display_options['fields']['vid']['group_type'] = 'count_distinct';
$handler->display->display_options['fields']['vid']['label'] = '';
$handler->display->display_options['fields']['vid']['element_label_colon'] = FALSE;
/* Filter criterion: Workbench Moderation: Current */
$handler->display->display_options['filters']['is_current']['id'] = 'is_current';
$handler->display->display_options['filters']['is_current']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['is_current']['field'] = 'is_current';
$handler->display->display_options['filters']['is_current']['value'] = '1';
$handler->display->display_options['filters']['is_current']['group'] = 1;
/* Filter criterion: Workbench Moderation: State */
$handler->display->display_options['filters']['state']['id'] = 'state';
$handler->display->display_options['filters']['state']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['state']['field'] = 'state';
$handler->display->display_options['filters']['state']['value'] = array(
  'draft' => 'draft',
);
$handler->display->display_options['filters']['state']['group'] = 1;
/* Filter criterion: User: Current */
$handler->display->display_options['filters']['uid_current']['id'] = 'uid_current';
$handler->display->display_options['filters']['uid_current']['table'] = 'users';
$handler->display->display_options['filters']['uid_current']['field'] = 'uid_current';
$handler->display->display_options['filters']['uid_current']['relationship'] = 'uid';
$handler->display->display_options['filters']['uid_current']['value'] = '1';

/* Display: Needs Review Badge */
$handler = $view->new_display('menu_badge', 'Needs Review Badge', 'needs_review_badge');
$handler->display->display_options['use_more_always'] = FALSE;
$handler->display->display_options['group_by'] = TRUE;
$handler->display->display_options['access']['type'] = 'none';
$handler->display->display_options['cache']['type'] = 'none';
$handler->display->display_options['query']['type'] = 'views_query';
$handler->display->display_options['exposed_form']['type'] = 'basic';
$handler->display->display_options['pager']['type'] = 'some';
$handler->display->display_options['style_plugin'] = 'default';
$handler->display->display_options['row_plugin'] = 'fields';
/* Relationship: Workbench Moderation: Node */
$handler->display->display_options['relationships']['nid']['id'] = 'nid';
$handler->display->display_options['relationships']['nid']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['relationships']['nid']['field'] = 'nid';
$handler->display->display_options['relationships']['nid']['required'] = TRUE;
/* Relationship: Content revision: User */
$handler->display->display_options['relationships']['uid']['id'] = 'uid';
$handler->display->display_options['relationships']['uid']['table'] = 'node_revision';
$handler->display->display_options['relationships']['uid']['field'] = 'uid';
/* Field: Content revision: Vid */
$handler->display->display_options['fields']['vid']['id'] = 'vid';
$handler->display->display_options['fields']['vid']['table'] = 'node_revision';
$handler->display->display_options['fields']['vid']['field'] = 'vid';
$handler->display->display_options['fields']['vid']['relationship'] = 'nid';
$handler->display->display_options['fields']['vid']['group_type'] = 'count_distinct';
$handler->display->display_options['fields']['vid']['label'] = '';
$handler->display->display_options['fields']['vid']['element_label_colon'] = FALSE;
/* Filter criterion: Workbench Moderation: Current */
$handler->display->display_options['filters']['is_current']['id'] = 'is_current';
$handler->display->display_options['filters']['is_current']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['is_current']['field'] = 'is_current';
$handler->display->display_options['filters']['is_current']['value'] = '1';
$handler->display->display_options['filters']['is_current']['group'] = 1;
/* Filter criterion: Workbench Moderation: State */
$handler->display->display_options['filters']['state']['id'] = 'state';
$handler->display->display_options['filters']['state']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['state']['field'] = 'state';
$handler->display->display_options['filters']['state']['value'] = array(
  'needs_review' => 'needs_review',
);
$handler->display->display_options['filters']['state']['group'] = 1;

/* Display: Stale Drafts Badge */
$handler = $view->new_display('menu_badge', 'Stale Drafts Badge', 'stale_drafts_badge');
$handler->display->display_options['title'] = 'Stale Drafts Badge';
$handler->display->display_options['use_more_always'] = FALSE;
$handler->display->display_options['group_by'] = TRUE;
$handler->display->display_options['access']['type'] = 'none';
$handler->display->display_options['cache']['type'] = 'none';
$handler->display->display_options['query']['type'] = 'views_query';
$handler->display->display_options['exposed_form']['type'] = 'basic';
$handler->display->display_options['pager']['type'] = 'some';
$handler->display->display_options['style_plugin'] = 'default';
$handler->display->display_options['row_plugin'] = 'fields';
/* Relationship: Workbench Moderation: Node */
$handler->display->display_options['relationships']['nid']['id'] = 'nid';
$handler->display->display_options['relationships']['nid']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['relationships']['nid']['field'] = 'nid';
$handler->display->display_options['relationships']['nid']['required'] = TRUE;
/* Relationship: Content revision: User */
$handler->display->display_options['relationships']['uid']['id'] = 'uid';
$handler->display->display_options['relationships']['uid']['table'] = 'node_revision';
$handler->display->display_options['relationships']['uid']['field'] = 'uid';
/* Field: Content revision: Vid */
$handler->display->display_options['fields']['vid']['id'] = 'vid';
$handler->display->display_options['fields']['vid']['table'] = 'node_revision';
$handler->display->display_options['fields']['vid']['field'] = 'vid';
$handler->display->display_options['fields']['vid']['relationship'] = 'nid';
$handler->display->display_options['fields']['vid']['group_type'] = 'count_distinct';
$handler->display->display_options['fields']['vid']['label'] = '';
$handler->display->display_options['fields']['vid']['element_label_colon'] = FALSE;
/* Filter criterion: Workbench Moderation: Current */
$handler->display->display_options['filters']['is_current']['id'] = 'is_current';
$handler->display->display_options['filters']['is_current']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['is_current']['field'] = 'is_current';
$handler->display->display_options['filters']['is_current']['value'] = '1';
$handler->display->display_options['filters']['is_current']['group'] = 1;
/* Filter criterion: Workbench Moderation: State */
$handler->display->display_options['filters']['state']['id'] = 'state';
$handler->display->display_options['filters']['state']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['state']['field'] = 'state';
$handler->display->display_options['filters']['state']['value'] = array(
  'draft' => 'draft',
);
$handler->display->display_options['filters']['state']['group'] = 1;
/* Filter criterion: Workbench Moderation: Timestamp */
$handler->display->display_options['filters']['stamp']['id'] = 'stamp';
$handler->display->display_options['filters']['stamp']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['stamp']['field'] = 'stamp';
$handler->display->display_options['filters']['stamp']['operator'] = '<';
$handler->display->display_options['filters']['stamp']['value']['value'] = '-72 hours';
$handler->display->display_options['filters']['stamp']['value']['type'] = 'offset';
$handler->display->display_options['filters']['stamp']['group'] = 1;

/* Display: Stale Reviews Badge */
$handler = $view->new_display('menu_badge', 'Stale Reviews Badge', 'stale_reviews_badge');
$handler->display->display_options['title'] = 'Stale Reviews Badge';
$handler->display->display_options['use_more_always'] = FALSE;
$handler->display->display_options['group_by'] = TRUE;
$handler->display->display_options['access']['type'] = 'none';
$handler->display->display_options['cache']['type'] = 'none';
$handler->display->display_options['query']['type'] = 'views_query';
$handler->display->display_options['exposed_form']['type'] = 'basic';
$handler->display->display_options['pager']['type'] = 'some';
$handler->display->display_options['style_plugin'] = 'default';
$handler->display->display_options['row_plugin'] = 'fields';
/* Relationship: Workbench Moderation: Node */
$handler->display->display_options['relationships']['nid']['id'] = 'nid';
$handler->display->display_options['relationships']['nid']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['relationships']['nid']['field'] = 'nid';
$handler->display->display_options['relationships']['nid']['required'] = TRUE;
/* Relationship: Content revision: User */
$handler->display->display_options['relationships']['uid']['id'] = 'uid';
$handler->display->display_options['relationships']['uid']['table'] = 'node_revision';
$handler->display->display_options['relationships']['uid']['field'] = 'uid';
/* Field: Content revision: Vid */
$handler->display->display_options['fields']['vid']['id'] = 'vid';
$handler->display->display_options['fields']['vid']['table'] = 'node_revision';
$handler->display->display_options['fields']['vid']['field'] = 'vid';
$handler->display->display_options['fields']['vid']['relationship'] = 'nid';
$handler->display->display_options['fields']['vid']['group_type'] = 'count_distinct';
$handler->display->display_options['fields']['vid']['label'] = '';
$handler->display->display_options['fields']['vid']['element_label_colon'] = FALSE;
/* Filter criterion: Workbench Moderation: Current */
$handler->display->display_options['filters']['is_current']['id'] = 'is_current';
$handler->display->display_options['filters']['is_current']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['is_current']['field'] = 'is_current';
$handler->display->display_options['filters']['is_current']['value'] = '1';
$handler->display->display_options['filters']['is_current']['group'] = 1;
/* Filter criterion: Workbench Moderation: State */
$handler->display->display_options['filters']['state']['id'] = 'state';
$handler->display->display_options['filters']['state']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['state']['field'] = 'state';
$handler->display->display_options['filters']['state']['value'] = array(
  'needs_review' => 'needs_review',
);
$handler->display->display_options['filters']['state']['group'] = 1;
/* Filter criterion: Workbench Moderation: Timestamp */
$handler->display->display_options['filters']['stamp']['id'] = 'stamp';
$handler->display->display_options['filters']['stamp']['table'] = 'workbench_moderation_node_history';
$handler->display->display_options['filters']['stamp']['field'] = 'stamp';
$handler->display->display_options['filters']['stamp']['operator'] = '<';
$handler->display->display_options['filters']['stamp']['value']['value'] = '-48 hours';
$handler->display->display_options['filters']['stamp']['value']['type'] = 'offset';
$handler->display->display_options['filters']['stamp']['group'] = 1;
