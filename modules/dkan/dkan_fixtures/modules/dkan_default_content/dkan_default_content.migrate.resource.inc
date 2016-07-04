<?php

/**
 * Migration class used to import Default Resources from fixtures.
 */
class DefaultContentResourceImport extends MigrateCkanResourceImport {

  /**
   * __construct().
   */
  public function __construct($arguments) {
    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_default_content') . '/data/';
      $arguments['list_url'] = drupal_get_path('module', 'dkan_default_content') . '/data/' . 'resource_list.json';
      $arguments['item_url'] = 'resource_show?id=:id.json';
    }
    parent::__construct($arguments);
  }

  /**
   * Do extra processing on the node data before its saved.
   */
  public function prepare($node, $row) {
    parent::prepare($node, $row);
    // Assign to 'Admin' user.
    $node->uid = 1;
    // Set up as published.
    $node->status = 1;
  }
}