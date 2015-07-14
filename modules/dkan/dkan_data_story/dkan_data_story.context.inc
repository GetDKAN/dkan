<?php
/**
 * @file
 * dkan_data_story.context.inc
 */

/**
 * Implements hook_context_default_contexts().
 */
function dkan_data_story_context_default_contexts() {
  $export = array();

  $context = new stdClass();
  $context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
  $context->api_version = 3;
  $context->name = 'stories';
  $context->description = '';
  $context->tag = 'Page';
  $context->conditions = array(
    'views' => array(
      'values' => array(
        'stories:page' => 'stories:page',
      ),
    ),
  );
  $context->reactions = array(
    'block' => array(
      'blocks' => array(
        'facetapi-GUajoKW4uKumJJsKiIOCQDoe1eyuc4YR' => array(
          'module' => 'facetapi',
          'delta' => 'GUajoKW4uKumJJsKiIOCQDoe1eyuc4YR',
          'region' => 'sidebar_first',
          'weight' => '-10',
        ),
      ),
    ),
  );
  $context->condition_mode = 0;

  // Translatables
  // Included for use with string extractors like potx.
  t('Page');
  $export['stories'] = $context;

  $context = new stdClass();
  $context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
  $context->api_version = 3;
  $context->name = 'story';
  $context->description = '';
  $context->tag = 'Node';
  $context->conditions = array(
    'node' => array(
      'values' => array(
        'dkan_data_story' => 'dkan_data_story',
      ),
      'options' => array(
        'node_form' => '1',
      ),
    ),
  );
  $context->reactions = array(
    'breadcrumb' => 'stories',
  );
  $context->condition_mode = 0;

  // Translatables
  // Included for use with string extractors like potx.
  t('Node');
  $export['story'] = $context;

  return $export;
}
