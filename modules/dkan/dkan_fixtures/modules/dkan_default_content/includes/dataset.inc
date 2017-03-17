<?php

/**
 * @file
 * Migration class used to import Default Datasets from fixtures.
 */

/**
 * Default content extention of the dataset fixtures migration class.
 */
class DefaultContentDatasetImport extends DkanDatasetImport {

  /**
   * {@inheritdoc}
   */
  public function __construct($arguments) {
    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_default_content') . '/data/';
      $arguments['list_url'] = 'package_list.json';
      $arguments['item_url'] = 'package_show%3Fid=:id.json';
    }

    parent::__construct($arguments);
  }

}
