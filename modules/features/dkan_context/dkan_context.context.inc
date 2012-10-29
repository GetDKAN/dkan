<?php
/**
 * @file
 * dkan_context.context.inc
 */

/**
 * Implements hook_context_default_contexts().
 */
function dkan_context_context_default_contexts() {
  $export = array();

  $context = new stdClass();
  $context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
  $context->api_version = 3;
  $context->name = 'dataset';
  $context->description = '';
  $context->tag = ' ';
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
        'dkan_dataset-dkan_dataset_social' => array(
          'module' => 'dkan_dataset',
          'delta' => 'dkan_dataset_social',
          'region' => 'sidebar_first',
          'weight' => '-10',
        ),
        'dkan_dataset-dkan_dataset_license' => array(
          'module' => 'dkan_dataset',
          'delta' => 'dkan_dataset_license',
          'region' => 'sidebar_first',
          'weight' => '-9',
        ),
      ),
    ),
  );
  $context->condition_mode = 0;

  // Translatables
  // Included for use with string extractors like potx.
  t(' ');
  $export['dataset'] = $context;

  $context = new stdClass();
  $context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
  $context->api_version = 3;
  $context->name = 'dataset-create';
  $context->description = '';
  $context->tag = 'Create';
  $context->conditions = array(
    'path' => array(
      'values' => array(
        'node/add/dataset' => 'node/add/dataset',
      ),
    ),
  );
  $context->reactions = array(
    'block' => array(
      'blocks' => array(
        'dkan_forms-dkan_forms_dataset_help' => array(
          'module' => 'dkan_forms',
          'delta' => 'dkan_forms_dataset_help',
          'region' => 'sidebar_first',
          'weight' => '-10',
        ),
      ),
    ),
  );
  $context->condition_mode = 0;

  // Translatables
  // Included for use with string extractors like potx.
  t('Create');
  $export['dataset-create'] = $context;

  $context = new stdClass();
  $context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
  $context->api_version = 3;
  $context->name = 'dataset-edit';
  $context->description = '';
  $context->tag = 'Edit';
  $context->conditions = array(
    'node' => array(
      'values' => array(
        'dataset' => 'dataset',
      ),
      'options' => array(
        'node_form' => '2',
      ),
    ),
    'path' => array(
      'values' => array(
        '~node/add/dataset' => '~node/add/dataset',
      ),
    ),
  );
  $context->reactions = array(
    'block' => array(
      'blocks' => array(
        'dkan_forms-dkan_forms_dataset_help' => array(
          'module' => 'dkan_forms',
          'delta' => 'dkan_forms_dataset_help',
          'region' => 'sidebar_first',
          'weight' => '-10',
        ),
        'dkan_forms-dkan_forms_resource_nodes' => array(
          'module' => 'dkan_forms',
          'delta' => 'dkan_forms_resource_nodes',
          'region' => 'sidebar_first',
          'weight' => '-10',
        ),
      ),
    ),
    'debug' => array(
      'debug' => 1,
    ),
  );
  $context->condition_mode = 1;

  // Translatables
  // Included for use with string extractors like potx.
  t('Edit');
  $export['dataset-edit'] = $context;

  $context = new stdClass();
  $context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
  $context->api_version = 3;
  $context->name = 'resource';
  $context->description = '';
  $context->tag = '';
  $context->conditions = array(
    'node' => array(
      'values' => array(
        'resource' => 'resource',
      ),
      'options' => array(
        'node_form' => '0',
      ),
    ),
  );
  $context->reactions = array();
  $context->condition_mode = 0;
  $export['resource'] = $context;

  $context = new stdClass();
  $context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
  $context->api_version = 3;
  $context->name = 'resource-create';
  $context->description = '';
  $context->tag = 'Create';
  $context->conditions = array(
    'path' => array(
      'values' => array(
        'node/add/resource' => 'node/add/resource',
      ),
    ),
  );
  $context->reactions = array(
    'block' => array(
      'blocks' => array(
        'dkan_forms-dkan_forms_resource_help' => array(
          'module' => 'dkan_forms',
          'delta' => 'dkan_forms_resource_help',
          'region' => 'sidebar_first',
          'weight' => '-13',
        ),
        'dkan_forms-dkan_forms_resource_nodes' => array(
          'module' => 'dkan_forms',
          'delta' => 'dkan_forms_resource_nodes',
          'region' => 'sidebar_first',
          'weight' => '-12',
        ),
      ),
    ),
    'debug' => array(
      'debug' => 1,
    ),
  );
  $context->condition_mode = 0;

  // Translatables
  // Included for use with string extractors like potx.
  t('Create');
  $export['resource-create'] = $context;

  $context = new stdClass();
  $context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
  $context->api_version = 3;
  $context->name = 'resource-edit';
  $context->description = '';
  $context->tag = 'Edit';
  $context->conditions = array(
    'node' => array(
      'values' => array(
        'resource' => 'resource',
      ),
      'options' => array(
        'node_form' => '2',
      ),
    ),
    'path' => array(
      'values' => array(
        '~node/add*' => '~node/add*',
      ),
    ),
  );
  $context->reactions = array(
    'block' => array(
      'blocks' => array(
        'dkan_forms-dkan_forms_resource_help' => array(
          'module' => 'dkan_forms',
          'delta' => 'dkan_forms_resource_help',
          'region' => 'sidebar_first',
          'weight' => '-10',
        ),
        'dkan_forms-dkan_forms_resource_nodes' => array(
          'module' => 'dkan_forms',
          'delta' => 'dkan_forms_resource_nodes',
          'region' => 'sidebar_first',
          'weight' => '-9',
        ),
        'book-navigation' => array(
          'module' => 'book',
          'delta' => 'navigation',
          'region' => 'sidebar_first',
          'weight' => '-8',
        ),
      ),
    ),
    'debug' => array(
      'debug' => 1,
    ),
  );
  $context->condition_mode = 1;

  // Translatables
  // Included for use with string extractors like potx.
  t('Edit');
  $export['resource-edit'] = $context;

  $context = new stdClass();
  $context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
  $context->api_version = 3;
  $context->name = 'sitewide';
  $context->description = 'Site-wide context';
  $context->tag = '';
  $context->conditions = array(
    'sitewide' => array(
      'values' => array(
        1 => 1,
      ),
    ),
  );
  $context->reactions = array(
    'block' => array(
      'blocks' => array(
        'system-main-menu' => array(
          'module' => 'system',
          'delta' => 'main-menu',
          'region' => 'branding',
          'weight' => '-10',
        ),
      ),
    ),
  );
  $context->condition_mode = 0;

  // Translatables
  // Included for use with string extractors like potx.
  t('Site-wide context');
  $export['sitewide'] = $context;

  return $export;
}
