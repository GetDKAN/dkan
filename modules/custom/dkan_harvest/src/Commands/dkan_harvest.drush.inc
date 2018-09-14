<?php

use Drupal\dkan_schema\Schema;
use Drupal\dkan_harvest\Harvest;

/**
 * Implements hook_drush_command().
 */
function dkan_harvest_drush_command() {
  $items['harvest-cache'] = [
    'description' => 'Cache a harvest source',
    'arguments' => [
      'sourceId' => 'The id for the harvest source',
    ],
    'drupal dependencies' => ['dkan_harvest'],
    'aliases' => ['harvest:cache'],
  ];
  $items['harvest-run'] = [
    'description' => 'Run a harvest using a source',
    'arguments' => [
      'sourceId' => 'The id for the harvest source',
    ],
    'drupal dependencies' => ['dkan_harvest'],
    'aliases' => ['harvest:run'],
  ];
  return $items;
}

/**
 * Caches a harvest source.
 */
function drush_dkan_harvest_harvest_cache($sourceId) {
	drush_print('Hello ' . $sourceId . ' !');
}
