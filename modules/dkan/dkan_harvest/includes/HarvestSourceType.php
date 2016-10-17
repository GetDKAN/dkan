<?php

/**
 * @file
 * File for dkan_harvest HarvestSourceType class. This will serve as a in code
 * documentation as well, please update the comments if you update the class!
 */

/**
 * Dkan Harvest HarvestSource Object is user to store the sources properties needed to
 * indentify a source to harvest. Those properties are:
 *
 * - 'machine_name' (String, Required): Unique identifier for this source.
 * - 'cache_callback' (String, Required): function to be used when caching a
 * source of the current type.
 * - 'migration_class' (String, Required): machine name of the migration called
 * during the import of a source.
 * - 'label' (String, Optional): User friendly name used to display this source. If
 * empty will use the 'machine_name' property.
 */
class HarvestSourceType {
  public $machine_name;
  public $label;
  public $cache_callback;
  public $migration_class;

  /**
   * Constructor for HarvestSourceType class.
   */
  public function __construct($machine_name, array $source_type) {

    if (!is_string($machine_name)) {
      // TODO Make sure the type exists.
      throw new Exception('HarvestSourceType machine_name invalid!');
    } else {
      $this->machine_name = $machine_name;
    }

    if (isset($source_type['cache callback']) && function_exists($source_type['cache callback'])) {
      $this->cache_callback = $source_type['cache callback'];
    } else {
      throw new Exception('HarvestSourceType cache_callback invalid!');
    }

    if (isset($source_type['migration class']) && class_exists($source_type['migration class'])) {
      $this->migration_class = $source_type['migration class'];
    } else {
      throw new Exception('HarvestSourceType migrate invalid!');
    }

    // Optional properties.
    // TODO add validation code for all the remining propreties.
    if (!isset($source_type['label']) || !is_string($source_type['label'])) {
      $this->label = $this->machine_name;
    } else {
      $this->label = $source_type['label'];
    }
  }

  /**
   * Return A HarvestSourceType from machine_name if exists.
   *
   * @param $machine_name: dkan_harvest source type machine name.
   *
   * @throws Exception if the source type corresponding to the machine_name is
   * not found.
   *
   * @return HarvestSourceType
   */
  public static function getSourceType($machine_name) {
    $sourceTypes = dkan_harvest_source_types_definition();
    if (isset($sourceTypes[$machine_name])) {
      return $sourceTypes[$machine_name];
    }
    throw new Exception('HarvestSourceType machine_name not found!');
  }
}
