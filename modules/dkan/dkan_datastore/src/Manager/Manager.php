<?php

namespace Dkan\Datastore\Manager;

use Dkan\Datastore\LockableDrupalVariables;
use Dkan\Datastore\Parser\Csv;
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

  private $timeLimit;

  /**
   * Constructor.
   */
  public function __construct(Resource $resource) {
    $this->timeLimit = 0;

    $this->resource = $resource;

    $this->stateDataImport = self::DATA_IMPORT_UNINITIALIZED;
    $this->stateStorage = self::STORAGE_UNINITIALIZED;

    $this->configurableProperties = [];

    if (!$this->loadState()) {
      $this->setConfigurablePropertiesHelper([
        'delimiter' => ',',
        'quote' => '"',
        'escape' => '\\',
        'trailing_delimiter' => FALSE,
      ]);
      $this->initialization($resource);
    }
  }

  /**
   * Set the time limit.
   *
   * The import process will stop if it hits the time limit.
   *
   * @param int $seconds
   *   Number of seconds.
   */
  public function setImportTimelimit($seconds) {
    if ($seconds > 0) {
      $this->timeLimit = $seconds;
    }
    else {
      $this->timeLimit = 0;
    }
  }

  /**
   * Get resource.
   *
   * @return \Dkan\Datastore\Resource
   *   The resource associated with this datastore.
   */
  protected function getResource() {
    return $this->resource;
  }

  /**
   * Get parser.
   *
   * @return \Dkan\Datastore\Parser\Csv
   *   Parser object.
   */
  protected function getParser() {
    if (!$this->parser) {
      $this->parser = new Csv(
        $this->configurableProperties['delimiter'],
        $this->configurableProperties['quote'],
        $this->configurableProperties['escape'],
        ["\r", "\n"]
      );

      if (isset($this->configurableProperties['trailing_delimiter']) && $this->configurableProperties['trailing_delimiter'] == TRUE) {
        $this->parser->activateTrailingDelimiter();
      }
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
   * @param \Dkan\Datastore\Resource $resource
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
   * Get table schema.
   */
  public function getTableSchema() {
    $schema = [];
    $header = $this->getTableHeaders();
    foreach ($header as $field) {
      $schema['fields'][$field] = [
        'label' => $field,
        'type' => "text",
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
      $new = preg_replace("/[^A-Za-z0-9_ ]/", '', $field);
      $new = dkan_datastore_safe_name($new);
      $header[$key] = $new;
    }

    if (empty($header)) {
      throw new \Exception("Unable to get headers from {$this->resource->getFilePath()}");
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
        $this->setConfigurablePropertiesHelper($state['configurable_properties']);
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
  public function dropState() {
    $state_storage = new LockableDrupalVariables("dkan_datastore");
    $state_storage->delete($this->resource->getId());
    $this->stateStorage = self::STORAGE_UNINITIALIZED;
    $this->stateDataImport = self::DATA_IMPORT_UNINITIALIZED;
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

    $import_state = $this->storeRecords($this->timeLimit);
    if ($import_state === self::DATA_IMPORT_DONE) {
      $this->stateDataImport = self::DATA_IMPORT_DONE;
      $this->saveState();
    }
    elseif ($import_state === self::DATA_IMPORT_PAUSED) {
      $this->stateDataImport = self::DATA_IMPORT_PAUSED;
      $this->saveState();
    }
    elseif ($import_state === self::DATA_IMPORT_ERROR) {
      $this->stateDataImport = self::DATA_IMPORT_ERROR;
      watchdog("dkan_datastore", $this->errors);
      $this->saveState();
    }
    else {
      throw new \Exception("An incorrect state was returnd by storeRecords().");
    }

    return $import_state;
  }

  /**
   * Store records.
   *
   * Move records from the resource to the datastore.
   *
   * @return bool
   *   Whether the storing process was successful.
   */
  abstract protected function storeRecords($time_limit = 0);

  /**
   * {@inheritdoc}
   */
  public function drop() {
    $this->dropTable();
    $this->dropState();
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
    $this->setConfigurablePropertiesHelper($properties);
    $this->stateDataImport = self::DATA_IMPORT_READY;
    $this->saveState();
  }

  /**
   * Helper.
   */
  private function setConfigurablePropertiesHelper($properties) {
    $this->configurableProperties = $properties;
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

  /**
   * {@inheritdoc}
   */
  public function goToPausedState() {
    $this->stateDataImport = $this::DATA_IMPORT_PAUSED;
    $this->saveState();
  }

}
