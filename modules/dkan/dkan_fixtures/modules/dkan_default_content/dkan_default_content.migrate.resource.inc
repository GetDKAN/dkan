<?php

/**
 * Migration class used to import Default Resources from fixtures.
 */
class DefaultContentResourceImport extends DkanResourceImport {

  /**
   * __construct().
   */
  public function __construct($arguments) {
    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_default_content') . '/data/';
      $arguments['list_url'] = 'resource_list.json';
      $arguments['item_url'] = 'resource_show?id=:id.json';
    }
    parent::__construct($arguments);
  }
}