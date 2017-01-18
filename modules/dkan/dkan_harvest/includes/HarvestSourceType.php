<?php

/**
 * @file
 * File for dkan_harvest HarvestSourceType class.
 *
 * This will serve as a in code documentation as well.
 * Please update the comments if you update the class!
 */

/**
 * Dkan Harvest HarvestSource.
 *
 * Object is user to store the sources properties needed to
 * indentify a source to harvest.
 *
 * Those properties are:
 *
 * The 'machineName' (String, Required): Unique identifier for this source.
 * The 'cacheCallback' (String, Required): function to be used when caching a
 * source of the current type.
 * The 'migrationClass' (String, Required): machine name of the migration called
 * during the import of a source.
 * The 'label' (String, Optional): Name used to display this source.
 * If empty will use the 'machineName' property.
 */
class HarvestSourceType {
  public $machineName;
  public $label;
  public $cacheCallback;
  public $migrationClass;

  /**
   * Constructor for HarvestSourceType class.
   */
  public function __construct($machine_name, array $source_type) {

    if (!is_string($machine_name)) {
      // TODO Make sure the type exists.
      throw new Exception('HarvestSourceType machineName invalid!');
    }
    else {
      $this->machineName = $machine_name;
    }

    if (isset($source_type['cache callback']) && function_exists($source_type['cache callback'])) {
      $this->cacheCallback = $source_type['cache callback'];
    }
    else {
      throw new Exception('HarvestSourceType cacheCallback invalid!');
    }

    if (isset($source_type['migration class']) && class_exists($source_type['migration class'])) {
      $this->migrationClass = $source_type['migration class'];
    }
    else {
      throw new Exception('HarvestSourceType migrate invalid!');
    }

    // Optional properties.
    // TODO add validation code for all the remining propreties.
    if (!isset($source_type['label']) || !is_string($source_type['label'])) {
      $this->label = $this->machineName;
    }
    else {
      $this->label = $source_type['label'];
    }
  }

  /**
   * Return A HarvestSourceType from machineName if exists.
   *
   * @param string $machine_name
   *        DKAN Harvest source type machine name.
   *
   * @throws Exception
   *         If the source type corresponding to the machineName is
   *         not found.
   *
   * @return HarvestSourceType
   *         Returns the type of harvest source.
   */
  public static function getSourceType($machine_name) {
    $source_types = dkan_harvest_source_types_definition();
    if (isset($source_types[$machine_name])) {
      return $source_types[$machine_name];
    }
    throw new Exception('HarvestSourceType machineName not found!');
  }

}
