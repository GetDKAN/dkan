<?php

/**
 * @file
 * dkan_sitewide_demo_front.context.inc
 */

/**
 * Implements hook_context_default_contexts().
 */
function dkan_sitewide_demo_front_context_default_contexts() {
  $export = array();

  $context = new stdClass();
  $context->disabled = TRUE;
  $context->api_version = 3;
  $context->name = 'front';
  $context->description = '';
  $context->tag = 'Page';
  $context->conditions = array(
    'path' => array(
      'values' => array(
        '<front>' => '<front>',
      ),
    ),
  );
  $context->reactions = array(
    'block' => array(
      'blocks' => array(
        'dkan_sitewide_demo_front-dkan_demo_front' => array(
          'module' => 'dkan_sitewide_demo_front',
          'delta' => 'dkan_demo_front',
          'region' => 'preface_first',
          'weight' => '-10',
        ),
        'dkan_sitewide_demo_front-dkan_add_front' => array(
          'module' => 'dkan_sitewide_demo_front',
          'delta' => 'dkan_add_front',
          'region' => 'preface_second',
          'weight' => '-10',
        ),
        'views-popular_tags-block' => array(
          'module' => 'views',
          'delta' => 'popular_tags-block',
          'region' => 'preface_second',
          'weight' => '-10',
        ),
        'dkan_sitewide_demo_front-dkan_search_front' => array(
          'module' => 'dkan_sitewide_demo_front',
          'delta' => 'dkan_search_front',
          'region' => 'preface_second',
          'weight' => '-9',
        ),
        'views-front_page_group_grid-block' => array(
          'module' => 'views',
          'delta' => 'front_page_group_grid-block',
          'region' => 'content',
          'weight' => '-10',
        ),
      ),
    ),
  );
  $context->condition_mode = 0;

  // Translatables
  // Included for use with string extractors like potx.
  t('Page');
  $export['front'] = $context;

  return $export;
}
