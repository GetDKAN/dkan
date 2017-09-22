<?php

/**
 * @file
 * Datastore.inc.
 */

/**
 * Creates Datastore class.
 */
abstract class Datastore {
  protected $uuid;
  protected $config;
  public $imported;
  public $endpoint;
  public $node;

  /**
   * Constructor, set uuid and load default configuration.
   */
  protected function __construct($uuid) {
    // Set this object's id.
    $this->uuid = $uuid;
    // Make sure configuration is populated.
    $this->config = $this->configDefaults();
    $this->node = $this->node($uuid);
  }

  /**
   * Config defaults.
   */
  public function configDefaults() {
  }

  /**
   * Loads node from uuuid.
   */
  protected function node($uuid) {
    static $nodes = array();
    if (!isset($node[$uuid])) {
      $nid = $this->getNid($uuid);
      if (!$nid) {
        throw new Exception(t("uuid !uuid not found.", array('!uuid' => $uuid)));
      }
      else {
        $nodes[$uuid] = node_load($nid);
      }
    }
    return $nodes[$uuid];
  }

  /**
   * Gets nid using uuid.
   */
  public function getNid($uuid) {
    $nid = db_query('SELECT nid FROM {node} WHERE uuid = :uuid', array(':uuid' => $uuid))->fetchField();
    if ($nid) {
      return $nid;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Instantiate a Datastore object with some baseline caching.
   *
   * Don't use directly, use dkan_datastore_go() instead.
   */
  public static function instance($class, $id) {
    // This is useful at least as long as we're developing.
    if (empty($id)) {
      // Create an empty resource.
      $resource = new stdClass();
      $resource->title = t('Automatically created by datastore');
      $resource->type = dkan_datastore_node_type();
      $resource->language = LANGUAGE_NONE;
      $resource->status = '1';
      $resource->promote = '0';
      $resource->sticky = '0';
      $resource->comment = '0';
      $resource->translate = '0';
      node_save($resource);

      $id = $resource->uuid;
    }

    static $instances = array();
    if (!isset($instances[$class][$id])) {
      $instances[$class][$id] = new $class($id);
    }
    return $instances[$class][$id];
  }

  /**
   * Imports file contents into the datastore.
   */
  public function import() {
  }

  /**
   * Drops datastore.
   */
  public function drop($form) {
  }

  /**
   * Retrieves headers.
   */
  public function headers() {
  }

  /**
   * Provides number of rows in datastore.
   */
  public function rows() {
  }

  /**
   * Provides API endpoint.
   */
  public function apiUri() {
  }

  /**
   * Deletes rows from the Datastore.
   */
  public function deleteRows($rows = 'all') {
  }

  /**
   * Unlocks locked datastore for updates.
   */
  public function unlock() {
  }

  /**
   * Retrieves file object from datastore.
   */
  public function file() {
    if ($file = dkan_datastore_file_field($this->node)) {
      return $file;
    }
  }

  /**
   * Updates the datastore with file.
   */
  public function updateFromFileUri($uri = '', $copy_file = TRUE) {

  }

  /**
   * Creates a datastore with file.
   */
  public function createFromFileUri($uri = '', $copy_file = TRUE) {

  }

  /**
   * Deletes the file.
   */
  public function deleteFile() {

  }

  /**
   * Retrieves file URI.
   */
  public function fileUri() {
    $file = $this->file();
    return $file->uri;
  }

  /**
   * Retrieves file URL.
   */
  public function fileUrl() {
    $file = $this->file();
    return file_create_url($file->uri);
  }

  /**
   * Retrieves timestamp for last import.
   */
  public function lastImport() {
  }

  /**
   * Retrieves progress of datastore import.
   */
  public function progress() {
  }

  /**
   * Retrieves status of the datastore.
   *
   * TODO: Document status levels.
   */
  public function status() {
  }

}

/**
 * Declares interface for forms.
 *
 * Declares interface for forms required for interaction with Datastores through
 * the DKAN user interface.
 */
interface DatastoreFormInterface {

  /**
   * Manage form.
   */
  public function manageForm(&$form_state);

  /**
   * Manage form submit.
   */
  public function manageFormSubmit(&$form_state);

  /**
   * Drop form.
   */
  public function dropForm(&$form_state);

  /**
   * Drop form submit.
   */
  public function dropFormSubmit(&$form_state);

  /**
   * Api form.
   */
  public function apiForm();

}
