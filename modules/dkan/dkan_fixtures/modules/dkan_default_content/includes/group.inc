<?php

/**
 * Migration class used to import Default Groups from fixtures.
 */
class DefaultContentGroupImport extends DkanGroupImport {

  /**
   * __construct().
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