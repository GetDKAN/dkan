<?php
/**
 * @file
 * dkan_sitewide_profile_page.pages_default.inc
 */

/**
 * Implements hook_default_page_manager_handlers().
 */
function dkan_sitewide_profile_page_default_page_manager_handlers() {
  $export = array();

  $handler = new stdClass();
  $handler->disabled = FALSE; /* Edit this to true to make a default handler disabled initially */
  $handler->api_version = 1;
  $handler->name = 'user_view__panel_context_cd3bc8b2-8c44-4dd1-926c-460b2459bdca';
  $handler->task = 'user_view';
  $handler->subtask = '';
  $handler->handler = 'panel_context';
  $handler->weight = -30;
  $handler->conf = array(
    'title' => 'NuCivic Data Dashboard',
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
          'name' => 'compare_users',
          'settings' => array(
            'equality' => '1',
          ),
          'context' => array(
            0 => 'argument_entity_id:user_1',
            1 => 'logged-in-user',
          ),
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
  $display->uuid = 'fb08d023-1c59-4d27-aebf-e2e55e7ef37c';
  $display->content = array();
  $display->panels = array();
    $pane = new stdClass();
    $pane->pid = 'new-bf8b8dff-2b1a-4595-84a4-ba8855c45bad';
    $pane->panel = 'contentmain';
    $pane->type = 'views';
    $pane->subtype = 'admin_content';
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
      'display' => 'default',
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
    $pane->uuid = 'bf8b8dff-2b1a-4595-84a4-ba8855c45bad';
    $display->content['new-bf8b8dff-2b1a-4595-84a4-ba8855c45bad'] = $pane;
    $display->panels['contentmain'][0] = 'new-bf8b8dff-2b1a-4595-84a4-ba8855c45bad';
    $pane = new stdClass();
    $pane->pid = 'new-a70d19c5-fd19-4979-8ccc-04c7ff2bc4a5';
    $pane->panel = 'sidebar';
    $pane->type = 'block';
    $pane->subtype = 'dkan_sitewide_profile_page-dkan_user_summary';
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
    $pane->uuid = 'a70d19c5-fd19-4979-8ccc-04c7ff2bc4a5';
    $display->content['new-a70d19c5-fd19-4979-8ccc-04c7ff2bc4a5'] = $pane;
    $display->panels['sidebar'][0] = 'new-a70d19c5-fd19-4979-8ccc-04c7ff2bc4a5';
  $display->hide_title = PANELS_TITLE_FIXED;
  $display->title_pane = '0';
  $handler->conf['display'] = $display;
  $export['user_view__panel_context_cd3bc8b2-8c44-4dd1-926c-460b2459bdca'] = $handler;

  return $export;
}
