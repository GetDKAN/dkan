<?php

namespace Dkan\DataDictionary;

/**
 * @class  ValidationReportControllerD7
 */
class ValidationReportControllerD7 implements ValidationReportControllerInterface {

  protected $validationReportCache = array();

  /**
   *
   */
  public function __construct() {
    $this->validationReportCache = &drupal_static(__CLASS__);

    if (!isset($this->validationReportCache)) {
      if (($cache = cache_get(__CLASS__)) && !empty($cache->data) && REQUEST_TIME < $cache->expire) {
        $this->validationReportCache = $cache->data;
      }
      else {
        $this->validationReportCache = cache_set(__CLASS__, $this->load());
      }
    }
  }

  /**
   * Builds the query to load the ValidationReport objects.
   *
   * @param $ids
   *   An array of ValidationReport object IDs, or FALSE to load all entities.
   * @param $conditions
   *   An array of conditions. Keys are field names on the ValidationReport object's base table.
   *   Values will be compared for equality. All the comparisons will be ANDed
   *   together.
   *
   * @return SelectQuery
   *   A SelectQuery object for loading the entity.
   */
  public function buildQuery(array $ids, $conditions = array()) {
    // TODO use a struct for table column.
    $query = db_select('dkan_data_dictionary_validation_report', 'vr')
      ->fields('vr');

    if (!empty($ids)) {
      $query->condition('vrid', $ids, 'IN');
    }

    if (!empty($conditions)) {
      foreach ($conditions as $field => $value) {
        $query->condition($field, $value);
      }
    }

    $query->orderBy('created', 'DESC');

    return $query;
  }

  /**
   *
   */
  public function resetCache(array $ids = NULL) {
    if (isset($ids)) {
      foreach ($ids as $id) {
        unset($this->validationReportCache[$id]);
      }
    }
    else {
      $this->validationReportCache = array();
    }
  }

  /**
   * Stores ValidationReport objects in the static ValidationReport objects cache.
   *
   * @param array $validation_reports
   *   ValidationReport object array to store in the cache.
   */
  public function cacheSet(array $validation_reports) {
    $this->validationReportCache += $validation_reports;
  }

  /**
   * Gets ValidationReport objects from the static cache.
   *
   * @param $ids
   *   If not empty, return ValidationReport objects that match these IDs.
   * @param $conditions
   *   If set, return ValidationReport objects that match all of these conditions.
   *
   * @return
   *   Array of ValidationReport objects from the cache.
   */
  public function cacheGet($ids, $conditions = array()) {
    $validation_reports = array();

    // Load any available validation_reports from the internal cache.
    if (!empty($this->validationReportCache)) {
      if ($ids) {
        $validation_reports += array_intersect_key($this->validationReportCache, array_flip($ids));
      }
      elseif ($conditions) {
        $validation_reports = $this->validationReportCache;
      }
    }

    // Exclude any ValidationReport object loaded from cache if they don't match $conditions.
    // This ensures the same behavior whether loading from memory or database.
    if ($conditions) {
      foreach ($validation_reports as $validationReport) {

        // Iterate over all conditions and compare them to the validationReport
        // properties. We cannot use array_diff_assoc() here since the
        // conditions can be nested arrays, too.
        foreach ($conditions as $property_name => $condition) {
          if (is_array($condition)) {

            // Multiple condition values for one property are treated as OR
            // operation: only if the value is not at all in the condition array
            // we remove the ValidationReport.
            if (!in_array($validationReport->{$property_name}, $condition)) {
              unset($validation_reports[$validationReport->vrid]);
              continue 2;
            }
          }
          elseif ($condition != $validationReport->{$property_name}) {
            unset($validation_reports[$validationReport->vrid]);
            continue 2;
          }
        }
      }
    }

    return $validation_reports;
  }

  /**
   *
   */
  public function load($ids = array(), $conditions = array()) {
    $validation_reports = array();

    // Create a new variable which is either a prepared version of the $ids
    // array for later comparison with the validationReport cache, or FALSE if no $ids
    // were passed. The $ids array is reduced as items are loaded from cache,
    // and we need to know if it's empty for this reason to avoid querying the
    // database when all requested validation_reports are loaded from cache.
    $passed_ids = !empty($ids) ? array_flip($ids) : FALSE;

    // Try to load validation_reports from the static cache.
    if ($this->validationReportCache) {
      $validationReports += $this
        ->cacheGet($ids, $conditions);

      // If any validation_reports were loaded, remove them from the ids still to load.
      if ($passed_ids) {
        $ids = array_keys(array_diff_key($passed_ids, $validation_reports));
      }
    }

    // Ensure integer validationReport IDs are valid.
    if (!empty($ids)) {
      $this->cleanIds($ids);
    }

    // Load any remaining validation_reports from the database. This is the case if $ids
    // is set to FALSE (so we load all ValidationReport objects), if there are any ids left to
    // load, or if $conditions was passed without $ids.
    if ($ids === FALSE || $ids || $conditions && !$passed_ids) {

      // Build the query.
      $query = $this->buildQuery($ids, $conditions);

      $raw = $query->execute()
        ->fetchAllAssoc('vrid');

      $queried_validation_reports = array_map(function ($item) {
        $vr = new ValidationReport($item);
        $vr->jsonUnserialize($item->report);

        return $vr;
      }, $raw);

      if (!empty($queried_validation_reports)) {
        $validation_reports += $queried_validation_reports;
      }
    }

    if ($this->validationReportCache) {
      // Add validation_reports to the cache.
      if (!empty($queried_validation_reports)) {
        $this->cacheSet($queried_validation_reports);
      }
    }

    // Ensure that the returned array is ordered the same as the original
    // $ids array if this was passed in and remove any invalid ids.
    if ($passed_ids) {

      // Remove any invalid ids from the array.
      $passed_ids = array_intersect_key($passed_ids, $validation_reports);
      foreach ($validation_reports as $validationReport) {
        $passed_ids[$validationReport->vrid] = $validationReport;
      }
      $validation_reports = $passed_ids;
    }

    return $validation_reports;
  }

  /**
   * Ensures integer ValidationReport IDs are valid.
   *
   * The identifier sanitization provided by this method has been introduced
   * as Drupal used to rely on the database to facilitate this, which worked
   * correctly with MySQL but led to errors with other DBMS such as PostgreSQL.
   *
   * @param array $ids
   *   The ValidationReport IDs to verify. Non-integer IDs are removed from this array if
   *   the ValidationReport type requires IDs to be integers.
   */
  protected function cleanIds(&$ids) {
    $ids = array_filter($ids, array(
      $this,
      'filterId',
    ));
    $ids = array_map('intval', $ids);
  }

  /**
   * Callback for array_filter that removes non-integer IDs.
   */
  protected function filterId($id) {
    return is_numeric($id) && $id == (int) $id;
  }

  /**
   *
   */
  public function save(ValidationReport $report, Resource $resource) {
    $account = $GLOBALS['user'];

    // TODO update to pull the information from ValidationReport.
    $record = array(
      'entity_type' => 'node',
      'bundle' => $resource->getBundle(),
      'entity_id' => $resource->getIdentifier(),
      'revision_id' => $resource->value()->vid,
      'uid' => $account->uid,
      'created' => time(),
      'report_valid' => $report->getReportStatus(),
      'report' => json_encode($report),
    );

    $saved_status = drupal_write_record('dkan_data_dictionary_validation_report', $record);

    // If ($saved_status != FALSE) {
    // $this->cacheSet(array($record));
    // }
    return $saved_status;
  }

}
