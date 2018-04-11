<?php

namespace Dkan\Datastore\Manager;

interface ManagerInterface {

  const STORAGE_UNINITIALIZED = 0;
  const STORAGE_INITIALIZED = 1;

  const DATA_IMPORT_UNINITIALIZED = 2;
  const DATA_IMPORT_IN_PROGRESS = 3;
  const DATA_IMPORT_DONE = 4;
  const DATA_IMPORT_ERROR = 5;

  public function drop();

  public function import();

  public function deleteRows();

  public function getStatus();

  public function getConfigurableProperties();

  public function setConfigurableProperties($properties);

  public function numberOfRecordsImported();

  public function initializeStorage();

  public function getTableName();

  public function getTableHeaders();

  public function getErrors();

}