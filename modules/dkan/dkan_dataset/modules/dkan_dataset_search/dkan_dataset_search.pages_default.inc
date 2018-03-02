<?php

/**
 * @file
 * dkan_dataset_search.pages_default.inc
 */

/**
 * Implements hook_default_page_manager_pages().
 */
function dkan_dataset_search_default_page_manager_pages() {
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
  $handler->name = 'page_datasets__panel_context_46cb320a-c327-48ac-9ff6-1a2db35f83d9';
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
    'name' => '',
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
  $display->storage_id = 'page_datasets__panel_context_46cb320a-c327-48ac-9ff6-1a2db35f83d9';
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
  $display->hide_title = PANELS_TITLE_FIXED;
  $display->title_pane = '0';
  $handler->conf['display'] = $display;
  $page->default_handlers[$handler->name] = $handler;
  $handler = new stdClass();
  $handler->disabled = FALSE; /* Edit this to true to make a default handler disabled initially */
  $handler->api_version = 1;
  $handler->name = 'page_datasets__panel_context_33e9e974-942a-44fc-89cc-dd699f50dbe6';
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
    'name' => '',
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
  $display->storage_id = 'page_datasets__panel_context_33e9e974-942a-44fc-89cc-dd699f50dbe6';
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

  return $pages;

}
