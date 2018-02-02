<?php

/**
 * @file
 * dkan_linkchecker.views_default.inc
 */

/**
 * Implements hook_views_default_views().
 */
function dkan_linkchecker_views_default_views() {
  $export = array();
  $view = new view();
  $view->name = 'dkan_linkchecker_reports';
  $view->description = 'Display a list of broken links.';
  $view->tag = 'default';
  $view->base_table = 'linkchecker_link';
  $view->human_name = 'DKAN Advanced Broken links';
  $view->core = 0;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Defaults */
  $handler = $view->new_display('default', 'Defaults', 'default');
  $handler->display->display_options['title'] = 'Private Broken links';
  $handler->display->display_options['items_per_page'] = 0;
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'perm';
  $handler->display->display_options['access']['perm'] = 'access broken links report';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['query']['options']['query_comment'] = FALSE;
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['exposed_form']['options']['reset_button'] = TRUE;
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '50';
  $handler->display->display_options['pager']['options']['offset'] = '0';
  $handler->display->display_options['pager']['options']['id'] = '0';
  $handler->display->display_options['pager']['options']['quantity'] = '9';
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'url' => 'url',
    'code' => 'code',
    'error' => 'error',
    'link_edit' => 'link_edit',
    'nid' => 'nid',
    'edit_node' => 'edit_node',
    'bid' => 'bid',
    'block_edit' => 'block_edit',
    'type' => 'type',
    'title' => 'title',
    'field_contact_email_1' => 'field_contact_email_1',
    'field_contact_email' => 'field_contact_email',
    'field_contact_name_1' => 'field_contact_name_1',
    'field_contact_name' => 'field_contact_name',
    'field_public_access_level_2' => 'field_public_access_level_2',
    'field_public_access_level' => 'field_public_access_level',
    'last_checked' => 'last_checked',
    'nothing' => 'nothing',
  );
  $handler->display->display_options['style_options']['default'] = '-1';
  $handler->display->display_options['style_options']['info'] = array(
    'url' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'code' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'error' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'link_edit' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'nid' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'edit_node' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'bid' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'block_edit' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'type' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
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
    'field_contact_email_1' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_contact_email' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_contact_name_1' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_contact_name' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_public_access_level_2' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'field_public_access_level' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'last_checked' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'nothing' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
  );
  $handler->display->display_options['style_options']['sticky'] = TRUE;
  $handler->display->display_options['style_options']['empty_table'] = TRUE;
  /* Header: Global: Result summary */
  $handler->display->display_options['header']['result']['id'] = 'result';
  $handler->display->display_options['header']['result']['table'] = 'views';
  $handler->display->display_options['header']['result']['field'] = 'result';
  /* No results behavior: Global: Text area */
  $handler->display->display_options['empty']['area']['id'] = 'area';
  $handler->display->display_options['empty']['area']['table'] = 'views';
  $handler->display->display_options['empty']['area']['field'] = 'area';
  $handler->display->display_options['empty']['area']['empty'] = TRUE;
  $handler->display->display_options['empty']['area']['content'] = 'No broken links have been found.';
  $handler->display->display_options['empty']['area']['format'] = 'plain_text';
  /* Relationship: Broken links: Content with link */
  $handler->display->display_options['relationships']['nid']['id'] = 'nid';
  $handler->display->display_options['relationships']['nid']['table'] = 'linkchecker_node';
  $handler->display->display_options['relationships']['nid']['field'] = 'nid';
  /* Relationship: Entity Reference: Referenced Entity */
  $handler->display->display_options['relationships']['field_dataset_ref_target_id']['id'] = 'field_dataset_ref_target_id';
  $handler->display->display_options['relationships']['field_dataset_ref_target_id']['table'] = 'field_data_field_dataset_ref';
  $handler->display->display_options['relationships']['field_dataset_ref_target_id']['field'] = 'field_dataset_ref_target_id';
  $handler->display->display_options['relationships']['field_dataset_ref_target_id']['relationship'] = 'nid';
  /* Field: Broken links: URL */
  $handler->display->display_options['fields']['url']['id'] = 'url';
  $handler->display->display_options['fields']['url']['table'] = 'linkchecker_link';
  $handler->display->display_options['fields']['url']['field'] = 'url';
  $handler->display->display_options['fields']['url']['alter']['max_length'] = '40';
  $handler->display->display_options['fields']['url']['alter']['word_boundary'] = FALSE;
  $handler->display->display_options['fields']['url']['alter']['trim'] = TRUE;
  $handler->display->display_options['fields']['url']['hide_empty'] = TRUE;
  /* Field: Broken links: HTTP status code */
  $handler->display->display_options['fields']['code']['id'] = 'code';
  $handler->display->display_options['fields']['code']['table'] = 'linkchecker_link';
  $handler->display->display_options['fields']['code']['field'] = 'code';
  $handler->display->display_options['fields']['code']['label'] = 'Response';
  $handler->display->display_options['fields']['code']['separator'] = '';
  /* Field: Broken links: Error message */
  $handler->display->display_options['fields']['error']['id'] = 'error';
  $handler->display->display_options['fields']['error']['table'] = 'linkchecker_link';
  $handler->display->display_options['fields']['error']['field'] = 'error';
  $handler->display->display_options['fields']['error']['label'] = 'Error';
  /* Field: Broken links: Edit link settings */
  $handler->display->display_options['fields']['link_edit']['id'] = 'link_edit';
  $handler->display->display_options['fields']['link_edit']['table'] = 'linkchecker_link';
  $handler->display->display_options['fields']['link_edit']['field'] = 'link_edit';
  $handler->display->display_options['fields']['link_edit']['exclude'] = TRUE;
  $handler->display->display_options['fields']['link_edit']['alter']['alter_text'] = TRUE;
  $handler->display->display_options['fields']['link_edit']['alter']['text'] = '<div class="btn-primary">Edit link settings</div>';
  $handler->display->display_options['fields']['link_edit']['hide_empty'] = TRUE;
  /* Field: Content: Nid */
  $handler->display->display_options['fields']['nid']['id'] = 'nid';
  $handler->display->display_options['fields']['nid']['table'] = 'node';
  $handler->display->display_options['fields']['nid']['field'] = 'nid';
  $handler->display->display_options['fields']['nid']['relationship'] = 'nid';
  $handler->display->display_options['fields']['nid']['exclude'] = TRUE;
  /* Field: Content: Edit link */
  $handler->display->display_options['fields']['edit_node']['id'] = 'edit_node';
  $handler->display->display_options['fields']['edit_node']['table'] = 'views_entity_node';
  $handler->display->display_options['fields']['edit_node']['field'] = 'edit_node';
  $handler->display->display_options['fields']['edit_node']['relationship'] = 'nid';
  $handler->display->display_options['fields']['edit_node']['label'] = 'Edit node';
  $handler->display->display_options['fields']['edit_node']['exclude'] = TRUE;
  $handler->display->display_options['fields']['edit_node']['alter']['alter_text'] = TRUE;
  $handler->display->display_options['fields']['edit_node']['alter']['text'] = '<div class="btn-primary">Edit node [nid]</div>';
  $handler->display->display_options['fields']['edit_node']['hide_empty'] = TRUE;
  /* Field: Broken links: Block ID */
  $handler->display->display_options['fields']['bid']['id'] = 'bid';
  $handler->display->display_options['fields']['bid']['table'] = 'linkchecker_block_custom';
  $handler->display->display_options['fields']['bid']['field'] = 'bid';
  $handler->display->display_options['fields']['bid']['exclude'] = TRUE;
  $handler->display->display_options['fields']['bid']['separator'] = '';
  /* Field: Broken links: Edit block */
  $handler->display->display_options['fields']['block_edit']['id'] = 'block_edit';
  $handler->display->display_options['fields']['block_edit']['table'] = 'linkchecker_block_custom';
  $handler->display->display_options['fields']['block_edit']['field'] = 'block_edit';
  $handler->display->display_options['fields']['block_edit']['exclude'] = TRUE;
  $handler->display->display_options['fields']['block_edit']['alter']['alter_text'] = TRUE;
  $handler->display->display_options['fields']['block_edit']['alter']['text'] = '<li>Edit block [bid]</li>';
  $handler->display->display_options['fields']['block_edit']['hide_empty'] = TRUE;
  /* Field: Content: Type */
  $handler->display->display_options['fields']['type']['id'] = 'type';
  $handler->display->display_options['fields']['type']['table'] = 'node';
  $handler->display->display_options['fields']['type']['field'] = 'type';
  $handler->display->display_options['fields']['type']['relationship'] = 'nid';
  /* Field: Content: Title */
  $handler->display->display_options['fields']['title']['id'] = 'title';
  $handler->display->display_options['fields']['title']['table'] = 'node';
  $handler->display->display_options['fields']['title']['field'] = 'title';
  $handler->display->display_options['fields']['title']['relationship'] = 'nid';
  /* Field: Resource contact email */
  $handler->display->display_options['fields']['field_contact_email_1']['id'] = 'field_contact_email_1';
  $handler->display->display_options['fields']['field_contact_email_1']['table'] = 'field_data_field_contact_email';
  $handler->display->display_options['fields']['field_contact_email_1']['field'] = 'field_contact_email';
  $handler->display->display_options['fields']['field_contact_email_1']['relationship'] = 'field_dataset_ref_target_id';
  $handler->display->display_options['fields']['field_contact_email_1']['ui_name'] = 'Resource contact email';
  $handler->display->display_options['fields']['field_contact_email_1']['label'] = '';
  $handler->display->display_options['fields']['field_contact_email_1']['exclude'] = TRUE;
  $handler->display->display_options['fields']['field_contact_email_1']['element_label_colon'] = FALSE;
  /* Field: Dataset contact email */
  $handler->display->display_options['fields']['field_contact_email']['id'] = 'field_contact_email';
  $handler->display->display_options['fields']['field_contact_email']['table'] = 'field_data_field_contact_email';
  $handler->display->display_options['fields']['field_contact_email']['field'] = 'field_contact_email';
  $handler->display->display_options['fields']['field_contact_email']['relationship'] = 'nid';
  $handler->display->display_options['fields']['field_contact_email']['ui_name'] = 'Dataset contact email';
  $handler->display->display_options['fields']['field_contact_email']['label'] = '';
  $handler->display->display_options['fields']['field_contact_email']['exclude'] = TRUE;
  $handler->display->display_options['fields']['field_contact_email']['element_label_colon'] = FALSE;
  /* Field: Content: Contact Name */
  $handler->display->display_options['fields']['field_contact_name_1']['id'] = 'field_contact_name_1';
  $handler->display->display_options['fields']['field_contact_name_1']['table'] = 'field_data_field_contact_name';
  $handler->display->display_options['fields']['field_contact_name_1']['field'] = 'field_contact_name';
  $handler->display->display_options['fields']['field_contact_name_1']['relationship'] = 'field_dataset_ref_target_id';
  $handler->display->display_options['fields']['field_contact_name_1']['label'] = '';
  $handler->display->display_options['fields']['field_contact_name_1']['exclude'] = TRUE;
  $handler->display->display_options['fields']['field_contact_name_1']['element_label_colon'] = FALSE;
  /* Field: Content: Contact Name */
  $handler->display->display_options['fields']['field_contact_name']['id'] = 'field_contact_name';
  $handler->display->display_options['fields']['field_contact_name']['table'] = 'field_data_field_contact_name';
  $handler->display->display_options['fields']['field_contact_name']['field'] = 'field_contact_name';
  $handler->display->display_options['fields']['field_contact_name']['relationship'] = 'nid';
  $handler->display->display_options['fields']['field_contact_name']['empty'] = '[field_contact_name_1]';
  /* Field: Resource Access Level */
  $handler->display->display_options['fields']['field_public_access_level_2']['id'] = 'field_public_access_level_2';
  $handler->display->display_options['fields']['field_public_access_level_2']['table'] = 'field_data_field_public_access_level';
  $handler->display->display_options['fields']['field_public_access_level_2']['field'] = 'field_public_access_level';
  $handler->display->display_options['fields']['field_public_access_level_2']['relationship'] = 'field_dataset_ref_target_id';
  $handler->display->display_options['fields']['field_public_access_level_2']['ui_name'] = 'Resource Access Level';
  $handler->display->display_options['fields']['field_public_access_level_2']['label'] = 'Resource Access Level';
  $handler->display->display_options['fields']['field_public_access_level_2']['exclude'] = TRUE;
  /* Field: Dataset Access Level */
  $handler->display->display_options['fields']['field_public_access_level']['id'] = 'field_public_access_level';
  $handler->display->display_options['fields']['field_public_access_level']['table'] = 'field_data_field_public_access_level';
  $handler->display->display_options['fields']['field_public_access_level']['field'] = 'field_public_access_level';
  $handler->display->display_options['fields']['field_public_access_level']['relationship'] = 'nid';
  $handler->display->display_options['fields']['field_public_access_level']['ui_name'] = 'Dataset Access Level';
  $handler->display->display_options['fields']['field_public_access_level']['label'] = 'Access Level';
  $handler->display->display_options['fields']['field_public_access_level']['empty'] = '[field_public_access_level_2]';
  /* Field: Broken links: Last checked */
  $handler->display->display_options['fields']['last_checked']['id'] = 'last_checked';
  $handler->display->display_options['fields']['last_checked']['table'] = 'linkchecker_link';
  $handler->display->display_options['fields']['last_checked']['field'] = 'last_checked';
  $handler->display->display_options['fields']['last_checked']['date_format'] = 'iso_8601_date';
  $handler->display->display_options['fields']['last_checked']['second_date_format'] = 'iso_8601_date';
  /* Field: Global: Custom text */
  $handler->display->display_options['fields']['nothing']['id'] = 'nothing';
  $handler->display->display_options['fields']['nothing']['table'] = 'views';
  $handler->display->display_options['fields']['nothing']['field'] = 'nothing';
  $handler->display->display_options['fields']['nothing']['label'] = 'Operations';
  $handler->display->display_options['fields']['nothing']['alter']['text'] = '[link_edit]
  [edit_node]
  [block_edit]';
  $handler->display->display_options['fields']['nothing']['hide_empty'] = TRUE;
  $handler->display->display_options['fields']['nothing']['hide_alter_empty'] = TRUE;
  /* Sort criterion: Content: Title */
  $handler->display->display_options['sorts']['title']['id'] = 'title';
  $handler->display->display_options['sorts']['title']['table'] = 'node';
  $handler->display->display_options['sorts']['title']['field'] = 'title';
  $handler->display->display_options['sorts']['title']['relationship'] = 'nid';
  /* Filter criterion: Broken links: Last checked */
  $handler->display->display_options['filters']['last_checked']['id'] = 'last_checked';
  $handler->display->display_options['filters']['last_checked']['table'] = 'linkchecker_link';
  $handler->display->display_options['filters']['last_checked']['field'] = 'last_checked';
  $handler->display->display_options['filters']['last_checked']['operator'] = '!=';
  $handler->display->display_options['filters']['last_checked']['value']['value'] = '0';
  $handler->display->display_options['filters']['last_checked']['group'] = 1;
  /* Filter criterion: Broken links: Status */
  $handler->display->display_options['filters']['status']['id'] = 'status';
  $handler->display->display_options['filters']['status']['table'] = 'linkchecker_link';
  $handler->display->display_options['filters']['status']['field'] = 'status';
  $handler->display->display_options['filters']['status']['value'] = '1';
  $handler->display->display_options['filters']['status']['group'] = 1;
  $handler->display->display_options['filters']['status']['expose']['operator_id'] = '';
  $handler->display->display_options['filters']['status']['expose']['label'] = 'Check link status';
  $handler->display->display_options['filters']['status']['expose']['operator'] = 'status_op';
  $handler->display->display_options['filters']['status']['expose']['identifier'] = 'status';
  $handler->display->display_options['filters']['status']['expose']['required'] = TRUE;
  /* Filter criterion: Broken links: HTTP status code */
  $handler->display->display_options['filters']['code_1']['id'] = 'code_1';
  $handler->display->display_options['filters']['code_1']['table'] = 'linkchecker_link';
  $handler->display->display_options['filters']['code_1']['field'] = 'code';
  $handler->display->display_options['filters']['code_1']['operator'] = 'not in';
  $handler->display->display_options['filters']['code_1']['value'] = array(
    200 => '200',
    301 => '301',
    302 => '302',
  );
  $handler->display->display_options['filters']['code_1']['group'] = 1;
  /* Filter criterion: Broken links: HTTP status code */
  $handler->display->display_options['filters']['code']['id'] = 'code';
  $handler->display->display_options['filters']['code']['table'] = 'linkchecker_link';
  $handler->display->display_options['filters']['code']['field'] = 'code';
  $handler->display->display_options['filters']['code']['value'] = array(
    100 => '100',
    101 => '101',
    201 => '201',
    202 => '202',
    203 => '203',
    204 => '204',
    205 => '205',
    206 => '206',
    300 => '300',
    301 => '301',
    302 => '302',
    303 => '303',
    304 => '304',
    305 => '305',
    307 => '307',
    400 => '400',
    401 => '401',
    402 => '402',
    403 => '403',
    404 => '404',
    405 => '405',
    406 => '406',
    407 => '407',
    408 => '408',
    409 => '409',
    410 => '410',
    411 => '411',
    412 => '412',
    413 => '413',
    414 => '414',
    415 => '415',
    416 => '416',
    417 => '417',
    500 => '500',
    501 => '501',
    502 => '502',
    503 => '503',
    504 => '504',
    505 => '505',
    -10053 => '-10053',
    -11001 => '-11001',
  );
  $handler->display->display_options['filters']['code']['group'] = 1;
  $handler->display->display_options['filters']['code']['exposed'] = TRUE;
  $handler->display->display_options['filters']['code']['expose']['operator_id'] = 'code_op';
  $handler->display->display_options['filters']['code']['expose']['label'] = 'HTTP status code';
  $handler->display->display_options['filters']['code']['expose']['operator'] = 'code_op';
  $handler->display->display_options['filters']['code']['expose']['identifier'] = 'code';
  $handler->display->display_options['filters']['code']['expose']['remember_roles'] = array(
    2 => '2',
    11 => 0,
    1 => 0,
    21 => 0,
    16 => 0,
    26 => 0,
  );
  $handler->display->display_options['filters']['code']['expose']['reduce'] = TRUE;
  /* Filter criterion: Content: Type */
  $handler->display->display_options['filters']['type']['id'] = 'type';
  $handler->display->display_options['filters']['type']['table'] = 'node';
  $handler->display->display_options['filters']['type']['field'] = 'type';
  $handler->display->display_options['filters']['type']['relationship'] = 'nid';
  $handler->display->display_options['filters']['type']['value'] = array(
    'dataset' => 'dataset',
    'harvest_source' => 'harvest_source',
    'resource' => 'resource',
  );
  $handler->display->display_options['filters']['type']['group'] = 1;
  $handler->display->display_options['filters']['type']['exposed'] = TRUE;
  $handler->display->display_options['filters']['type']['expose']['operator_id'] = 'type_op';
  $handler->display->display_options['filters']['type']['expose']['label'] = 'Type';
  $handler->display->display_options['filters']['type']['expose']['operator'] = 'type_op';
  $handler->display->display_options['filters']['type']['expose']['identifier'] = 'type';
  $handler->display->display_options['filters']['type']['expose']['remember_roles'] = array(
    2 => '2',
    11 => 0,
    1 => 0,
    21 => 0,
    16 => 0,
    26 => 0,
  );
  $handler->display->display_options['filters']['type']['expose']['reduce'] = TRUE;
  /* Filter criterion: Content: Public Access Level (field_public_access_level) */
  $handler->display->display_options['filters']['field_public_access_level_value']['id'] = 'field_public_access_level_value';
  $handler->display->display_options['filters']['field_public_access_level_value']['table'] = 'field_data_field_public_access_level';
  $handler->display->display_options['filters']['field_public_access_level_value']['field'] = 'field_public_access_level_value';
  $handler->display->display_options['filters']['field_public_access_level_value']['relationship'] = 'nid';
  $handler->display->display_options['filters']['field_public_access_level_value']['group'] = 1;
  $handler->display->display_options['filters']['field_public_access_level_value']['exposed'] = TRUE;
  $handler->display->display_options['filters']['field_public_access_level_value']['expose']['operator_id'] = 'field_public_access_level_value_op';
  $handler->display->display_options['filters']['field_public_access_level_value']['expose']['label'] = 'Public Access Level';
  $handler->display->display_options['filters']['field_public_access_level_value']['expose']['operator'] = 'field_public_access_level_value_op';
  $handler->display->display_options['filters']['field_public_access_level_value']['expose']['identifier'] = 'field_public_access_level_value';
  $handler->display->display_options['filters']['field_public_access_level_value']['expose']['remember_roles'] = array(
    2 => '2',
    3 => 0,
    1 => 0,
    5 => 0,
    4 => 0,
    6 => 0,
  );

  /* Display: Advanced Broken links */
  $handler = $view->new_display('page', 'Advanced Broken links', 'dkan_linkchecker_admin_report_page');
  $handler->display->display_options['defaults']['title'] = FALSE;
  $handler->display->display_options['title'] = 'Broken Links Report';
  $handler->display->display_options['display_description'] = 'Shows a list of broken links in content, with advanced filters.';
  $handler->display->display_options['defaults']['hide_admin_links'] = FALSE;
  $handler->display->display_options['path'] = 'admin/reports/dkan-linkchecker-report';
  $handler->display->display_options['menu']['type'] = 'normal';
  $handler->display->display_options['menu']['title'] = 'Broken Links Report';
  $handler->display->display_options['menu']['description'] = 'Shows a list of broken links in content, with advanced filters.';
  $handler->display->display_options['menu']['weight'] = '2';
  $handler->display->display_options['menu']['name'] = 'management';
  $handler->display->display_options['menu']['context'] = 0;
  $handler->display->display_options['menu']['context_only_inline'] = 0;
  $export['dkan_linkchecker_reports'] = $view;
  return $export;
}
