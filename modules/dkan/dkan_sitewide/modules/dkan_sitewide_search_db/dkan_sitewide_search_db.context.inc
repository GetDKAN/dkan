<?php

/**
 * @file
 * dkan_sitewide_search_db.context.inc
 */

/**
 * Implements hook_context_default_contexts().
 */
function dkan_sitewide_search_db_context_default_contexts() {
  $export = array();

  $context = new stdClass();
  $context->disabled = TRUE;
  $context->api_version = 3;
  $context->name = 'dataset_search';
  $context->description = 'Blocks for the dataset search page';
  $context->tag = 'Page';
  $context->conditions = array(
    'views' => array(
      'values' => array(
        'dataset:page' => 'dataset:page',
      ),
    ),
  );
  $context->reactions = array(
    'block' => array(
      'blocks' => array(
        'facetapi-dqCRnp6cKP1cSzAsNB6zqFtY9AVEa0mp' => array(
          'module' => 'facetapi',
          'delta' => 'dqCRnp6cKP1cSzAsNB6zqFtY9AVEa0mp',
          'region' => 'sidebar_first',
          'weight' => '-23',
        ),
        'facetapi-yW6mJ3GaJF0LypnLPa433Bf9GI8QdWd9' => array(
          'module' => 'facetapi',
          'delta' => 'yW6mJ3GaJF0LypnLPa433Bf9GI8QdWd9',
          'region' => 'sidebar_first',
          'weight' => '-22',
        ),
        'facetapi-VBBDnNWYupC3PxCnXOq4e4ds4sAU4W7s' => array(
          'module' => 'facetapi',
          'delta' => 'VBBDnNWYupC3PxCnXOq4e4ds4sAU4W7s',
          'region' => 'sidebar_first',
          'weight' => '-21',
        ),
        'facetapi-wsVI1ENUXwf4Rz08n9fg2WvfQ0Gs5h2a' => array(
          'module' => 'facetapi',
          'delta' => 'wsVI1ENUXwf4Rz08n9fg2WvfQ0Gs5h2a',
          'region' => 'sidebar_first',
          'weight' => '-20',
        ),
        'facetapi-y0bmt1wN50B9oCuMeoazc1hpKiMkSQ2A' => array(
          'module' => 'facetapi',
          'delta' => 'y0bmt1wN50B9oCuMeoazc1hpKiMkSQ2A',
          'region' => 'sidebar_first',
          'weight' => '-19',
        ),
        'facetapi-AbuAoaiaio6DzODdgSKx7mdmYXObBzp7' => array(
          'module' => 'facetapi',
          'delta' => 'AbuAoaiaio6DzODdgSKx7mdmYXObBzp7',
          'region' => 'sidebar_first',
          'weight' => '-10',
        ),
      ),
    ),
  );
  $context->condition_mode = 0;

  // Translatables
  // Included for use with string extractors like potx.
  t('Blocks for the dataset search page');
  t('Page');
  $export['dataset_search'] = $context;

  return $export;
}
