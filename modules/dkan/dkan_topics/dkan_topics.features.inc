<?php

/**
 * @file
 * dkan_topics.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function dkan_topics_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "entity_path" && $api == "entity_path") {
    return array("version" => "1");
  }
  if ($module == "facetapi" && $api == "facetapi_defaults") {
    return array("version" => "1");
  }
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function dkan_topics_views_api($module = NULL, $api = NULL) {
  return array("api" => "3.0");
}

/**
 * Implements hook_image_default_styles().
 */
function dkan_topics_image_default_styles() {
  $styles = array();

  // Exported image style: small_topics_icons.
  $styles['small_topics_icons'] = array(
    'label' => 'Small topics icons',
    'effects' => array(
      2 => array(
        'name' => 'image_scale_and_crop',
        'data' => array(
          'width' => 25,
          'height' => 25,
        ),
        'weight' => 2,
      ),
    ),
  );

  // Exported image style: topic_medium.
  $styles['topic_medium'] = array(
    'label' => 'Topic Medium',
    'effects' => array(
      4 => array(
        'name' => 'image_scale',
        'data' => array(
          'width' => 200,
          'height' => '',
          'upscale' => 0,
        ),
        'weight' => 1,
      ),
      5 => array(
        'name' => 'canvasactions_definecanvas',
        'data' => array(
          'RGB' => array(
            'HEX' => '#ffffff',
          ),
          'under' => 1,
          'exact' => array(
            'width' => 200,
            'height' => 200,
            'xpos' => 'center',
            'ypos' => 'center',
          ),
          'relative' => array(
            'leftdiff' => '',
            'rightdiff' => '',
            'topdiff' => '',
            'bottomdiff' => '',
          ),
        ),
        'weight' => 2,
      ),
      6 => array(
        'name' => 'image_crop',
        'data' => array(
          'width' => 200,
          'height' => 200,
          'anchor' => 'center-center',
        ),
        'weight' => 3,
      ),
    ),
  );

  return $styles;
}
