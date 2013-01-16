<?php
/**
 * @file
 * dkan_context.context.inc
 */

/**
 * Implements hook_context_default_contexts().
 */
function dkan_sitewide_context_default_contexts() {
  $export = array();
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
          'weight' => '-24',
        ),
        'dkan_dataset-dkan_dataset_search_bar' => array(
          'module' => 'dkan_dataset',
          'delta' => 'dkan_dataset_search_bar',
          'region' => 'branding',
          'weight' => '-23',
        ),
        'dkan_sitewide-dkan_sitewide_user_menu' => array(
          'module' => 'dkan_sitewide',
          'delta' => 'dkan_sitewide_user_menu',
          'region' => 'branding',
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
