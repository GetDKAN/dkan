<?php
module_load_include("php", "dkan_datastore", "src/DatastoreInterface");

/**
 * @file
 * DKAN Datatore.
 */

/**
 * Class for Null Datastores.
 */
class NullDatastore implements \Dkan\Datastore\DatastoreInterface {
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
    return "null";
  }

  /**
   * A label is a human readable identifier for the datastore.
   */
  public function getLabel() {
    return "Null";
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
    return "Everything is Awesome!!!";
  }

  /**
   * Starts import.
   */
  public function import() {
  }

  public function drop() {
  }

  public function delete() {
  }

  public function exists() {
    return TRUE;
  }

}
