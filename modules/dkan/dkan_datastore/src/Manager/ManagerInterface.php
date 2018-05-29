<?php

namespace Dkan\Datastore\Manager;

/**
 * Interface ManagerInterface.
 */
interface ManagerInterface {

  const STORAGE_UNINITIALIZED = 0;
  const STORAGE_INITIALIZED = 1;

  const DATA_IMPORT_UNINITIALIZED = 2;
  const DATA_IMPORT_IN_PROGRESS = 3;
  const DATA_IMPORT_DONE = 4;
  const DATA_IMPORT_ERROR = 5;
  const DATA_IMPORT_READY = 6;

  /**
   * Drops a datastore.
   */
  public function drop();

  /**
   * Imports data into a datastore.
   */
  public function import();

  /**
   * Import time limit.
   */
  public function setImportTimelimit($seconds);

  /**
   * Delete the rows (records) from the datastore.
   */
  public function deleteRows();

  /**
   * Get status.
   *
   * The status (state information) is meant to inform the
   * outside world of what the status of a given datastore is.
   *
   * @return array
   *   An array with the status (state) information.
   */
  public function getStatus();

  /**
   * Get Configurable properties.
   *
   * If a datastore requires information from the user, it
   * can make the system aware of those requirements by
   * returning an array from this method.
   *
   * @return array
   *   An associative array where the key is the property name
   *   and the value is the default value for the property.
   */
  public function getConfigurableProperties();

  /**
   * Setter.
   */
  public function setConfigurableProperties($properties);

  /**
   * The number of records in the datastore.
   */
  public function numberOfRecordsImported();

  /**
   * Get table name.
   *
   * The datastore mechanics are still tied to one type of storage:
   * Tables in the drupal database.
   *
   * @return string
   *   The name of that table.
   */
  public function getTableName();

  /**
   * Get table headers.
   *
   * @return array
   *   The names of the columns in the db table.
   */
  public function getTableHeaders();

  /**
   * Get errors.
   *
   * @return array
   *   An array of error messages (Strings).
   */
  public function getErrors();

  /**
   * Save state.
   *
   * Move the current state of the datastore manager to persistent storage.
   */
  public function saveState();

}
