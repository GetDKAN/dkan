<?php

/**
 * @file
 * dkan_data_story.panelizer.inc
 */

/**
 * Implements hook_panelizer_defaults().
 */
function dkan_data_story_panelizer_defaults() {
  $export = array();

  $panelizer = new stdClass();
  $panelizer->disabled = FALSE; /* Edit this to true to make a default panelizer disabled initially */
  $panelizer->api_version = 1;
  $panelizer->title = 'Default';
  $panelizer->panelizer_type = 'node';
  $panelizer->panelizer_key = 'dkan_data_story';
  $panelizer->access = array();
  $panelizer->view_mode = 'default';
  $panelizer->name = 'node:dkan_data_story:default:default';
  $panelizer->css_id = '';
  $panelizer->css_class = '';
  $panelizer->css = '';
  $panelizer->no_blocks = FALSE;
  $panelizer->title_element = 'H2';
  $panelizer->link_to_entity = FALSE;
  $panelizer->extra = array();
  $panelizer->pipeline = 'ipe';
  $panelizer->contexts = array();
  $panelizer->relationships = array();
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
  $display->title = '%node:title';
  $display->uuid = '2b15dd64-2e38-4fef-b312-eba3d938312f';
  $display->storage_type = 'panelizer_default';
  $display->storage_id = 'node:dkan_data_story:default:default';
  $display->content = array();
  $display->panels = array();
  $pane = new stdClass();
  $pane->pid = 'new-93906781-399f-4b90-bf5d-b1d1228a83d8';
  $pane->panel = 'contentmain';
  $pane->type = 'entity_field';
  $pane->subtype = 'node:field_topic';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'label' => 'hidden',
    'formatter' => 'dkan_topic_formatter',
    'delta_limit' => '0',
    'delta_offset' => '0',
    'delta_reversed' => 0,
    'formatter_settings' => array(),
    'context' => 'panelizer',
    'override_title' => 0,
    'override_title_text' => '',
    'override_title_heading' => 'h2',
  );
  $pane->cache = array();
  $pane->style = array();
  $pane->css = array();
  $pane->extras = array();
  $pane->position = 0;
  $pane->locks = array();
  $pane->uuid = '93906781-399f-4b90-bf5d-b1d1228a83d8';
  $display->content['new-93906781-399f-4b90-bf5d-b1d1228a83d8'] = $pane;
  $display->panels['contentmain'][0] = 'new-93906781-399f-4b90-bf5d-b1d1228a83d8';
  $pane = new stdClass();
  $pane->pid = 'new-309fe989-26f8-4c4f-8c6b-54a55738fd5f';
  $pane->panel = 'contentmain';
  $pane->type = 'entity_field';
  $pane->subtype = 'node:field_image';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'label' => 'title',
    'formatter' => 'image',
    'delta_limit' => 0,
    'delta_offset' => '0',
    'delta_reversed' => FALSE,
    'formatter_settings' => array(
      'image_style' => 'story_image_full',
      'image_link' => '',
      'file_view_mode' => 'full',
    ),
    'context' => 'panelizer',
    'override_title' => 1,
    'override_title_text' => '',
    'override_title_heading' => 'h2',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => NULL,
  );
  $pane->css = array(
    'css_id' => '',
    'css_class' => 'story_image_full_width',
  );
  $pane->extras = array();
  $pane->position = 1;
  $pane->locks = array();
  $pane->uuid = '309fe989-26f8-4c4f-8c6b-54a55738fd5f';
  $display->content['new-309fe989-26f8-4c4f-8c6b-54a55738fd5f'] = $pane;
  $display->panels['contentmain'][1] = 'new-309fe989-26f8-4c4f-8c6b-54a55738fd5f';
  $pane = new stdClass();
  $pane->pid = 'new-00684646-ba76-48ef-9f1c-d8ffeb33983a';
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
  $pane->position = 2;
  $pane->locks = array();
  $pane->uuid = '00684646-ba76-48ef-9f1c-d8ffeb33983a';
  $display->content['new-00684646-ba76-48ef-9f1c-d8ffeb33983a'] = $pane;
  $display->panels['contentmain'][2] = 'new-00684646-ba76-48ef-9f1c-d8ffeb33983a';
  $pane = new stdClass();
  $pane->pid = 'new-9badcb19-4dc5-49a8-bac6-5ec537133e2f';
  $pane->panel = 'contentmain';
  $pane->type = 'entity_field';
  $pane->subtype = 'node:field_tags';
  $pane->shown = TRUE;
  $pane->access = array();
  $pane->configuration = array(
    'label' => 'hidden',
    'formatter' => 'taxonomy_term_reference_link',
    'delta_limit' => 0,
    'delta_offset' => '0',
    'delta_reversed' => FALSE,
    'formatter_settings' => array(),
    'context' => 'panelizer',
  );
  $pane->cache = array();
  $pane->style = array(
    'settings' => NULL,
  );
  $pane->css = array();
  $pane->extras = array();
  $pane->position = 3;
  $pane->locks = array();
  $pane->uuid = '9badcb19-4dc5-49a8-bac6-5ec537133e2f';
  $display->content['new-9badcb19-4dc5-49a8-bac6-5ec537133e2f'] = $pane;
  $display->panels['contentmain'][3] = 'new-9badcb19-4dc5-49a8-bac6-5ec537133e2f';
  $display->hide_title = PANELS_TITLE_NONE;
  $display->title_pane = '0';
  $panelizer->display = $display;
  $export['node:dkan_data_story:default:default'] = $panelizer;

  return $export;
}
