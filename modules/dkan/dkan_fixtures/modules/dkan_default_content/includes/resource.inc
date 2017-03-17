<?php

/**
 * @file
 * Migration class used to import Default Resources from fixtures.
 */

/**
 * Default content extention of resource fixtures migration class.
 */
class DefaultContentResourceImport extends DkanResourceImport {

  /**
   * {@inheritdoc}
   */
  public function __construct($arguments) {
    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_default_content') . '/data/';
      $arguments['list_url'] = 'resource_list.json';
      $arguments['item_url'] = 'resource_show%3Fid=:id.json';
    }
    parent::__construct($arguments);
  }

}
