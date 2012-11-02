<?php
/**
 * @file
 * dkan_front.context.inc
 */

/**
 * Implements hook_context_default_contexts().
 */
function dkan_front_context_default_contexts() {
  $export = array();

  $context = new stdClass();
  $context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
  $context->api_version = 3;
  $context->name = 'front';
  $context->description = '';
  $context->tag = '';
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
        'views-front_page_group_list-block' => array(
          'module' => 'views',
          'delta' => 'front_page_group_list-block',
          'region' => 'postscript_first',
          'weight' => '-10',
        ),
        'views-front_page_group_list-block_2' => array(
          'module' => 'views',
          'delta' => 'front_page_group_list-block_2',
          'region' => 'postscript_second',
          'weight' => '-10',
        ),
      ),
    ),
  );
  $context->condition_mode = 0;
  $export['front'] = $context;

  return $export;
}
