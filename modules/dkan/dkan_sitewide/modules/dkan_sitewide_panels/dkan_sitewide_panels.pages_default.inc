<?php
/**
 * @file
 * dkan_sitewide_panels.pages_default.inc
 */

/**
 * Implements hook_default_page_manager_handlers().
 */
function dkan_sitewide_panels_default_page_manager_handlers() {
  $export = array();

  $handler = new stdClass();
  $handler->disabled = FALSE; /* Edit this to true to make a default handler disabled initially */
  $handler->api_version = 1;
  $handler->name = 'node_edit_panel_context';
  $handler->task = 'node_edit';
  $handler->subtask = '';
  $handler->handler = 'panel_context';
  $handler->weight = 0;
  $handler->conf = array(
    'title' => 'Dataset Form',
    'no_blocks' => 0,
    'pipeline' => 'standard',
    'body_classes_to_remove' => '',
    'body_classes_to_add' => '',
    'css_id' => '',
    'css' => '',
    'contexts' => array(),
    'relationships' => array(),
    'access' => array(
      'plugins' => array(
        0 => array(
          'name' => 'node_type',
          'settings' => array(
            'type' => array(
              'dataset' => 'dataset',
            ),
          ),
          'context' => 'argument_node_edit_1',
          'not' => FALSE,
        ),
      ),
      'logic' => 'and',
    ),
  );
  $display = new panels_display();
  $display->layout = 'radix_bryant';
  $display->layout_settings = array();
  $display->panel_settings = array(
    'style_settings' => array(
      'default' => NULL,
      'sidebar' => NULL,
      'contentmain' => NULL,
    ),
  );
  $display->cache = array();
  $display->title = 'Add a %node:content-type';
  $display->uuid = '903225a4-77c6-466f-a0b8-201b722bde0b';
  $display->content = array();
  $display->panels = array();
    $pane = new stdClass();
    $pane->pid = 'new-c650a924-4993-4ac3-9d3c-a45656b528f4';
    $pane->panel = 'contentmain';
    $pane->type = 'form';
    $pane->subtype = 'form';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'context' => 'argument_node_edit_1',
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = 'c650a924-4993-4ac3-9d3c-a45656b528f4';
    $display->content['new-c650a924-4993-4ac3-9d3c-a45656b528f4'] = $pane;
    $display->panels['contentmain'][0] = 'new-c650a924-4993-4ac3-9d3c-a45656b528f4';
    $pane = new stdClass();
    $pane->pid = 'new-23bb3d91-0edb-4ed2-a9c3-10fdac3fc38c';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'dkan_dataset-dkan_dataset_dataset_help';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '23bb3d91-0edb-4ed2-a9c3-10fdac3fc38c';
    $display->content['new-23bb3d91-0edb-4ed2-a9c3-10fdac3fc38c'] = $pane;
    $display->panels['sidebar'][0] = 'new-23bb3d91-0edb-4ed2-a9c3-10fdac3fc38c';
  $display->hide_title = PANELS_TITLE_NONE;
  $display->title_pane = 'new-c650a924-4993-4ac3-9d3c-a45656b528f4';
  $handler->conf['display'] = $display;
  $export['node_edit_panel_context'] = $handler;

  $handler = new stdClass();
  $handler->disabled = FALSE; /* Edit this to true to make a default handler disabled initially */
  $handler->api_version = 1;
  $handler->name = 'node_edit_panel_context_2';
  $handler->task = 'node_edit';
  $handler->subtask = '';
  $handler->handler = 'panel_context';
  $handler->weight = 1;
  $handler->conf = array(
    'title' => 'Resource Form',
    'no_blocks' => 0,
    'pipeline' => 'standard',
    'body_classes_to_remove' => '',
    'body_classes_to_add' => '',
    'css_id' => '',
    'css' => '',
    'contexts' => array(),
    'relationships' => array(),
    'access' => array(
      'plugins' => array(
        0 => array(
          'name' => 'node_type',
          'settings' => array(
            'type' => array(
              'resource' => 'resource',
            ),
          ),
          'context' => 'argument_node_edit_1',
          'not' => FALSE,
        ),
      ),
      'logic' => 'and',
    ),
  );
  $display = new panels_display();
  $display->layout = 'radix_bryant';
  $display->layout_settings = array();
  $display->panel_settings = array(
    'style_settings' => array(
      'default' => NULL,
      'sidebar' => NULL,
      'contentmain' => NULL,
    ),
  );
  $display->cache = array();
  $display->title = 'Add a %node:content-type';
  $display->uuid = '903225a4-77c6-466f-a0b8-201b722bde0b';
  $display->content = array();
  $display->panels = array();
    $pane = new stdClass();
    $pane->pid = 'new-c650a924-4993-4ac3-9d3c-a45656b528f4';
    $pane->panel = 'contentmain';
    $pane->type = 'form';
    $pane->subtype = 'form';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'context' => 'argument_node_edit_1',
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = 'c650a924-4993-4ac3-9d3c-a45656b528f4';
    $display->content['new-c650a924-4993-4ac3-9d3c-a45656b528f4'] = $pane;
    $display->panels['contentmain'][0] = 'new-c650a924-4993-4ac3-9d3c-a45656b528f4';
    $pane = new stdClass();
    $pane->pid = 'new-98266867-26b3-4d5e-942c-54b0c53b51b7';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'dkan_dataset-dkan_dataset_resource_help';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '98266867-26b3-4d5e-942c-54b0c53b51b7';
    $display->content['new-98266867-26b3-4d5e-942c-54b0c53b51b7'] = $pane;
    $display->panels['sidebar'][0] = 'new-98266867-26b3-4d5e-942c-54b0c53b51b7';
  $display->hide_title = PANELS_TITLE_NONE;
  $display->title_pane = 'new-c650a924-4993-4ac3-9d3c-a45656b528f4';
  $handler->conf['display'] = $display;
  $export['node_edit_panel_context_2'] = $handler;

  $handler = new stdClass();
  $handler->disabled = FALSE; /* Edit this to true to make a default handler disabled initially */
  $handler->api_version = 1;
  $handler->name = 'node_view__resource';
  $handler->task = 'node_view';
  $handler->subtask = '';
  $handler->handler = 'panel_context';
  $handler->weight = 4;
  $handler->conf = array(
    'title' => 'Resource',
    'no_blocks' => 0,
    'pipeline' => 'standard',
    'body_classes_to_remove' => '',
    'body_classes_to_add' => '',
    'css_id' => '',
    'css' => '',
    'contexts' => array(),
    'relationships' => array(),
    'name' => 'resource',
    'access' => array(
      'plugins' => array(
        0 => array(
          'name' => 'node_type',
          'settings' => array(
            'type' => array(
              'resource' => 'resource',
            ),
          ),
          'context' => 'argument_entity_id:node_1',
          'not' => FALSE,
        ),
      ),
      'logic' => 'and',
    ),
  );
  $display = new panels_display();
  $display->layout = 'radix_sutro';
  $display->layout_settings = array();
  $display->panel_settings = array(
    'style_settings' => array(
      'default' => NULL,
      'header' => NULL,
      'column1' => NULL,
      'column2' => NULL,
      'footer' => NULL,
    ),
  );
  $display->cache = array();
  $display->title = '';
  $display->uuid = '346ac7da-4f98-4c21-96f3-a47b9eb0f238';
  $display->content = array();
  $display->panels = array();
    $pane = new stdClass();
    $pane->pid = 'new-0301d504-1b1e-471b-8239-f207759bb981';
    $pane->panel = 'column1';
    $pane->type = 'block';
    $pane->subtype = 'dkan_dataset-dkan_dataset_resource_nodes';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
      'override_title_heading' => 'h2',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '0301d504-1b1e-471b-8239-f207759bb981';
    $display->content['new-0301d504-1b1e-471b-8239-f207759bb981'] = $pane;
    $display->panels['column1'][0] = 'new-0301d504-1b1e-471b-8239-f207759bb981';
    $pane = new stdClass();
    $pane->pid = 'new-c82392fb-3b88-440e-8485-2c2ec42344be';
    $pane->panel = 'column2';
    $pane->type = 'block';
    $pane->subtype = 'dkan_sitewide-dkan_sitewide_resource_add';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
      'override_title_heading' => 'h2',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = 'c82392fb-3b88-440e-8485-2c2ec42344be';
    $display->content['new-c82392fb-3b88-440e-8485-2c2ec42344be'] = $pane;
    $display->panels['column2'][0] = 'new-c82392fb-3b88-440e-8485-2c2ec42344be';
    $pane = new stdClass();
    $pane->pid = 'new-91c4fc62-fd2f-4e23-bd4c-ae4973d4ca46';
    $pane->panel = 'header';
    $pane->type = 'node';
    $pane->subtype = 'node';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'nid' => '%1',
      'links' => 0,
      'leave_node_title' => 0,
      'identifier' => '',
      'build_mode' => 'full',
      'link_node_title' => 0,
      'override_title' => 0,
      'override_title_text' => '',
      'override_title_heading' => 'h2',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array(
      'css_id' => '',
      'css_class' => 'radix-layouts-content',
    );
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '91c4fc62-fd2f-4e23-bd4c-ae4973d4ca46';
    $display->content['new-91c4fc62-fd2f-4e23-bd4c-ae4973d4ca46'] = $pane;
    $display->panels['header'][0] = 'new-91c4fc62-fd2f-4e23-bd4c-ae4973d4ca46';
  $display->hide_title = PANELS_TITLE_PANE;
  $display->title_pane = '0';
  $handler->conf['display'] = $display;
  $export['node_view__resource'] = $handler;

  $handler = new stdClass();
  $handler->disabled = FALSE; /* Edit this to true to make a default handler disabled initially */
  $handler->api_version = 1;
  $handler->name = 'node_view_panel_context';
  $handler->task = 'node_view';
  $handler->subtask = '';
  $handler->handler = 'panel_context';
  $handler->weight = 3;
  $handler->conf = array(
    'title' => 'Dataset',
    'no_blocks' => 0,
    'pipeline' => 'ipe',
    'body_classes_to_remove' => '',
    'body_classes_to_add' => '',
    'css_id' => '',
    'css' => '',
    'contexts' => array(),
    'relationships' => array(),
    'access' => array(
      'plugins' => array(
        0 => array(
          'name' => 'node_type',
          'settings' => array(
            'type' => array(
              'dataset' => 'dataset',
            ),
          ),
          'context' => 'argument_entity_id:node_1',
          'not' => FALSE,
        ),
      ),
      'logic' => 'and',
    ),
  );
  $display = new panels_display();
  $display->layout = 'radix_bryant';
  $display->layout_settings = array();
  $display->panel_settings = array(
    'style_settings' => array(
      'default' => NULL,
      'sidebar' => NULL,
      'contentmain' => NULL,
    ),
  );
  $display->cache = array();
  $display->title = '';
  $display->uuid = '7a09b6f7-196b-421c-a080-418d6588fed2';
  $display->content = array();
  $display->panels = array();
    $pane = new stdClass();
    $pane->pid = 'new-5771913e-f897-497d-9d56-f76f6b207b18';
    $pane->panel = 'contentmain';
    $pane->type = 'node';
    $pane->subtype = 'node';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'nid' => '%1',
      'links' => 1,
      'leave_node_title' => 0,
      'identifier' => '',
      'build_mode' => 'full',
      'link_node_title' => 0,
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '5771913e-f897-497d-9d56-f76f6b207b18';
    $display->content['new-5771913e-f897-497d-9d56-f76f6b207b18'] = $pane;
    $display->panels['contentmain'][0] = 'new-5771913e-f897-497d-9d56-f76f6b207b18';
    $pane = new stdClass();
    $pane->pid = 'new-25ca95bb-18cd-4175-8cbd-5d44b3b08e20';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'views-group_block-block';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '25ca95bb-18cd-4175-8cbd-5d44b3b08e20';
    $display->content['new-25ca95bb-18cd-4175-8cbd-5d44b3b08e20'] = $pane;
    $display->panels['sidebar'][0] = 'new-25ca95bb-18cd-4175-8cbd-5d44b3b08e20';
    $pane = new stdClass();
    $pane->pid = 'new-3c3f5701-3799-45f9-bfa6-444fa0f25dc5';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'dkan_sitewide-dkan_sitewide_data_extent';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 1;
    $pane->locks = array();
    $pane->uuid = '3c3f5701-3799-45f9-bfa6-444fa0f25dc5';
    $display->content['new-3c3f5701-3799-45f9-bfa6-444fa0f25dc5'] = $pane;
    $display->panels['sidebar'][1] = 'new-3c3f5701-3799-45f9-bfa6-444fa0f25dc5';
    $pane = new stdClass();
    $pane->pid = 'new-03b64023-23f0-4fd9-8725-138db6d57b5e';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'dkan_sitewide-dkan_sitewide_license';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 2;
    $pane->locks = array();
    $pane->uuid = '03b64023-23f0-4fd9-8725-138db6d57b5e';
    $display->content['new-03b64023-23f0-4fd9-8725-138db6d57b5e'] = $pane;
    $display->panels['sidebar'][2] = 'new-03b64023-23f0-4fd9-8725-138db6d57b5e';
    $pane = new stdClass();
    $pane->pid = 'new-4e02f451-58f6-4c70-87d5-69945e2f44c4';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'dkan_sitewide-dkan_sitewide_other_access';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 3;
    $pane->locks = array();
    $pane->uuid = '4e02f451-58f6-4c70-87d5-69945e2f44c4';
    $display->content['new-4e02f451-58f6-4c70-87d5-69945e2f44c4'] = $pane;
    $display->panels['sidebar'][3] = 'new-4e02f451-58f6-4c70-87d5-69945e2f44c4';
    $pane = new stdClass();
    $pane->pid = 'new-a3d3ba71-c2c5-4eb0-8ac8-77b7709264a8';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'dkan_sitewide-dkan_sitewide_social';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 4;
    $pane->locks = array();
    $pane->uuid = 'a3d3ba71-c2c5-4eb0-8ac8-77b7709264a8';
    $display->content['new-a3d3ba71-c2c5-4eb0-8ac8-77b7709264a8'] = $pane;
    $display->panels['sidebar'][4] = 'new-a3d3ba71-c2c5-4eb0-8ac8-77b7709264a8';
  $display->hide_title = PANELS_TITLE_PANE;
  $display->title_pane = 'new-5771913e-f897-497d-9d56-f76f6b207b18';
  $handler->conf['display'] = $display;
  $export['node_view_panel_context'] = $handler;

  $handler = new stdClass();
  $handler->disabled = FALSE; /* Edit this to true to make a default handler disabled initially */
  $handler->api_version = 1;
  $handler->name = 'node_view_panel_context_2';
  $handler->task = 'node_view';
  $handler->subtask = '';
  $handler->handler = 'panel_context';
  $handler->weight = 1;
  $handler->conf = array(
    'title' => 'Page',
    'no_blocks' => 0,
    'pipeline' => 'standard',
    'body_classes_to_remove' => '',
    'body_classes_to_add' => '',
    'css_id' => '',
    'css' => '',
    'contexts' => array(),
    'relationships' => array(),
    'access' => array(
      'plugins' => array(
        0 => array(
          'name' => 'node_type',
          'settings' => array(
            'type' => array(
              'page' => 'page',
            ),
          ),
          'context' => 'argument_entity_id:node_1',
          'not' => FALSE,
        ),
      ),
      'logic' => 'and',
    ),
  );
  $display = new panels_display();
  $display->layout = 'radix_boxton';
  $display->layout_settings = array();
  $display->panel_settings = array(
    'style_settings' => array(
      'default' => NULL,
      'sidebar' => NULL,
      'contentmain' => NULL,
    ),
  );
  $display->cache = array();
  $display->title = '';
  $display->uuid = '7a09b6f7-196b-421c-a080-418d6588fed2';
  $display->content = array();
  $display->panels = array();
    $pane = new stdClass();
    $pane->pid = 'new-93b93af5-e162-416f-813d-06e7be118cb7';
    $pane->panel = 'contentmain';
    $pane->type = 'node_content';
    $pane->subtype = 'node_content';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'links' => 1,
      'no_extras' => 1,
      'override_title' => 0,
      'override_title_text' => '',
      'identifier' => '',
      'link' => 0,
      'leave_node_title' => 0,
      'build_mode' => 'full',
      'context' => 'argument_entity_id:node_1',
      'override_title_heading' => 'h2',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '93b93af5-e162-416f-813d-06e7be118cb7';
    $display->content['new-93b93af5-e162-416f-813d-06e7be118cb7'] = $pane;
    $display->panels['contentmain'][0] = 'new-93b93af5-e162-416f-813d-06e7be118cb7';
  $display->hide_title = PANELS_TITLE_PANE;
  $display->title_pane = '0';
  $handler->conf['display'] = $display;
  $export['node_view_panel_context_2'] = $handler;

  $handler = new stdClass();
  $handler->disabled = FALSE; /* Edit this to true to make a default handler disabled initially */
  $handler->api_version = 1;
  $handler->name = 'node_view_panel_context_3';
  $handler->task = 'node_view';
  $handler->subtask = '';
  $handler->handler = 'panel_context';
  $handler->weight = 2;
  $handler->conf = array(
    'title' => 'Group',
    'no_blocks' => 0,
    'pipeline' => 'standard',
    'body_classes_to_remove' => '',
    'body_classes_to_add' => '',
    'css_id' => '',
    'css' => '',
    'contexts' => array(),
    'relationships' => array(),
    'access' => array(
      'plugins' => array(
        0 => array(
          'name' => 'node_type',
          'settings' => array(
            'type' => array(
              'group' => 'group',
            ),
          ),
          'context' => 'argument_entity_id:node_1',
          'not' => FALSE,
        ),
      ),
      'logic' => 'and',
    ),
  );
  $display = new panels_display();
  $display->layout = 'radix_bryant';
  $display->layout_settings = array();
  $display->panel_settings = array(
    'style_settings' => array(
      'default' => NULL,
      'sidebar' => NULL,
      'contentmain' => NULL,
    ),
  );
  $display->cache = array();
  $display->title = '';
  $display->uuid = '8e2bef12-a059-414b-afa4-5b37861c0b09';
  $display->content = array();
  $display->panels = array();
    $pane = new stdClass();
    $pane->pid = 'new-925eab7e-c3df-4309-bd73-7c3c73727fba';
    $pane->panel = 'contentmain';
    $pane->type = 'node_content';
    $pane->subtype = 'node_content';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'links' => 1,
      'no_extras' => 1,
      'override_title' => 0,
      'override_title_text' => '',
      'identifier' => '',
      'link' => 0,
      'leave_node_title' => 0,
      'build_mode' => 'full',
      'context' => 'argument_entity_id:node_1',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '925eab7e-c3df-4309-bd73-7c3c73727fba';
    $display->content['new-925eab7e-c3df-4309-bd73-7c3c73727fba'] = $pane;
    $display->panels['contentmain'][0] = 'new-925eab7e-c3df-4309-bd73-7c3c73727fba';
    $pane = new stdClass();
    $pane->pid = 'new-4e2d9246-e27b-4b4a-961e-b4718e53117c';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'views-group_block-block';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '4e2d9246-e27b-4b4a-961e-b4718e53117c';
    $display->content['new-4e2d9246-e27b-4b4a-961e-b4718e53117c'] = $pane;
    $display->panels['sidebar'][0] = 'new-4e2d9246-e27b-4b4a-961e-b4718e53117c';
    $pane = new stdClass();
    $pane->pid = 'new-3e8ea599-5b82-4fb2-af0f-ce8f0a6b58be';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'facetapi-GZUUj6TgVjA9Nwt0HvjnjSO2uDxLxfy7';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 1;
    $pane->locks = array();
    $pane->uuid = '3e8ea599-5b82-4fb2-af0f-ce8f0a6b58be';
    $display->content['new-3e8ea599-5b82-4fb2-af0f-ce8f0a6b58be'] = $pane;
    $display->panels['sidebar'][1] = 'new-3e8ea599-5b82-4fb2-af0f-ce8f0a6b58be';
    $pane = new stdClass();
    $pane->pid = 'new-0720de5b-6051-4f54-948b-c62d1074f82e';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'facetapi-lzvCI5xSUC94irLWjBH4WoOTqXVxs8FR';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 2;
    $pane->locks = array();
    $pane->uuid = '0720de5b-6051-4f54-948b-c62d1074f82e';
    $display->content['new-0720de5b-6051-4f54-948b-c62d1074f82e'] = $pane;
    $display->panels['sidebar'][2] = 'new-0720de5b-6051-4f54-948b-c62d1074f82e';
    $pane = new stdClass();
    $pane->pid = 'new-5b7571be-ff82-48eb-8855-16ddcd379a9d';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'facetapi-xNAidW9x2foW6UGpYpK8MzTaBT8ebK4D';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 3;
    $pane->locks = array();
    $pane->uuid = '5b7571be-ff82-48eb-8855-16ddcd379a9d';
    $display->content['new-5b7571be-ff82-48eb-8855-16ddcd379a9d'] = $pane;
    $display->panels['sidebar'][3] = 'new-5b7571be-ff82-48eb-8855-16ddcd379a9d';
    $pane = new stdClass();
    $pane->pid = 'new-58708ed1-8465-4976-887b-a764f6bc6e4f';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'facetapi-n9S7gglvd3lMrVMu760QrIyeZAKByKsX';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 4;
    $pane->locks = array();
    $pane->uuid = '58708ed1-8465-4976-887b-a764f6bc6e4f';
    $display->content['new-58708ed1-8465-4976-887b-a764f6bc6e4f'] = $pane;
    $display->panels['sidebar'][4] = 'new-58708ed1-8465-4976-887b-a764f6bc6e4f';
  $display->hide_title = PANELS_TITLE_PANE;
  $display->title_pane = 'new-925eab7e-c3df-4309-bd73-7c3c73727fba';
  $handler->conf['display'] = $display;
  $export['node_view_panel_context_3'] = $handler;

  $handler = new stdClass();
  $handler->disabled = FALSE; /* Edit this to true to make a default handler disabled initially */
  $handler->api_version = 1;
  $handler->name = 'user_view_panel_context';
  $handler->task = 'user_view';
  $handler->subtask = '';
  $handler->handler = 'panel_context';
  $handler->weight = 0;
  $handler->conf = array(
    'title' => 'User View',
    'no_blocks' => 0,
    'pipeline' => 'standard',
    'body_classes_to_remove' => '',
    'body_classes_to_add' => '',
    'css_id' => '',
    'css' => '',
    'contexts' => array(),
    'relationships' => array(),
  );
  $display = new panels_display();
  $display->layout = 'radix_bryant';
  $display->layout_settings = array();
  $display->panel_settings = array(
    'style_settings' => array(
      'default' => NULL,
      'sidebar' => NULL,
      'contentmain' => NULL,
    ),
  );
  $display->cache = array();
  $display->title = '';
  $display->uuid = 'fb08d023-1c59-4d27-aebf-e2e55e7ef37c';
  $display->content = array();
  $display->panels = array();
    $pane = new stdClass();
    $pane->pid = 'new-24319b70-9d20-4220-82bf-7b156d32a4fc';
    $pane->panel = 'contentmain';
    $pane->type = 'user_profile';
    $pane->subtype = 'user_profile';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'view_mode' => 'full',
      'context' => 'argument_entity_id:user_1',
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '24319b70-9d20-4220-82bf-7b156d32a4fc';
    $display->content['new-24319b70-9d20-4220-82bf-7b156d32a4fc'] = $pane;
    $display->panels['contentmain'][0] = 'new-24319b70-9d20-4220-82bf-7b156d32a4fc';
    $pane = new stdClass();
    $pane->pid = 'new-fb253b49-d228-4e77-ad01-4fc83600ba06';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'views-user_profile_fields-block';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = 'fb253b49-d228-4e77-ad01-4fc83600ba06';
    $display->content['new-fb253b49-d228-4e77-ad01-4fc83600ba06'] = $pane;
    $display->panels['sidebar'][0] = 'new-fb253b49-d228-4e77-ad01-4fc83600ba06';
    $pane = new stdClass();
    $pane->pid = 'new-b5ed2517-658a-473d-932e-dd69b664a49e';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'facetapi-y0bmt1wN50B9oCuMeoazc1hpKiMkSQ2A';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 1;
    $pane->locks = array();
    $pane->uuid = 'b5ed2517-658a-473d-932e-dd69b664a49e';
    $display->content['new-b5ed2517-658a-473d-932e-dd69b664a49e'] = $pane;
    $display->panels['sidebar'][1] = 'new-b5ed2517-658a-473d-932e-dd69b664a49e';
    $pane = new stdClass();
    $pane->pid = 'new-9602946a-fe91-45cf-9744-a01fdf79436a';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'facetapi-VBBDnNWYupC3PxCnXOq4e4ds4sAU4W7s';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 2;
    $pane->locks = array();
    $pane->uuid = '9602946a-fe91-45cf-9744-a01fdf79436a';
    $display->content['new-9602946a-fe91-45cf-9744-a01fdf79436a'] = $pane;
    $display->panels['sidebar'][2] = 'new-9602946a-fe91-45cf-9744-a01fdf79436a';
    $pane = new stdClass();
    $pane->pid = 'new-d6f746e0-dffc-4ae9-a4fb-b4d70474a18d';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'facetapi-yW6mJ3GaJF0LypnLPa433Bf9GI8QdWd9';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 3;
    $pane->locks = array();
    $pane->uuid = 'd6f746e0-dffc-4ae9-a4fb-b4d70474a18d';
    $display->content['new-d6f746e0-dffc-4ae9-a4fb-b4d70474a18d'] = $pane;
    $display->panels['sidebar'][3] = 'new-d6f746e0-dffc-4ae9-a4fb-b4d70474a18d';
    $pane = new stdClass();
    $pane->pid = 'new-b77564f7-b847-4b0d-a2e3-a88c83ac8ee2';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'facetapi-dqCRnp6cKP1cSzAsNB6zqFtY9AVEa0mp';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 4;
    $pane->locks = array();
    $pane->uuid = 'b77564f7-b847-4b0d-a2e3-a88c83ac8ee2';
    $display->content['new-b77564f7-b847-4b0d-a2e3-a88c83ac8ee2'] = $pane;
    $display->panels['sidebar'][4] = 'new-b77564f7-b847-4b0d-a2e3-a88c83ac8ee2';
  $display->hide_title = PANELS_TITLE_FIXED;
  $display->title_pane = 'new-24319b70-9d20-4220-82bf-7b156d32a4fc';
  $handler->conf['display'] = $display;
  $export['user_view_panel_context'] = $handler;

  return $export;
}

