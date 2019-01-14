<?php

namespace Dkan\DataDictionary;

/**
 *
 */
interface ValidationReportControllerInterface {

  /**
   * Resets the internal, static ValidationReport cache.
   *
   * @param $ids
   *   (optional) If specified, the cache is reset for the entities with the
   *   given ids only.
   */
  public function resetCache(array $ids = NULL);

  /**
   * Loads one or more entities.
   *
   * @param $ids
   *   An array of ValidationReport IDs, or FALSE to load all entities.
   * @param $conditions
   *   An array of conditions. Keys are field names on the ValidationReport's table.
   *   Values will be compared for equality. All the comparisons will be ANDed
   *   together.
   *
   * @return
   *   An array of ValidationReport objects indexed by their ids. When no results are
   *   found, an empty array is returned.
   */
  public function load($ids = array(), $conditions = array());

  /**
   *
   */
  public function save(ValidationReport $report, Resource $resource);

}
