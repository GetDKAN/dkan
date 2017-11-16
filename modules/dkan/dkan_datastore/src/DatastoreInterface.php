<?php

namespace Dkan\Datastore;

/**
 * Interface DatastoreInterface.
 */
interface DatastoreInterface {

  /**
   * DatastoreInterface constructor.
   */
  public function __construct($uuid);

  /**
   * Return the node (resource) associated with this datastore.
   */
  public function getNode();

  /**
   * A machine readable identifier for the datastore.
   */
  public function getId();

  /**
   * A label is a human readable identifier for the datastore.
   */
  public function getLabel();

  /**
   * Progress of the import (0 - 1).
   */
  public function getImportProgress();

  /**
   * A string with datastore status information.
   */
  public function getStatusMessage();

  /**
   * Import resources into storage.
   */
  public function import();

  /**
   * Drop the storage.
   */
  public function drop();

  /**
   * Remove all the items in storage.
   */
  public function delete();

  /**
   * Existance check.
   */
  public function exists();

  /**
   * Form to gather info specific to this datastore.
   */
  public function getConfigForm(&$form_state);

  /**
   * Config from submit handler.
   */
  public function configFormSubmitHandler(&$form_state);

}
