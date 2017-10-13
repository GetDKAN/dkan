<?php

/**
 * Class DkanDatastoreCliWrapper.
 */
class DkanDatastoreCliWrapper {

  private $datastore;
  private $node;

  /**
   * DkanDatastoreCliWrapper constructor.
   */
  public function __construct($uuid) {
    $this->datastore = dkan_datastore_go($uuid);
    $this->node = $this->datastore->getNode();
  }

  /**
   * Create from file uri.
   */
  public function createFromFileUri($uri = '', $copy_file = TRUE) {

    if ($uri) {
      return $this->updateFromFileUri($uri, $copy_file);
    }
    else {
      return FALSE;
    }
  }

  /**
   * Updates the resource file using the URI.
   *
   * @param string $uri
   *   The URI, ie public://myfile.png, for the file.
   * @param bool $copy_file
   *   A boolean indicating whether to copy the file locally.
   *   If set to TRUE the file is copied locally.
   */
  public function updateFromFileUri($uri = '', $copy_file = TRUE) {
    $file = FALSE;

    // Sanity check: make sure the file exits.
    if (!file_exists($uri)) {
      return FALSE;
    }

    if ($copy_file) {
      // Generate destination based on the URI.
      $destination = 'public://' . drupal_basename($uri);

      $data = file_get_contents($uri);
      // Using the same name can break the update process when the current file
      // is cleaned.
      $file = file_save_data($data, $destination, FILE_EXISTS_RENAME);
    }
    else {
      $file = file_uri_to_object($uri);
    }

    // At this point we should have a valid file object.
    if (!$file) {
      return FALSE;
    }

    return $this->updateFromFile($file);
  }

  /**
   * Update resource with file.
   *
   * @param object $file
   *   Drupal file object.
   * @param bool $use_current_setttings
   *   If set to TRUE use the current file visibility settings.
   */
  public function updateFromFile($file, $use_current_setttings = TRUE) {
    // Get current file.
    $current_file = $this->file();

    if ($current_file && $use_current_setttings) {
      // Copy view settings from current file.
      if (isset($current_file->map)) {
        $file->map = $current_file->map;
      }
      if (isset($current_file->grid)) {
        $file->grid = $current_file->grid;
      }
      if (isset($current_file->graph)) {
        $file->graph = $current_file->graph;
      }

      // Delete current file.
      $this->deleteFile();
    }

    // Save the file if new.
    if (empty($file->fid)) {
      file_save($file);
    }

    $file_field = dkan_datastore_file_upload_field();
    $this->node->{$file_field} = array(LANGUAGE_NONE => array((array) $file));
    node_save($this->node);

    return TRUE;
  }

  /**
   * Delete file from datastore.
   */
  public function deleteFile() {

    // Get current file.
    $current_file = $this->file();

    if ($current_file) {
      // If the datastore has items, delete them.
      $this->datastore->delete();
      // Delete file.
      file_delete($current_file, TRUE);
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Retrieves file object from datastore.
   */
  public function file() {
    if ($file = dkan_datastore_file_field($this->node)) {
      return $file;
    }
  }

}
