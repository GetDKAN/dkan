<?php

/**
 * @file
 * Resource fixtures migration class.
 */

/**
 * CKAN-based resource migration for fixtures.
 */
class DkanResourceImport extends MigrateCkanResourceBase {

  /**
   * Here we go.
   */
  public function __construct($arguments) {
    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_fixtures') . '/data/';
      $arguments['list_url'] = 'resource_list.json';
      $arguments['item_url'] = 'resource_show?id=:id.json';
    }
    parent::__construct($arguments);

    $this->addFieldMapping('path', 'path');
  }

  /**
   * Prepare all data for import.
   */
  public function prepareRow($row) {

    parent::prepareRow($row);

    // Import Resource file.
    if (isset($row->url) && trim($row->url)) {
      if (strpos($row->url, 'http://') === FALSE && strpos($row->url, 'https://') === FALSE) {
        $file_orig_dir = DRUPAL_ROOT . '/' . $this->endpoint;
        $file = dkan_fixtures_import_file($row->url, $file_orig_dir);
        $row->file = $file->fid;
      }
    }
  }

  /**
   * Do extra processing before the node is saved.
   */
  public function prepare($node, $row) {
    parent::prepare($node, $row);
    $node->path['pathauto'] = FALSE;
    // Assign to 'Admin' user.
    $node->uid = 1;
    // Set up as published.
    $node->status = NODE_PUBLISHED;
  }

}
