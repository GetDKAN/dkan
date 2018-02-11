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
  $display->storage_type = 'page_manager';
  $display->storage_id = 'node_edit_panel_context';
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
  $display->storage_type = 'page_manager';
  $display->storage_id = 'node_edit_panel_context_2';
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
  $handler->name = 'node_view__panel_context_026b4bea-efb2-4aaf-b432-11f82d63631b';
  $handler->task = 'node_view';
  $handler->subtask = '';
  $handler->handler = 'panel_context';
  $handler->weight = 5;
  $handler->conf = array(
    'title' => 'Harvest Source',
    'no_blocks' => 0,
    'pipeline' => 'standard',
    'body_classes_to_remove' => '',
    'body_classes_to_add' => '',
    'css_id' => '',
    'css' => '',
    'contexts' => array(),
    'relationships' => array(),
    'name' => '',
    'access' => array(
      'plugins' => array(
        0 => array(
          'name' => 'node_type',
          'settings' => array(
            'type' => array(
              'harvest_source' => 'harvest_source',
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
    'contentmain' => array(
      'style' => 'default',
    ),
  );
  $display->cache = array();
  $display->title = '';
  $display->uuid = '607c6ea8-9599-4067-aa8f-0b475ff07dc1';
  $display->storage_type = 'page_manager';
  $display->storage_id = 'node_view__panel_context_026b4bea-efb2-4aaf-b432-11f82d63631b';
  $display->content = array();
  $display->panels = array();
  $pane = new stdClass();
  $pane->pid = 'new-8e654a7f-3d01-4145-9c2f-230b4a9771ac';
  $pane->panel = 'contentmain';
  $pane->type = 'node_title';
  $pane->subtype = 'node_title';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'link' => 0,
    'markup' => 'h2',
    'id' => '',
    'class' => 'pane-title',
    'context' => 'argument_entity_id:node_1',
    'override_title' => 1,
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
  $pane->uuid = '8e654a7f-3d01-4145-9c2f-230b4a9771ac';
  $display->content['new-8e654a7f-3d01-4145-9c2f-230b4a9771ac'] = $pane;
  $display->panels['contentmain'][0] = 'new-8e654a7f-3d01-4145-9c2f-230b4a9771ac';
  $pane = new stdClass();
  $pane->pid = 'new-988e220f-b6c8-48f6-bdd3-747b5ea2a30a';
  $pane->panel = 'contentmain';
  $pane->type = 'entity_field';
  $pane->subtype = 'node:body';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'label' => 'hidden',
    'formatter' => 'text_default',
    'delta_limit' => 0,
    'delta_offset' => '0',
    'delta_reversed' => FALSE,
    'formatter_settings' => array(),
    'context' => 'argument_entity_id:node_1',
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
  $pane->position = 1;
  $pane->locks = array();
  $pane->uuid = '988e220f-b6c8-48f6-bdd3-747b5ea2a30a';
  $display->content['new-988e220f-b6c8-48f6-bdd3-747b5ea2a30a'] = $pane;
  $display->panels['contentmain'][1] = 'new-988e220f-b6c8-48f6-bdd3-747b5ea2a30a';
  $pane = new stdClass();
  $pane->pid = 'new-c0fb5b4d-a03a-444b-8be5-c57562638d37';
  $pane->panel = 'contentmain';
  $pane->type = 'block';
  $pane->subtype = 'views-d4726e4fc497c408cb6ed4fbaa05e720';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'inherit_path' => 1,
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
  $pane->position = 2;
  $pane->locks = array();
  $pane->uuid = 'c0fb5b4d-a03a-444b-8be5-c57562638d37';
  $display->content['new-c0fb5b4d-a03a-444b-8be5-c57562638d37'] = $pane;
  $display->panels['contentmain'][2] = 'new-c0fb5b4d-a03a-444b-8be5-c57562638d37';
  $pane = new stdClass();
  $pane->pid = 'new-de61f230-4a5e-4907-8a1c-4ab50c6eece2';
  $pane->panel = 'contentmain';
  $pane->type = 'views_panes';
  $pane->subtype = 'dkan_harvest_source_search-dkan_harvest_source_search_pane';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array();
  $pane->cache = array();
  $pane->style = array(
    'settings' => NULL,
  );
  $pane->css = array();
  $pane->extras = array();
  $pane->position = 3;
  $pane->locks = array();
  $pane->uuid = 'de61f230-4a5e-4907-8a1c-4ab50c6eece2';
  $display->content['new-de61f230-4a5e-4907-8a1c-4ab50c6eece2'] = $pane;
  $display->panels['contentmain'][3] = 'new-de61f230-4a5e-4907-8a1c-4ab50c6eece2';
  $pane = new stdClass();
  $pane->pid = 'new-6f4e98ef-44f1-4285-82ae-e50466f01fe3';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'dkan_harvest-harvest_source_summary';
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
  $pane->uuid = '6f4e98ef-44f1-4285-82ae-e50466f01fe3';
  $display->content['new-6f4e98ef-44f1-4285-82ae-e50466f01fe3'] = $pane;
  $display->panels['sidebar'][0] = 'new-6f4e98ef-44f1-4285-82ae-e50466f01fe3';
  $pane = new stdClass();
  $pane->pid = 'new-dff3ab78-497e-4b4b-9821-3c971ad02b5a';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'facetapi-wsVI1ENUXwf4Rz08n9fg2WvfQ0Gs5h2a';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'override_title' => 1,
    'override_title_text' => 'Author',
    'override_title_heading' => 'h2',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => NULL,
  );
  $pane->css = array();
  $pane->extras = array();
  $pane->position = 1;
  $pane->locks = array();
  $pane->uuid = 'dff3ab78-497e-4b4b-9821-3c971ad02b5a';
  $display->content['new-dff3ab78-497e-4b4b-9821-3c971ad02b5a'] = $pane;
  $display->panels['sidebar'][1] = 'new-dff3ab78-497e-4b4b-9821-3c971ad02b5a';
  $pane = new stdClass();
  $pane->pid = 'new-1dd44b86-bd6f-4a06-86bf-741c89c980f2';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'facetapi-dqCRnp6cKP1cSzAsNB6zqFtY9AVEa0mp';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'override_title' => 1,
    'override_title_text' => 'Tags',
    'override_title_heading' => 'h2',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => NULL,
  );
  $pane->css = array();
  $pane->extras = array();
  $pane->position = 2;
  $pane->locks = array();
  $pane->uuid = '1dd44b86-bd6f-4a06-86bf-741c89c980f2';
  $display->content['new-1dd44b86-bd6f-4a06-86bf-741c89c980f2'] = $pane;
  $display->panels['sidebar'][2] = 'new-1dd44b86-bd6f-4a06-86bf-741c89c980f2';
  $display->hide_title = PANELS_TITLE_PANE;
  $display->title_pane = '0';
  $handler->conf['display'] = $display;
  $export['node_view__panel_context_026b4bea-efb2-4aaf-b432-11f82d63631b'] = $handler;

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
  $display->storage_type = 'page_manager';
  $display->storage_id = 'node_view__resource';
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
  $pane->pid = 'new-f8882733-00a0-4b8f-a54b-47782e311327';
  $pane->panel = 'column1';
  $pane->type = 'block';
  $pane->subtype = 'dkan_dataset-dkan_dataset_visualizations_list';
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
  $pane->position = 1;
  $pane->locks = array();
  $pane->uuid = 'f8882733-00a0-4b8f-a54b-47782e311327';
  $display->content['new-f8882733-00a0-4b8f-a54b-47782e311327'] = $pane;
  $display->panels['column1'][1] = 'new-f8882733-00a0-4b8f-a54b-47782e311327';
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
  $display->storage_type = 'page_manager';
  $display->storage_id = 'node_view_panel_context';
  $display->content = array();
  $display->panels = array();
  $pane = new stdClass();
  $pane->pid = 'new-dbba36e8-a75c-4fec-b189-d8c3cae601e9';
  $pane->panel = 'contentmain';
  $pane->type = 'entity_view';
  $pane->subtype = 'node';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'view_mode' => 'full',
    'context' => 'argument_entity_id:node_1',
    'override_title' => 1,
    'override_title_text' => '%node:title',
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
  $pane->uuid = 'dbba36e8-a75c-4fec-b189-d8c3cae601e9';
  $display->content['new-dbba36e8-a75c-4fec-b189-d8c3cae601e9'] = $pane;
  $display->panels['contentmain'][0] = 'new-dbba36e8-a75c-4fec-b189-d8c3cae601e9';
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
  $display->title_pane = 'new-dbba36e8-a75c-4fec-b189-d8c3cae601e9';
  $handler->conf['display'] = $display;
  $export['node_view_panel_context'] = $handler;

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
  $display->storage_type = 'page_manager';
  $display->storage_id = 'node_view_panel_context_3';
  $display->content = array();
  $display->panels = array();
  $pane = new stdClass();
  $pane->pid = 'new-7a0c91ca-bf36-42df-b89a-f4d3a73f44a8';
  $pane->panel = 'contentmain';
  $pane->type = 'block';
  $pane->subtype = 'views-7d6279e929769d19b46e686f927993a3';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'inherit_path' => 1,
    'override_title' => 1,
    'override_title_text' => '%node:title',
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
  $pane->uuid = '7a0c91ca-bf36-42df-b89a-f4d3a73f44a8';
  $display->content['new-7a0c91ca-bf36-42df-b89a-f4d3a73f44a8'] = $pane;
  $display->panels['contentmain'][0] = 'new-7a0c91ca-bf36-42df-b89a-f4d3a73f44a8';
  $pane = new stdClass();
  $pane->pid = 'new-cf4cd1b6-f7ec-4b26-ae5d-57762810dc17';
  $pane->panel = 'contentmain';
  $pane->type = 'views_panes';
  $pane->subtype = 'dkan_group_search-dkan_group_search';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array();
  $pane->cache = array();
  $pane->style = array(
    'settings' => NULL,
  );
  $pane->css = array();
  $pane->extras = array();
  $pane->position = 1;
  $pane->locks = array();
  $pane->uuid = 'cf4cd1b6-f7ec-4b26-ae5d-57762810dc17';
  $display->content['new-cf4cd1b6-f7ec-4b26-ae5d-57762810dc17'] = $pane;
  $display->panels['contentmain'][1] = 'new-cf4cd1b6-f7ec-4b26-ae5d-57762810dc17';
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
    'override_title' => 1,
    'override_title_text' => 'Date Changed',
    'override_title_heading' => 'h2',
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
  $pane->pid = 'new-5b7571be-ff82-48eb-8855-16ddcd379a9d';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'facetapi-xNAidW9x2foW6UGpYpK8MzTaBT8ebK4D';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'override_title' => 1,
    'override_title_text' => 'Tags',
    'override_title_heading' => 'h2',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => NULL,
  );
  $pane->css = array();
  $pane->extras = array();
  $pane->position = 2;
  $pane->locks = array();
  $pane->uuid = '5b7571be-ff82-48eb-8855-16ddcd379a9d';
  $display->content['new-5b7571be-ff82-48eb-8855-16ddcd379a9d'] = $pane;
  $display->panels['sidebar'][2] = 'new-5b7571be-ff82-48eb-8855-16ddcd379a9d';
  $pane = new stdClass();
  $pane->pid = 'new-0720de5b-6051-4f54-948b-c62d1074f82e';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'facetapi-lzvCI5xSUC94irLWjBH4WoOTqXVxs8FR';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'override_title' => 1,
    'override_title_text' => 'Format',
    'override_title_heading' => 'h2',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => NULL,
  );
  $pane->css = array();
  $pane->extras = array();
  $pane->position = 3;
  $pane->locks = array();
  $pane->uuid = '0720de5b-6051-4f54-948b-c62d1074f82e';
  $display->content['new-0720de5b-6051-4f54-948b-c62d1074f82e'] = $pane;
  $display->panels['sidebar'][3] = 'new-0720de5b-6051-4f54-948b-c62d1074f82e';
  $pane = new stdClass();
  $pane->pid = 'new-58708ed1-8465-4976-887b-a764f6bc6e4f';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'facetapi-n9S7gglvd3lMrVMu760QrIyeZAKByKsX';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'override_title' => 1,
    'override_title_text' => 'Author',
    'override_title_heading' => 'h2',
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
  $display->title_pane = 'new-7a0c91ca-bf36-42df-b89a-f4d3a73f44a8';
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
  $display->storage_type = 'page_manager';
  $display->storage_id = 'user_view_panel_context';
  $display->content = array();
  $display->panels = array();
  $pane = new stdClass();
  $pane->pid = 'new-3b15fd88-4e94-4c1e-9cd0-603f26518943';
  $pane->panel = 'contentmain';
  $pane->type = 'block';
  $pane->subtype = 'views-57b778c513d5cc2716ad72eb5897fabe';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'inherit_path' => 1,
    'override_title' => 1,
    'override_title_text' => '%user:name\'s content',
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
  $pane->uuid = '3b15fd88-4e94-4c1e-9cd0-603f26518943';
  $display->content['new-3b15fd88-4e94-4c1e-9cd0-603f26518943'] = $pane;
  $display->panels['contentmain'][0] = 'new-3b15fd88-4e94-4c1e-9cd0-603f26518943';
  $pane = new stdClass();
  $pane->pid = 'new-b7c6a77b-f4e8-4477-83c9-8d97a0ee402d';
  $pane->panel = 'contentmain';
  $pane->type = 'views';
  $pane->subtype = 'user_profile_search';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'override_pager_settings' => 0,
    'use_pager' => 1,
    'nodes_per_page' => '10',
    'pager_id' => '0',
    'offset' => '0',
    'more_link' => 0,
    'feed_icons' => 0,
    'panel_args' => 0,
    'link_to_view' => 0,
    'args' => '',
    'url' => '',
    'display' => 'user_profile_search_block',
    'context' => array(
      0 => '',
    ),
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
  $pane->position = 1;
  $pane->locks = array();
  $pane->uuid = 'b7c6a77b-f4e8-4477-83c9-8d97a0ee402d';
  $display->content['new-b7c6a77b-f4e8-4477-83c9-8d97a0ee402d'] = $pane;
  $display->panels['contentmain'][1] = 'new-b7c6a77b-f4e8-4477-83c9-8d97a0ee402d';
  $pane = new stdClass();
  $pane->pid = 'new-b3b15443-086c-4225-8e97-48e60df6d7b4';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'dkan_sitewide_panels-dkan_user_summary';
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
  $pane->uuid = 'b3b15443-086c-4225-8e97-48e60df6d7b4';
  $display->content['new-b3b15443-086c-4225-8e97-48e60df6d7b4'] = $pane;
  $display->panels['sidebar'][0] = 'new-b3b15443-086c-4225-8e97-48e60df6d7b4';
  $pane = new stdClass();
  $pane->pid = 'new-b5ed2517-658a-473d-932e-dd69b664a49e';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'facetapi-y0bmt1wN50B9oCuMeoazc1hpKiMkSQ2A';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'override_title' => 1,
    'override_title_text' => 'License',
    'override_title_heading' => 'h2',
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
    'override_title' => 1,
    'override_title_text' => 'Publisher',
    'override_title_heading' => 'h2',
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
    'override_title' => 1,
    'override_title_text' => 'Format',
    'override_title_heading' => 'h2',
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
    'override_title' => 1,
    'override_title_text' => 'Tags',
    'override_title_heading' => 'h2',
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
  $display->title_pane = '0';
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
  $page->path = 'search';
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
  $handler->name = 'page_datasets_panel_context';
  $handler->task = 'page';
  $handler->subtask = 'datasets';
  $handler->handler = 'panel_context';
  $handler->weight = 0;
  $handler->conf = array(
    'title' => 'With Topics',
    'no_blocks' => 0,
    'pipeline' => 'standard',
    'body_classes_to_remove' => '',
    'body_classes_to_add' => '',
    'css_id' => '',
    'css' => '',
    'contexts' => array(),
    'relationships' => array(),
    'access' => array(
      'logic' => 'and',
      'plugins' => array(
        0 => array(
          'name' => 'php',
          'settings' => array(
            'description' => 'dkan_topics is enabled',
            'php' => 'if(module_exists(\'dkan_topics\')) {
return TRUE;
}',
          ),
          'not' => FALSE,
        ),
      ),
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
  $display->title = 'Search';
  $display->uuid = 'b1ebec5e-aed7-4ebc-82de-7e9eac72c35a';
  $display->storage_type = 'page_manager';
  $display->storage_id = 'page_datasets_panel_context';
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
  $pane->pid = 'new-1946db71-2c78-4c13-8d68-fa4eb5d7b8fd';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'facetapi-guaQewLU3YsZiLS2mGWJqmw8ycXAVT0R';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'override_title' => 1,
    'override_title_text' => 'Content Types',
    'override_title_heading' => 'h2',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => array(
      'pane_title' => '%title',
      'pane_collapsed' => 0,
      'pane_empty_check' => 0,
    ),
    'style' => 'collapsible',
  );
  $pane->css = array(
    'css_id' => '',
    'css_class' => 'pane-facetapi pane-block',
  );
  $pane->extras = array();
  $pane->position = 0;
  $pane->locks = array();
  $pane->uuid = '1946db71-2c78-4c13-8d68-fa4eb5d7b8fd';
  $display->content['new-1946db71-2c78-4c13-8d68-fa4eb5d7b8fd'] = $pane;
  $display->panels['sidebar'][0] = 'new-1946db71-2c78-4c13-8d68-fa4eb5d7b8fd';
  $pane = new stdClass();
  $pane->pid = 'new-51b9a740-77dd-44f7-8aa8-57d1728afbe7';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'facetapi-EwrOFrHh7WdVteFiBTQYXfGlscW50MbI';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'override_title' => 1,
    'override_title_text' => 'Topics',
    'override_title_heading' => 'h2',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => array(
      'pane_title' => '%title',
      'pane_collapsed' => 0,
      'pane_empty_check' => 0,
    ),
    'style' => 'collapsible',
  );
  $pane->css = array(
    'css_id' => '',
    'css_class' => 'pane-facetapi pane-block',
  );
  $pane->extras = array();
  $pane->position = 1;
  $pane->locks = array();
  $pane->uuid = '51b9a740-77dd-44f7-8aa8-57d1728afbe7';
  $display->content['new-51b9a740-77dd-44f7-8aa8-57d1728afbe7'] = $pane;
  $display->panels['sidebar'][1] = 'new-51b9a740-77dd-44f7-8aa8-57d1728afbe7';
  $pane = new stdClass();
  $pane->pid = 'new-af542600-62f2-4baf-b2c3-12b9ee1d59ba';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'facetapi-dqCRnp6cKP1cSzAsNB6zqFtY9AVEa0mp';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'override_title' => 1,
    'override_title_text' => 'Tags',
    'override_title_heading' => 'h2',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => array(
      'pane_title' => '%title',
      'pane_collapsed' => 1,
      'pane_empty_check' => 0,
    ),
    'style' => 'collapsible',
  );
  $pane->css = array(
    'css_id' => '',
    'css_class' => 'pane-facetapi pane-block',
  );
  $pane->extras = array();
  $pane->position = 2;
  $pane->locks = array();
  $pane->uuid = 'af542600-62f2-4baf-b2c3-12b9ee1d59ba';
  $display->content['new-af542600-62f2-4baf-b2c3-12b9ee1d59ba'] = $pane;
  $display->panels['sidebar'][2] = 'new-af542600-62f2-4baf-b2c3-12b9ee1d59ba';
  $pane = new stdClass();
  $pane->pid = 'new-549159dd-e073-4e10-9307-b13fad742409';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'facetapi-yW6mJ3GaJF0LypnLPa433Bf9GI8QdWd9';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'override_title' => 1,
    'override_title_text' => 'Format',
    'override_title_heading' => 'h2',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => array(
      'pane_title' => '%title',
      'pane_collapsed' => 1,
      'pane_empty_check' => 0,
    ),
    'style' => 'collapsible',
  );
  $pane->css = array(
    'css_id' => '',
    'css_class' => 'pane-facetapi pane-block',
  );
  $pane->extras = array();
  $pane->position = 3;
  $pane->locks = array();
  $pane->uuid = '549159dd-e073-4e10-9307-b13fad742409';
  $display->content['new-549159dd-e073-4e10-9307-b13fad742409'] = $pane;
  $display->panels['sidebar'][3] = 'new-549159dd-e073-4e10-9307-b13fad742409';
  $pane = new stdClass();
  $pane->pid = 'new-b0fb8ba0-c78f-4b2e-9ae3-e4cc61f06345';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'facetapi-VBBDnNWYupC3PxCnXOq4e4ds4sAU4W7s';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'override_title' => 1,
    'override_title_text' => 'Publisher',
    'override_title_heading' => 'h2',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => array(
      'pane_title' => '%title',
      'pane_collapsed' => 1,
      'pane_empty_check' => 0,
    ),
    'style' => 'collapsible',
  );
  $pane->css = array(
    'css_id' => '',
    'css_class' => 'pane-facetapi pane-block',
  );
  $pane->extras = array();
  $pane->position = 4;
  $pane->locks = array();
  $pane->uuid = 'b0fb8ba0-c78f-4b2e-9ae3-e4cc61f06345';
  $display->content['new-b0fb8ba0-c78f-4b2e-9ae3-e4cc61f06345'] = $pane;
  $display->panels['sidebar'][4] = 'new-b0fb8ba0-c78f-4b2e-9ae3-e4cc61f06345';
  $pane = new stdClass();
  $pane->pid = 'new-0541c9a5-2e8c-488f-8db4-4cd064bdc903';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'facetapi-y0bmt1wN50B9oCuMeoazc1hpKiMkSQ2A';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'override_title' => 1,
    'override_title_text' => 'License',
    'override_title_heading' => 'h2',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => array(
      'pane_title' => '%title',
      'pane_collapsed' => 1,
      'pane_empty_check' => 0,
    ),
    'style' => 'collapsible',
  );
  $pane->css = array(
    'css_id' => '',
    'css_class' => 'pane-facetapi pane-block',
  );
  $pane->extras = array();
  $pane->position = 5;
  $pane->locks = array();
  $pane->uuid = '0541c9a5-2e8c-488f-8db4-4cd064bdc903';
  $display->content['new-0541c9a5-2e8c-488f-8db4-4cd064bdc903'] = $pane;
  $display->panels['sidebar'][5] = 'new-0541c9a5-2e8c-488f-8db4-4cd064bdc903';
  $pane = new stdClass();
  $pane->pid = 'new-e9e837de-fde5-421e-b698-d9d1d75f9375';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'facetapi-nSfOGeWhiLagPUYEma8AowuwlSHisprc';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'override_title' => 1,
    'override_title_text' => 'Filter by Collection',
    'override_title_heading' => 'h2',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => NULL,
  );
  $pane->css = array();
  $pane->extras = array();
  $pane->position = 6;
  $pane->locks = array();
  $pane->uuid = 'e9e837de-fde5-421e-b698-d9d1d75f9375';
  $display->content['new-e9e837de-fde5-421e-b698-d9d1d75f9375'] = $pane;
  $display->panels['sidebar'][6] = 'new-e9e837de-fde5-421e-b698-d9d1d75f9375';
  $display->hide_title = PANELS_TITLE_FIXED;
  $display->title_pane = '0';
  $handler->conf['display'] = $display;
  $page->default_handlers[$handler->name] = $handler;
  $handler = new stdClass();
  $handler->disabled = FALSE; /* Edit this to true to make a default handler disabled initially */
  $handler->api_version = 1;
  $handler->name = 'page_datasets__without-topics';
  $handler->task = 'page';
  $handler->subtask = 'datasets';
  $handler->handler = 'panel_context';
  $handler->weight = 1;
  $handler->conf = array(
    'title' => 'Without Topics',
    'no_blocks' => 0,
    'pipeline' => 'standard',
    'body_classes_to_remove' => '',
    'body_classes_to_add' => '',
    'css_id' => '',
    'css' => '',
    'contexts' => array(),
    'relationships' => array(),
    'access' => array(
      'logic' => 'and',
      'plugins' => array(
        0 => array(
          'name' => 'php',
          'settings' => array(
            'description' => 'dkan_topics is disabled',
            'php' => 'if(!module_exists(\'dkan_topics\')) {
return TRUE;
}',
          ),
          'not' => FALSE,
        ),
      ),
    ),
    'name' => 'without-topics',
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
  $display->storage_type = 'page_manager';
  $display->storage_id = 'page_datasets__without-topics';
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
  $pane->pid = 'new-1946db71-2c78-4c13-8d68-fa4eb5d7b8fd';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'facetapi-guaQewLU3YsZiLS2mGWJqmw8ycXAVT0R';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'override_title' => 1,
    'override_title_text' => 'Content Types',
    'override_title_heading' => 'h2',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => array(
      'pane_title' => '%title',
      'pane_collapsed' => 0,
      'pane_empty_check' => 0,
    ),
    'style' => 'collapsible',
  );
  $pane->css = array(
    'css_id' => '',
    'css_class' => 'pane-facetapi pane-block',
  );
  $pane->extras = array();
  $pane->position = 0;
  $pane->locks = array();
  $pane->uuid = '1946db71-2c78-4c13-8d68-fa4eb5d7b8fd';
  $display->content['new-1946db71-2c78-4c13-8d68-fa4eb5d7b8fd'] = $pane;
  $display->panels['sidebar'][0] = 'new-1946db71-2c78-4c13-8d68-fa4eb5d7b8fd';
  $pane = new stdClass();
  $pane->pid = 'new-af542600-62f2-4baf-b2c3-12b9ee1d59ba';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'facetapi-dqCRnp6cKP1cSzAsNB6zqFtY9AVEa0mp';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'override_title' => 1,
    'override_title_text' => 'Tags',
    'override_title_heading' => 'h2',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => array(
      'pane_title' => '%title',
      'pane_collapsed' => 1,
      'pane_empty_check' => 0,
    ),
    'style' => 'collapsible',
  );
  $pane->css = array(
    'css_id' => '',
    'css_class' => 'pane-facetapi pane-block',
  );
  $pane->extras = array();
  $pane->position = 1;
  $pane->locks = array();
  $pane->uuid = 'af542600-62f2-4baf-b2c3-12b9ee1d59ba';
  $display->content['new-af542600-62f2-4baf-b2c3-12b9ee1d59ba'] = $pane;
  $display->panels['sidebar'][1] = 'new-af542600-62f2-4baf-b2c3-12b9ee1d59ba';
  $pane = new stdClass();
  $pane->pid = 'new-549159dd-e073-4e10-9307-b13fad742409';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'facetapi-yW6mJ3GaJF0LypnLPa433Bf9GI8QdWd9';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'override_title' => 1,
    'override_title_text' => 'Format',
    'override_title_heading' => 'h2',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => array(
      'pane_title' => '%title',
      'pane_collapsed' => 1,
      'pane_empty_check' => 0,
    ),
    'style' => 'collapsible',
  );
  $pane->css = array(
    'css_id' => '',
    'css_class' => 'pane-facetapi pane-block',
  );
  $pane->extras = array();
  $pane->position = 2;
  $pane->locks = array();
  $pane->uuid = '549159dd-e073-4e10-9307-b13fad742409';
  $display->content['new-549159dd-e073-4e10-9307-b13fad742409'] = $pane;
  $display->panels['sidebar'][2] = 'new-549159dd-e073-4e10-9307-b13fad742409';
  $pane = new stdClass();
  $pane->pid = 'new-b0fb8ba0-c78f-4b2e-9ae3-e4cc61f06345';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'facetapi-VBBDnNWYupC3PxCnXOq4e4ds4sAU4W7s';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'override_title' => 1,
    'override_title_text' => 'Publisher',
    'override_title_heading' => 'h2',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => array(
      'pane_title' => '%title',
      'pane_collapsed' => 1,
      'pane_empty_check' => 0,
    ),
    'style' => 'collapsible',
  );
  $pane->css = array(
    'css_id' => '',
    'css_class' => 'pane-facetapi pane-block',
  );
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
    'override_title' => 1,
    'override_title_text' => 'Author',
    'override_title_heading' => 'h2',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => array(
      'pane_title' => '%title',
      'pane_collapsed' => 1,
      'pane_empty_check' => 0,
    ),
    'style' => 'collapsible',
  );
  $pane->css = array(
    'css_id' => '',
    'css_class' => 'pane-facetapi pane-block',
  );
  $pane->extras = array();
  $pane->position = 4;
  $pane->locks = array();
  $pane->uuid = '51f2a5b5-ab6a-474a-b0f8-4a0b297160a8';
  $display->content['new-51f2a5b5-ab6a-474a-b0f8-4a0b297160a8'] = $pane;
  $display->panels['sidebar'][4] = 'new-51f2a5b5-ab6a-474a-b0f8-4a0b297160a8';
  $pane = new stdClass();
  $pane->pid = 'new-0541c9a5-2e8c-488f-8db4-4cd064bdc903';
  $pane->panel = 'sidebar';
  $pane->type = 'block';
  $pane->subtype = 'facetapi-y0bmt1wN50B9oCuMeoazc1hpKiMkSQ2A';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'override_title' => 1,
    'override_title_text' => 'License',
    'override_title_heading' => 'h2',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => array(
      'pane_title' => '%title',
      'pane_collapsed' => 1,
      'pane_empty_check' => 0,
    ),
    'style' => 'collapsible',
  );
  $pane->css = array(
    'css_id' => '',
    'css_class' => 'pane-facetapi pane-block',
  );
  $pane->extras = array();
  $pane->position = 5;
  $pane->locks = array();
  $pane->uuid = '0541c9a5-2e8c-488f-8db4-4cd064bdc903';
  $display->content['new-0541c9a5-2e8c-488f-8db4-4cd064bdc903'] = $pane;
  $display->panels['sidebar'][5] = 'new-0541c9a5-2e8c-488f-8db4-4cd064bdc903';
  $display->hide_title = PANELS_TITLE_FIXED;
  $display->title_pane = '0';
  $handler->conf['display'] = $display;
  $page->default_handlers[$handler->name] = $handler;
  $pages['datasets'] = $page;

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
  $page->menu = array();
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
  $display->storage_type = 'page_manager';
  $display->storage_id = 'page_groups_panel_context';
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
