<?php

/**
 * @file
 * Migration class used to import Default Groups from fixtures.
 */

/**
 * Default content extention of group fixtures migration class.
 */
class DefaultContentGroupImport extends DkanGroupImport {

  /**
   * {@inheritdoc}
   */
  public function __construct($arguments) {

    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_default_content') . '/data/';
      $arguments['list_url'] = 'group_list.json';
      $arguments['item_url'] = 'group_show?id=:id.json';
    }

    parent::__construct($arguments);
  }

}
