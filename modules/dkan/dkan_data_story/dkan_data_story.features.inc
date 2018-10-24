<?php

/**
 * @file
 * dkan_data_story.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function dkan_data_story_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "facetapi" && $api == "facetapi_defaults") {
    return array("version" => "1");
  }
  if ($module == "panelizer" && $api == "panelizer") {
    return array("version" => "1");
  }
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function dkan_data_story_views_api($module = NULL, $api = NULL) {
  return array("api" => "3.0");
}

/**
 * Implements hook_image_default_styles().
 */
function dkan_data_story_image_default_styles() {
  $styles = array();

  // Exported image style: story_image_full.
  $styles['story_image_full'] = array(
    'label' => 'Story Image - Full Width (Could upscale small images)',
    'effects' => array(
      2 => array(
        'name' => 'image_scale_and_crop',
        'data' => array(
          'width' => 1140,
          'height' => 500,
        ),
        'weight' => 1,
      ),
    ),
  );

  // Exported image style: story_image_teaser.
  $styles['story_image_teaser'] = array(
    'label' => 'Story Image - Teaser',
    'effects' => array(
      1 => array(
        'name' => 'image_scale_and_crop',
        'data' => array(
          'width' => 640,
          'height' => 300,
        ),
        'weight' => 1,
      ),
    ),
  );

  return $styles;
}

/**
 * Implements hook_node_info().
 */
function dkan_data_story_node_info() {
  $items = array(
    'dkan_data_story' => array(
      'name' => t('Data Story'),
      'base' => 'node_content',
      'description' => t('Create compelling stories using data to illustrate key points.'),
      'has_title' => '1',
      'title_label' => t('Title'),
      'help' => '',
    ),
  );
  drupal_alter('node_info', $items);
  return $items;
}
