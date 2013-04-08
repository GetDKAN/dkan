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
          'weight' => '-23',
        ),
        'facetapi-Mpg7iEG3Hm2flFXehMZeicnSO5efkqgl' => array(
          'module' => 'facetapi',
          'delta' => 'Mpg7iEG3Hm2flFXehMZeicnSO5efkqgl',
          'region' => 'sidebar_first',
          'weight' => '-22',
        ),
        'facetapi-YS5bB6HT1ceVd06CYT1Lm4DvMx1nNd5M' => array(
          'module' => 'facetapi',
          'delta' => 'YS5bB6HT1ceVd06CYT1Lm4DvMx1nNd5M',
          'region' => 'sidebar_first',
          'weight' => '-21',
        ),
        'facetapi-dqCRnp6cKP1cSzAsNB6zqFtY9AVEa0mp' => array(
          'module' => 'facetapi',
          'delta' => 'dqCRnp6cKP1cSzAsNB6zqFtY9AVEa0mp',
          'region' => 'sidebar_first',
          'weight' => '-20',
        ),
        'facetapi-wsVI1ENUXwf4Rz08n9fg2WvfQ0Gs5h2a' => array(
          'module' => 'facetapi',
          'delta' => 'wsVI1ENUXwf4Rz08n9fg2WvfQ0Gs5h2a',
          'region' => 'sidebar_first',
          'weight' => '-18',
        ),
        'facetapi-y0bmt1wN50B9oCuMeoazc1hpKiMkSQ2A' => array(
          'module' => 'facetapi',
          'delta' => 'y0bmt1wN50B9oCuMeoazc1hpKiMkSQ2A',
          'region' => 'sidebar_first',
          'weight' => '-17',
        ),
        'og_extras-group_info' => array(
          'module' => 'og_extras',
          'delta' => 'group_info',
          'region' => 'sidebar_first',
          'weight' => '-16',
        ),
      ),
    ),
  );
  $context->condition_mode = 0;

  // Translatables
  // Included for use with string extractors like potx.
  t('Group');
  t('Group content type');
  $export['group_group'] = $context;

  return $export;
}
