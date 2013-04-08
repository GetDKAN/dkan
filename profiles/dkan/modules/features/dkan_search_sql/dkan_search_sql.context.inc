<?php
/**
 * @file
 * dkan_search_sql.context.inc
 */

/**
 * Implements hook_context_default_contexts().
 */
function dkan_search_sql_context_default_contexts() {
  $export = array();

  $context = new stdClass();
  $context->disabled = FALSE; /* Edit this to true to make a default context disabled initially */
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
          'weight' => '-20',
        ),
        'facetapi-Mpg7iEG3Hm2flFXehMZeicnSO5efkqgl' => array(
          'module' => 'facetapi',
          'delta' => 'Mpg7iEG3Hm2flFXehMZeicnSO5efkqgl',
          'region' => 'sidebar_first',
          'weight' => '-19',
        ),
        'facetapi-VBBDnNWYupC3PxCnXOq4e4ds4sAU4W7s' => array(
          'module' => 'facetapi',
          'delta' => 'VBBDnNWYupC3PxCnXOq4e4ds4sAU4W7s',
          'region' => 'sidebar_first',
          'weight' => '-18',
        ),
        'facetapi-wsVI1ENUXwf4Rz08n9fg2WvfQ0Gs5h2a' => array(
          'module' => 'facetapi',
          'delta' => 'wsVI1ENUXwf4Rz08n9fg2WvfQ0Gs5h2a',
          'region' => 'sidebar_first',
          'weight' => '-17',
        ),
        'facetapi-y0bmt1wN50B9oCuMeoazc1hpKiMkSQ2A' => array(
          'module' => 'facetapi',
          'delta' => 'y0bmt1wN50B9oCuMeoazc1hpKiMkSQ2A',
          'region' => 'sidebar_first',
          'weight' => '-16',
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
