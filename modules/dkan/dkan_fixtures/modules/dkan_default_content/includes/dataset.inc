<?php

/**
 * Migration class used to import Default Datasets from fixtures.
 */
class DefaultContentDatasetImport extends DkanDatasetImport {

  /**
   * __construct().
   */
  public function __construct($arguments) {
    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_default_content') . '/data/';
      $arguments['list_url'] = 'package_list.json';
      $arguments['item_url'] = 'package_show?id=:id.json';
    }

    parent::__construct($arguments);
  }
}