/**
 * Implements hook_default_page_manager_pages().
 */
function dkan_sitewide_panels_default_page_manager_pages() {
  $page = new stdClass();
  $page->disabled = FALSE; /* Edit this to true to make a default page disabled initially */
  $page->api_version = 1;
  $page->name = 'datasets';
  $page->task = 'page';
  $page->admin_title = 'Datasets';
  $page->admin_description = '';
  $page->path = 'dataset';
  $page->access = array(
    'type' => 'none',
    'settings' => NULL,
  );
  $page->menu = array(
    'type' => 'normal',
    'title' => 'Datasets',
    'name' => 'main-menu',
    'weight' => '-6',
    'parent' => array(
      'type' => 'none',
      'title' => '',
      'name' => 'navigation',
      'weight' => '0',
    ),
  );
  $page->arguments = array();
  $page->conf = array(
    'admin_paths' => FALSE,
  );
  $page->default_handlers = array();
  $handler = new stdClass();
  $handler->disabled = FALSE; /* Edit this to true to make a default handler disabled initially */
  $handler->api_version = 1;
  $handler->name = 'page_datasets_panel_context';
  $handler->task = 'page';
  $handler->subtask = 'datasets';
  $handler->handler = 'panel_context';
  $handler->weight = 0;
  $handler->conf = array(
    'title' => 'Panel',
    'no_blocks' => 0,
    'pipeline' => 'ipe',
    'body_classes_to_remove' => '',
    'body_classes_to_add' => '',
    'css_id' => '',
    'css' => '',
    'contexts' => array(),
    'relationships' => array(),
    'access' => array(
      'logic' => 'and',
    ),
  );
  $display = new panels_display();
  $display->layout = 'radix_bryant';
  $display->layout_settings = array(
    'items' => array(
      'canvas' => array(
        'type' => 'row',
        'contains' => 'column',
        'children' => array(
          0 => 'main',
        ),
        'parent' => NULL,
      ),
      'main' => array(
        'type' => 'column',
        'width' => 100,
        'width_type' => '%',
        'children' => array(
          0 => 'main-row',
        ),
        'parent' => 'canvas',
      ),
      'main-row' => array(
        'type' => 'row',
        'contains' => 'region',
        'children' => array(
          0 => 'sidebar_left',
          1 => 'center',
        ),
        'parent' => 'main',
      ),
      'center' => array(
        'type' => 'region',
        'title' => 'Center',
        'width' => 50,
        'width_type' => '%',
        'parent' => 'main-row',
      ),
      'sidebar_left' => array(
        'type' => 'region',
        'title' => 'sidebar-left',
        'width' => 50,
        'width_type' => '%',
        'parent' => 'main-row',
        'class' => '',
      ),
    ),
  );
  $display->panel_settings = array(
    'style_settings' => array(
      'default' => NULL,
      'center' => NULL,
      'sidebar_left' => NULL,
      'left' => NULL,
      'middle' => NULL,
      'right' => NULL,
      'contentmain' => NULL,
      'sidebar' => NULL,
    ),
    'sidebar' => array(
      'style' => 'naked',
    ),
  );
  $display->cache = array();
  $display->title = '';
  $display->uuid = 'b1ebec5e-aed7-4ebc-82de-7e9eac72c35a';
  $display->content = array();
  $display->panels = array();
    $pane = new stdClass();
    $pane->pid = 'new-1ee5d84c-1ea4-4726-ba4d-291779f9c493';
    $pane->panel = 'contentmain';
    $pane->type = 'views_panes';
    $pane->subtype = 'dkan_datasets-panel_pane_1';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array();
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '1ee5d84c-1ea4-4726-ba4d-291779f9c493';
    $display->content['new-1ee5d84c-1ea4-4726-ba4d-291779f9c493'] = $pane;
    $display->panels['contentmain'][0] = 'new-1ee5d84c-1ea4-4726-ba4d-291779f9c493';
    $pane = new stdClass();
    $pane->pid = 'new-af542600-62f2-4baf-b2c3-12b9ee1d59ba';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'facetapi-dqCRnp6cKP1cSzAsNB6zqFtY9AVEa0mp';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = 'af542600-62f2-4baf-b2c3-12b9ee1d59ba';
    $display->content['new-af542600-62f2-4baf-b2c3-12b9ee1d59ba'] = $pane;
    $display->panels['sidebar'][0] = 'new-af542600-62f2-4baf-b2c3-12b9ee1d59ba';
    $pane = new stdClass();
    $pane->pid = 'new-549159dd-e073-4e10-9307-b13fad742409';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'facetapi-yW6mJ3GaJF0LypnLPa433Bf9GI8QdWd9';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 1;
    $pane->locks = array();
    $pane->uuid = '549159dd-e073-4e10-9307-b13fad742409';
    $display->content['new-549159dd-e073-4e10-9307-b13fad742409'] = $pane;
    $display->panels['sidebar'][1] = 'new-549159dd-e073-4e10-9307-b13fad742409';
    $pane = new stdClass();
    $pane->pid = 'new-6d7e00a0-22d3-4c44-b96e-f4a08ef807ef';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'facetapi-NbnMlu4YH5xxKGpioOhBeBZI0jn060c0';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 2;
    $pane->locks = array();
    $pane->uuid = '6d7e00a0-22d3-4c44-b96e-f4a08ef807ef';
    $display->content['new-6d7e00a0-22d3-4c44-b96e-f4a08ef807ef'] = $pane;
    $display->panels['sidebar'][2] = 'new-6d7e00a0-22d3-4c44-b96e-f4a08ef807ef';
    $pane = new stdClass();
    $pane->pid = 'new-b0fb8ba0-c78f-4b2e-9ae3-e4cc61f06345';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'facetapi-VBBDnNWYupC3PxCnXOq4e4ds4sAU4W7s';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 3;
    $pane->locks = array();
    $pane->uuid = 'b0fb8ba0-c78f-4b2e-9ae3-e4cc61f06345';
    $display->content['new-b0fb8ba0-c78f-4b2e-9ae3-e4cc61f06345'] = $pane;
    $display->panels['sidebar'][3] = 'new-b0fb8ba0-c78f-4b2e-9ae3-e4cc61f06345';
    $pane = new stdClass();
    $pane->pid = 'new-51f2a5b5-ab6a-474a-b0f8-4a0b297160a8';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'facetapi-wsVI1ENUXwf4Rz08n9fg2WvfQ0Gs5h2a';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 4;
    $pane->locks = array();
    $pane->uuid = '51f2a5b5-ab6a-474a-b0f8-4a0b297160a8';
    $display->content['new-51f2a5b5-ab6a-474a-b0f8-4a0b297160a8'] = $pane;
    $display->panels['sidebar'][4] = 'new-51f2a5b5-ab6a-474a-b0f8-4a0b297160a8';
    $pane = new stdClass();
    $pane->pid = 'new-6c414a50-f4f5-4a1d-a7dd-e552e12a208a';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'facetapi-n9S7gglvd3lMrVMu760QrIyeZAKByKsX';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '%title',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 5;
    $pane->locks = array();
    $pane->uuid = '6c414a50-f4f5-4a1d-a7dd-e552e12a208a';
    $display->content['new-6c414a50-f4f5-4a1d-a7dd-e552e12a208a'] = $pane;
    $display->panels['sidebar'][5] = 'new-6c414a50-f4f5-4a1d-a7dd-e552e12a208a';
    $pane = new stdClass();
    $pane->pid = 'new-0541c9a5-2e8c-488f-8db4-4cd064bdc903';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'facetapi-y0bmt1wN50B9oCuMeoazc1hpKiMkSQ2A';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 6;
    $pane->locks = array();
    $pane->uuid = '0541c9a5-2e8c-488f-8db4-4cd064bdc903';
    $display->content['new-0541c9a5-2e8c-488f-8db4-4cd064bdc903'] = $pane;
    $display->panels['sidebar'][6] = 'new-0541c9a5-2e8c-488f-8db4-4cd064bdc903';
  $display->hide_title = PANELS_TITLE_FIXED;
  $display->title_pane = '0';
  $handler->conf['display'] = $display;
  $page->default_handlers[$handler->name] = $handler;
  $pages['datasets'] = $page;

  $page = new stdClass();
  $page->disabled = FALSE; /* Edit this to true to make a default page disabled initially */
  $page->api_version = 1;
  $page->name = 'front_page';
  $page->task = 'page';
  $page->admin_title = 'Front page';
  $page->admin_description = '';
  $page->path = 'welcome';
  $page->access = array(
    'type' => 'none',
    'settings' => NULL,
  );
  $page->menu = array();
  $page->arguments = array();
  $page->conf = array(
    'admin_paths' => FALSE,
  );
  $page->default_handlers = array();
  $handler = new stdClass();
  $handler->disabled = FALSE; /* Edit this to true to make a default handler disabled initially */
  $handler->api_version = 1;
  $handler->name = 'page_front_page_panel_context';
  $handler->task = 'page';
  $handler->subtask = 'front_page';
  $handler->handler = 'panel_context';
  $handler->weight = 0;
  $handler->conf = array(
    'title' => 'Panel',
    'no_blocks' => 0,
    'pipeline' => 'ipe',
    'body_classes_to_remove' => '',
    'body_classes_to_add' => '',
    'css_id' => '',
    'css' => '',
    'contexts' => array(),
    'relationships' => array(),
    'access' => array(
      'plugins' => array(),
      'logic' => 'and',
    ),
  );
  $display = new panels_display();
  $display->layout = 'full_width';
  $display->layout_settings = array();
  $display->panel_settings = array(
    'style_settings' => array(
      'default' => NULL,
      'header' => NULL,
      'column1' => NULL,
      'column2' => NULL,
      'middle' => NULL,
      'secondcolumn1' => NULL,
      'secondcolumn2' => NULL,
      'footer' => NULL,
      'top-first' => NULL,
      'top-second' => NULL,
      'bottom-first' => NULL,
      'bottom-second' => NULL,
      'bottom-third' => NULL,
      'bottom-fourth' => NULL,
    ),
  );
  $display->cache = array();
  $display->title = '';
  $display->uuid = '53a00d34-5bc4-4b11-aad6-a5615c73e388';
  $display->content = array();
  $display->panels = array();
    $pane = new stdClass();
    $pane->pid = 'new-8aa431e2-a9e8-4d73-b043-aa57ecbf0947';
    $pane->panel = 'middle';
    $pane->type = 'block';
    $pane->subtype = 'views-front_page_group_grid-block';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '8aa431e2-a9e8-4d73-b043-aa57ecbf0947';
    $display->content['new-8aa431e2-a9e8-4d73-b043-aa57ecbf0947'] = $pane;
    $display->panels['middle'][0] = 'new-8aa431e2-a9e8-4d73-b043-aa57ecbf0947';
    $pane = new stdClass();
    $pane->pid = 'new-d3655ff2-8c3c-4a0b-b492-8aa98ea1b7f3';
    $pane->panel = 'top-first';
    $pane->type = 'block';
    $pane->subtype = 'dkan_sitewide_demo_front-dkan_demo_front';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = 'd3655ff2-8c3c-4a0b-b492-8aa98ea1b7f3';
    $display->content['new-d3655ff2-8c3c-4a0b-b492-8aa98ea1b7f3'] = $pane;
    $display->panels['top-first'][0] = 'new-d3655ff2-8c3c-4a0b-b492-8aa98ea1b7f3';
    $pane = new stdClass();
    $pane->pid = 'new-e9a59ddf-15b0-4a29-8ef6-ef460ba539e0';
    $pane->panel = 'top-second';
    $pane->type = 'block';
    $pane->subtype = 'dkan_sitewide_demo_front-dkan_add_front';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = 'e9a59ddf-15b0-4a29-8ef6-ef460ba539e0';
    $display->content['new-e9a59ddf-15b0-4a29-8ef6-ef460ba539e0'] = $pane;
    $display->panels['top-second'][0] = 'new-e9a59ddf-15b0-4a29-8ef6-ef460ba539e0';
    $pane = new stdClass();
    $pane->pid = 'new-949696cf-520f-40a4-af80-725bca8b2f13';
    $pane->panel = 'top-second';
    $pane->type = 'block';
    $pane->subtype = 'views-popular_tags-block';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 1;
    $pane->locks = array();
    $pane->uuid = '949696cf-520f-40a4-af80-725bca8b2f13';
    $display->content['new-949696cf-520f-40a4-af80-725bca8b2f13'] = $pane;
    $display->panels['top-second'][1] = 'new-949696cf-520f-40a4-af80-725bca8b2f13';
    $pane = new stdClass();
    $pane->pid = 'new-11969a4b-795a-40df-8d66-91f6daec6c20';
    $pane->panel = 'top-second';
    $pane->type = 'block';
    $pane->subtype = 'dkan_sitewide-dkan_sitewide_search_bar';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 2;
    $pane->locks = array();
    $pane->uuid = '11969a4b-795a-40df-8d66-91f6daec6c20';
    $display->content['new-11969a4b-795a-40df-8d66-91f6daec6c20'] = $pane;
    $display->panels['top-second'][2] = 'new-11969a4b-795a-40df-8d66-91f6daec6c20';
  $display->hide_title = PANELS_TITLE_NONE;
  $display->title_pane = 'new-e9a59ddf-15b0-4a29-8ef6-ef460ba539e0';
  $handler->conf['display'] = $display;
  $page->default_handlers[$handler->name] = $handler;
  $pages['front_page'] = $page;

  $page = new stdClass();
  $page->disabled = FALSE; /* Edit this to true to make a default page disabled initially */
  $page->api_version = 1;
  $page->name = 'groups';
  $page->task = 'page';
  $page->admin_title = 'Groups';
  $page->admin_description = '';
  $page->path = 'groups';
  $page->access = array(
    'type' => 'none',
    'settings' => NULL,
  );
  $page->menu = array(
    'type' => 'normal',
    'title' => 'Groups',
    'name' => 'main-menu',
    'weight' => '2',
    'parent' => array(
      'type' => 'none',
      'title' => '',
      'name' => 'navigation',
      'weight' => '0',
    ),
  );
  $page->arguments = array();
  $page->conf = array(
    'admin_paths' => FALSE,
  );
  $page->default_handlers = array();
  $handler = new stdClass();
  $handler->disabled = FALSE; /* Edit this to true to make a default handler disabled initially */
  $handler->api_version = 1;
  $handler->name = 'page_groups_panel_context';
  $handler->task = 'page';
  $handler->subtask = 'groups';
  $handler->handler = 'panel_context';
  $handler->weight = 0;
  $handler->conf = array(
    'title' => 'Panel',
    'no_blocks' => 0,
    'pipeline' => 'standard',
    'body_classes_to_remove' => '',
    'body_classes_to_add' => '',
    'css_id' => '',
    'css' => '',
    'contexts' => array(),
    'relationships' => array(),
  );
  $display = new panels_display();
  $display->layout = 'radix_boxton';
  $display->layout_settings = array();
  $display->panel_settings = array(
    'style_settings' => array(
      'default' => NULL,
      'contentmain' => NULL,
    ),
  );
  $display->cache = array();
  $display->title = 'Groups';
  $display->uuid = 'a3180d51-5e38-4887-8a53-9f3561b1a654';
  $display->content = array();
  $display->panels = array();
    $pane = new stdClass();
    $pane->pid = 'new-510a2eff-125e-4cf3-a465-976055bf58a4';
    $pane->panel = 'contentmain';
    $pane->type = 'block';
    $pane->subtype = 'views-dkan_groups-block_1';
    $pane->shown = TRUE;
    $pane->access = array();
    $pane->configuration = array(
      'override_title' => 0,
      'override_title_text' => '',
    );
    $pane->cache = array();
    $pane->style = array(
      'settings' => NULL,
    );
    $pane->css = array();
    $pane->extras = array();
    $pane->position = 0;
    $pane->locks = array();
    $pane->uuid = '510a2eff-125e-4cf3-a465-976055bf58a4';
    $display->content['new-510a2eff-125e-4cf3-a465-976055bf58a4'] = $pane;
    $display->panels['contentmain'][0] = 'new-510a2eff-125e-4cf3-a465-976055bf58a4';
  $display->hide_title = PANELS_TITLE_FIXED;
  $display->title_pane = '0';
  $handler->conf['display'] = $display;
  $page->default_handlers[$handler->name] = $handler;
  $pages['groups'] = $page;

  return $pages;

}
