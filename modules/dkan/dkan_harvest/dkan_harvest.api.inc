<?php

/**
 * @file
 * Documentation for hooks defined by Dkan Harvest.
 */

/**
 * Register a Dkan Harvest Source type.
 *
 * @see HarvestSourceType
 */
function dkan_harvest_harvest_sources_types() {
  return array(
    'harvest_type_json' => array(
      'machine_name' => 'harvest_type_json',
      'name' => 'Harvest Source Type JSON',
      'cache callback' => 'mymodule_harvest_type_json',
      'migration class' => 'TypeHarvestMigrate',
    ),
  );
}
