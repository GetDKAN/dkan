<?php

/**
 * @file
 * Datastore.inc.
 */

namespace Dkan\Datastore\Manager;

use Dkan\Datastore\LockableDrupalVariables;
use Dkan\Datastore\CsvParser;
use Dkan\Datastore\Resource;

/**
 * Class Manager.
 */
abstract class Manager implements ManagerInterface {

  private $errors = [];

  private $stateDataImport;
  private $stateStorage;

  private $configurableProperties;

  private $resource;

  private $parser;

  /**
   * Constructor.
   */
  public function __construct(Resource $resource) {
    $this->resource = $resource;

    $this->stateDataImport = self::DATA_IMPORT_UNINITIALIZED;
    $this->stateStorage = self::STORAGE_UNINITIALIZED;

    $this->configurableProperties = [];

    if(!$this->loadState()) {
      $this->setConfigurableProperties(['delimiter' => ',', 'quote' => '"', 'escape' => '\\']);
      $this->initialization($resource);
      $this->saveState();
    }
  }

  protected function getResource() {
    return $this->resource;
  }

  protected function getParser() {
    if (!$this->parser) {
      $this->parser = new CsvParser(
        $this->configurableProperties['delimiter'],
        $this->configurableProperties['quote'],
        $this->configurableProperties['escape'],
        ["\r", "\n"]
      );
    }
    return $this->parser;
  }

  abstract function initialization(Resource $resource);

  public function initializeStorage() {
    $table_name = $this->getTableName();

    if (!db_table_exists($table_name)) {
      $schema = $this->getTableSchema();
      db_create_table($table_name, $schema);

      $this->stateStorage = self::STORAGE_INITIALIZED;
      $this->saveState();
    }
    elseif ($this->stateStorage == self::STORAGE_UNINITIALIZED) {
      $this->stateStorage = self::STORAGE_INITIALIZED;
      $this->saveState();
    }
  }

  private function getTableSchema() {
    $schema = [];
    $header = $this->getTableHeaders();
    foreach($header as $field) {
      $schema['fields'][$field] = [
        'type' => "varchar",
        'length' => 255,
      ];
    }
    return $schema;
  }

  public function getTableHeaders() {
    $parser = $this->getParser();

    $h = fopen($this->resource->getFilePath(), 'r');

    $headers = [];
    // @todo If csv cofiguration is incorrect we could end up getting the whole file.
    while ($chunk = fread($h, 32)) {
      $parser->feed($chunk);
      if ($record = $parser->getRecord()) {
        $headers = $record;
        break;
      }
    }

    fclose($h);
    $parser->reset();

    foreach ($headers as $key => $field) {
      $new = preg_replace("/[^A-Za-z0-9 ]/", '', $field);
      $new = trim($new);
      $new = strtolower($new);
      $new = str_replace(" ", "_", $new);
      $header[$key] = $new;
    }

    return $header;
  }

  private function loadState() {
    $state_storage = new LockableDrupalVariables("dkan_datastore");
    $state = $state_storage->get($this->resource->getId());

    if ($state) {
      if ($state['storage']) {
        $this->stateStorage = $state['storage'];
      }
      if ($state['data_import']) {
        $this->stateDataImport = $state['data_import'];
      }
      if ($state['configurable_properties']) {
        $this->setConfigurableProperties($state['configurable_properties']);
      }
      return TRUE;
    }
    return FALSE;
  }

  private function saveState() {
    $state_storage = new LockableDrupalVariables("dkan_datastore");
    $state = $state_storage->get($this->resource->getId());

    if (!$state) {
      $state = [];
    }

    $state['class'] = static::class;
    $state['storage'] = $this->stateStorage;
    $state['data_import'] = $this->stateDataImport;
    $state['configurable_properties'] = $this->getConfigurableProperties();

    $state_storage->set($this->resource->getId(), $state);
  }

  public function import() {
    $status = $this->getStatus();
    if ($status['storage'] == self::STORAGE_UNINITIALIZED) {
      $this->initializeStorage();
    }

    $this->stateDataImport = self::DATA_IMPORT_IN_PROGRESS;
    $this->saveState();

    if ($this->storeRecords() === TRUE) {
      $this->stateDataImport = self::DATA_IMPORT_DONE;
      $this->saveState();

      return TRUE;
    }
    else {
      $this->stateDataImport = self::DATA_IMPORT_ERROR;
      $this->saveState();

      return FALSE;
    }
  }

  abstract function storeRecords();

  public function drop() {
    $this->dropTable();
    $this->stateStorage = self::STORAGE_UNINITIALIZED;
    $this->stateDataImport = self::DATA_IMPORT_UNINITIALIZED;
    $this->saveState();
  }

  public function dropTable() {
    db_drop_table($this->getTableName());
  }

  public function deleteRows() {
    $this->stateDataImport = self::DATA_IMPORT_UNINITIALIZED;
    $this->saveState();
  }

  public function getStatus() {
    return ['storage' => $this->stateStorage, 'data_import' => $this->stateDataImport];
  }

  public function getTableName() {
    return "dkan_datastore_{$this->resource->getId()}";
  }

  public function getConfigurableProperties() {
    return $this->configurableProperties;
  }

  public function setConfigurableProperties($properties) {
    $this->configurableProperties = $properties;
    $this->saveState();
  }

  public function numberOfRecordsImported() {
    $table_name = $this->getTableName();
    $query = db_select($table_name, "t");

    try {
      return $query->countQuery()->execute()->fetchField();
    }
    catch(\Exception $exception) {
      return 0;
    }
  }

  protected function setError($error) {
    $this->errors[] = $error;
  }

  public function getErrors() {
    return $this->errors;
  }
}
