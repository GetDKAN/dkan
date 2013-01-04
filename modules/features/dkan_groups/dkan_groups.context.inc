<?php
/**
 * @file
 * dkan_groups.context.inc
 */

/**
 * Implements hook_context_default_contexts().
 */
function dkan_groups_context_default_contexts() {
  $export = array();

  $context = new stdClass();
  $context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
  $context->api_version = 3;
  $context->name = 'group_dataset';
  $context->description = '';
  $context->tag = 'Group';
  $context->conditions = array(
    'node' => array(
      'values' => array(
        'dataset' => 'dataset',
      ),
      'options' => array(
        'node_form' => '0',
      ),
    ),
  );
  $context->reactions = array(
    'block' => array(
      'blocks' => array(
        'views-group_block-block' => array(
          'module' => 'views',
          'delta' => 'group_block-block',
          'region' => 'sidebar_first',
          'weight' => '-10',
        ),
      ),
    ),
  );
  $context->condition_mode = 0;

  // Translatables
  // Included for use with string extractors like potx.
  t('Group');
  $export['group_dataset'] = $context;

  return $export;
}
