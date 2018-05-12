<?php

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

    if (!$this->loadState()) {
      $this->configurableProperties = [
        'delimiter' => ',',
        'quote' => '"',
        'escape' => '\\'
      ];
      $this->initialization($resource);
    }
  }

  /**
   * Get resource.
   *
   * @return Resource
   *   The resource associated with this datastore.
   */
  protected function getResource() {
    return $this->resource;
  }

  /**
   * Get pareser.
   *
   * @return CsvParser
   *   Parser object.
   */
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

  /**
   * Initialization.
   *
   * This method is called the first time an instance of a
   * Manager is created.
   *
   * This gives specific classes to affect what happens
   * during construction.
   *
   * @param Resource $resource
   *   Resource.
   */
  abstract protected function initialization(Resource $resource);

  /**
   * {@inheritdoc}
   */
  private function initializeStorage() {
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

  /**
   * Private method.
   */
  private function getTableSchema() {
    $schema = [];
    $header = $this->getTableHeaders();
    foreach ($header as $field) {
      $schema['fields'][$field] = [
        'type' => "varchar",
        'length' => 255,
      ];
    }
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
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

  /**
   * Private method.
   */
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

  /**
   * {@inheritdoc}
   */
  public function saveState() {
    $state_storage = new LockableDrupalVariables("dkan_datastore");
    $state_storage->set($this->resource->getId(), $this->getStatus());
  }

  /**
   * {@inheritdoc}
   */
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

  /**
   * Store records.
   *
   * Move records from the resource to the datastore.
   *
   * @return bool
   *   Whether the storing process was successful.
   */
  abstract protected function storeRecords();

  /**
   * {@inheritdoc}
   */
  public function drop() {
    $this->dropTable();
    $this->stateStorage = self::STORAGE_UNINITIALIZED;
    $this->stateDataImport = self::DATA_IMPORT_UNINITIALIZED;
    $this->saveState();
  }

  /**
   * Drop table.
   */
  protected function dropTable() {
    db_drop_table($this->getTableName());
  }

  /**
   * {@inheritdoc}
   */
  public function deleteRows() {
    db_delete($this->getTableName())->execute();
    $this->stateDataImport = self::DATA_IMPORT_UNINITIALIZED;
    $this->saveState();
  }

  /**
   * {@inheritdoc}
   */
  public function getStatus() {
    $state = [];

    $state['class'] = static::class;
    $state['storage'] = $this->stateStorage;
    $state['data_import'] = $this->stateDataImport;
    $state['configurable_properties'] = $this->getConfigurableProperties();

    return $state;
  }

  /**
   * {@inheritdoc}
   */
  public function getTableName() {
    return "dkan_datastore_{$this->resource->getId()}";
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigurableProperties() {
    return $this->configurableProperties;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfigurableProperties($properties) {
    $this->configurableProperties = $properties;
    $this->saveState();
  }

  /**
   * {@inheritdoc}
   */
  public function numberOfRecordsImported() {
    $table_name = $this->getTableName();
    $query = db_select($table_name, "t");

    try {
      return $query->countQuery()->execute()->fetchField();
    }
    catch (\Exception $exception) {
      return 0;
    }
  }

  /**
   * Set error.
   *
   * Adds an error message to the errors array.
   */
  protected function setError($error) {
    $this->errors[] = $error;
  }

  /**
   * {@inheritdoc}
   */
  public function getErrors() {
    return $this->errors;
  }

}
