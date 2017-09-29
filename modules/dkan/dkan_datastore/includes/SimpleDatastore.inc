<?php
module_load_include("php", "dkan_datastore", "src/DatastoreInterface");
module_load_include("php", "dkan_datastore", "vendor/autoload");

/**
 * @file
 * DKAN Datatore.
 */

/**
 * Class for Null Datastores.
 */
class SimpleDatastore implements \Dkan\Datastore\DatastoreInterface {
  private $node;

  /**
   * Sets default items for datastore object.
   */
  public function __construct($uuid) {
    module_load_include("php", "dkan_datastore", "src/Helper/Node");
    $this->node = \Dkan\Datastore\Helper\Node::getNodeFromUuid($uuid);
  }

  /**
   * A machine readable identifier for a datastore.
   */
  public function getId() {
    return "simple";
  }

  /**
   * A label is a human readable identifier for the datastore.
   */
  public function getLabel() {
    return "Simple";
  }

  public function getNode() {
    return $this->node;
  }

  /**
   * Progress of the import (0 - 1).
   */
  public function getImportProgress() {
    return 1.0;
  }

  /**
   * Provides themed status message for import.
   */
  public function getStatusMessage() {
    if ($this->exists()) {
      $result = db_select($this->getTableName(), 't')
        ->fields('t')
        ->execute();
      return "{$result->rowCount()} Items imported to the datastore.";
    }
    else {
      return "No data in the datastore yet!!!";
    }
  }

  /**
   * Starts import.
   */
  public function import() {
    $this->drop();

    $csv = \League\Csv\Reader::createFromPath($this->getFilePath());
    $data = $csv->fetchAll();

    $headers = array_shift($data);

    if (!db_table_exists($this->getTableName())) {
      $schema = [
        "description" => "A table to store the resource with fid {$this->getFileId()}",
        "fields" => []
      ];

      foreach ($headers as $header) {
        $schema['fields'][$this->machinize($header)] = [
          'type' => 'varchar',
          'length' => 256,
          'not null' => TRUE,
          'default' => ''
        ];
      }

      db_create_table($this->getTableName(), $schema);
    }

    foreach ($data as $row) {
      $fields = [];

      foreach ($headers as $key => $header) {
        $fields[$this->machinize($header)] = $row[$key];
      }

      db_insert($this->getTableName()) // Table name no longer needs {}
      ->fields($fields)->execute();
    }
  }

  public function drop() {
    if (db_table_exists($this->getTableName())) {
      db_drop_table($this->getTableName());
    }
  }

  public function delete() {
    $this->drop();
  }

  public function exists() {
    if (db_table_exists($this->getTableName())) {
      return TRUE;
    }
    return FALSE;
  }

  private function getFileId() {
    $wrapper = entity_metadata_wrapper("node", $this->node);
    return $wrapper->field_upload->fid->raw();
  }

  private function getFilePath() {
    $file = file_load($this->getFileId());
    $uri = $file->uri;
    return drupal_realpath($uri);
  }

  private function getTableName() {
    return "simple_datastore_{$this->getFileId()}";
  }

  private function machinize($string) {
    $machine = strtolower($string);
    $machine = str_replace(" ", "_", $machine);
    return $machine;
  }

}
