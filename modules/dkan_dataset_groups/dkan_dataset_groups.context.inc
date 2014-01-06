<?php
/**
 * @file
 * dkan_dataset_groups.context.inc
 */

/**
 * Implements hook_context_default_contexts().
 */
function dkan_dataset_groups_context_default_contexts() {
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

  $context = new stdClass();
  $context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
  $context->api_version = 3;
  $context->name = 'group_group';
  $context->description = 'Group content type';
  $context->tag = 'Group';
  $context->conditions = array(
    'node' => array(
      'values' => array(
        'group' => 'group',
      ),
      'options' => array(
        'node_form' => '1',
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
          'weight' => '-17',
        ),
        'og_extras-group_info' => array(
          'module' => 'og_extras',
          'delta' => 'group_info',
          'region' => 'sidebar_first',
          'weight' => '-16',
        ),
        'facetapi-lzvCI5xSUC94irLWjBH4WoOTqXVxs8FR' => array(
          'module' => 'facetapi',
          'delta' => 'lzvCI5xSUC94irLWjBH4WoOTqXVxs8FR',
          'region' => 'sidebar_first',
          'weight' => '-10',
        ),
        'facetapi-xNAidW9x2foW6UGpYpK8MzTaBT8ebK4D' => array(
          'module' => 'facetapi',
          'delta' => 'xNAidW9x2foW6UGpYpK8MzTaBT8ebK4D',
          'region' => 'sidebar_first',
          'weight' => '-8',
        ),
        'facetapi-n9S7gglvd3lMrVMu760QrIyeZAKByKsX' => array(
          'module' => 'facetapi',
          'delta' => 'n9S7gglvd3lMrVMu760QrIyeZAKByKsX',
          'region' => 'sidebar_first',
          'weight' => '-7',
        ),
      ),
    ),
  );
  $context->condition_mode = 0;

  // Translatables
  // Included for use with string extractors like potx.
  t('Group');
  t('Group content type');

  return $export;
}
