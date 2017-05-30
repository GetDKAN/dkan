<?php

/**
 * @file
 * dkan_sitewide_context.context.inc
 */

/**
 * Implements hook_context_default_contexts().
 */
function dkan_sitewide_context_context_default_contexts() {
  $export = array();

  $context = new stdClass();
  $context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
  $context->api_version = 3;
  $context->name = 'dataset';
  $context->description = '';
  $context->tag = 'Node';
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
        'dkan_sitewide-dkan_sitewide_social' => array(
          'module' => 'dkan_sitewide',
          'delta' => 'dkan_sitewide_social',
          'region' => 'sidebar_first',
          'weight' => '-9',
        ),
        'dkan_sitewide-dkan_sitewide_license' => array(
          'module' => 'dkan_sitewide',
          'delta' => 'dkan_sitewide_license',
          'region' => 'sidebar_first',
          'weight' => '-8',
        ),
        'dkan_sitewide-dkan_sitewide_other_access' => array(
          'module' => 'dkan_sitewide',
          'delta' => 'dkan_sitewide_other_access',
          'region' => 'sidebar_first',
          'weight' => '-7',
        ),
        'dkan_sitewide-dkan_sitewide_data_extent' => array(
          'module' => 'dkan_sitewide',
          'delta' => 'dkan_sitewide_data_extent',
          'region' => 'sidebar_first',
          'weight' => '-6',
        ),
      ),
    ),
    'breadcrumb' => 'dataset',
  );
  $context->condition_mode = 0;

  // Translatables
  // Included for use with string extractors like potx.
  t('Node');
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
        'dkan_dataset-dkan_dataset_dataset_help' => array(
          'module' => 'dkan_dataset',
          'delta' => 'dkan_dataset_dataset_help',
          'region' => 'sidebar_first',
          'weight' => '-10',
        ),
      ),
    ),
    'breadcrumb' => 'dataset',
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
        'dkan_dataset-dkan_dataset_dataset_help' => array(
          'module' => 'dkan_dataset',
          'delta' => 'dkan_dataset_dataset_help',
          'region' => 'sidebar_first',
          'weight' => '-10',
        ),
      ),
    ),
    'breadcrumb' => 'dataset',
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
  $context->tag = 'Node';
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
  $context->reactions = array(
    'block' => array(
      'blocks' => array(
        'dkan_dataset-dkan_dataset_resource_nodes' => array(
          'module' => 'dkan_dataset',
          'delta' => 'dkan_dataset_resource_nodes',
          'region' => 'postscript_first',
          'weight' => '-10',
        ),
        'dkan_sitewide-dkan_sitewide_social' => array(
          'module' => 'dkan_sitewide',
          'delta' => 'dkan_sitewide_social',
          'region' => 'postscript_first',
          'weight' => '-9',
        ),
        'dkan_sitewide-dkan_sitewide_resource_add' => array(
          'module' => 'dkan_sitewide',
          'delta' => 'dkan_sitewide_resource_add',
          'region' => 'postscript_second',
          'weight' => '-10',
        ),
      ),
    ),
    'breadcrumb' => 'dataset',
  );
  $context->condition_mode = 0;

  // Translatables
  // Included for use with string extractors like potx.
  t('Node');
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
        'dkan_dataset-dkan_dataset_resource_help' => array(
          'module' => 'dkan_dataset',
          'delta' => 'dkan_dataset_resource_help',
          'region' => 'sidebar_first',
          'weight' => '-10',
        ),
        'dkan_dataset-dkan_dataset_resource_nodes' => array(
          'module' => 'dkan_dataset',
          'delta' => 'dkan_dataset_resource_nodes',
          'region' => 'sidebar_first',
          'weight' => '-9',
        ),
      ),
    ),
    'breadcrumb' => 'dataset',
  );
  $context->condition_mode = 0;

  // Translatables
  // Included for use with string extractors like potx.
  t('Create');
  $export['resource-create'] = $context;

  $context = new stdClass();
  $context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
  $context->api_version = 3;
  $context->name = 'resource-datastore';
  $context->description = '';
  $context->tag = 'Datastore';
  $context->conditions = array(
    'path' => array(
      'values' => array(
        'node/*/datastore*' => 'node/*/datastore*',
      ),
    ),
  );
  $context->reactions = array(
    'breadcrumb' => 'dataset',
  );
  $context->condition_mode = 0;

  // Translatables
  // Included for use with string extractors like potx.
  t('Datastore');
  $export['resource-datastore'] = $context;

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
        'dkan_dataset-dkan_dataset_resource_help' => array(
          'module' => 'dkan_dataset',
          'delta' => 'dkan_dataset_resource_help',
          'region' => 'sidebar_first',
          'weight' => '-10',
        ),
        'dkan_dataset-dkan_dataset_resource_nodes' => array(
          'module' => 'dkan_dataset',
          'delta' => 'dkan_dataset_resource_nodes',
          'region' => 'sidebar_first',
          'weight' => '-9',
        ),
      ),
    ),
    'breadcrumb' => 'dataset',
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
  $context->description = '';
  $context->tag = 'Sitewide';
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
        'dkan_sitewide-dkan_sitewide_search_bar' => array(
          'module' => 'dkan_sitewide',
          'delta' => 'dkan_sitewide_search_bar',
          'region' => 'branding',
          'weight' => '-9',
        ),
        'dkan_sitewide-dkan_sitewide_user_menu' => array(
          'module' => 'dkan_sitewide',
          'delta' => 'dkan_sitewide_user_menu',
          'region' => 'branding',
          'weight' => '-8',
        ),
        'dkan_sitewide-dkan_sitewide_footer_author' => array(
          'module' => 'dkan_sitewide',
          'delta' => 'dkan_sitewide_footer_author',
          'region' => 'footer',
          'weight' => '-10',
        ),
      ),
    ),
  );
  $context->condition_mode = 0;

  // Translatables
  // Included for use with string extractors like potx.
  t('Sitewide');
  $export['sitewide'] = $context;

  return $export;
}
