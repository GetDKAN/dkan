<?php

/**
 * Migration class used to import Default Data Stories from fixtures.
 */
class DefaultContentDataStoriesImport extends DkanDataStoriesImport {

  /**
   * __construct().
   */
  public function __construct($arguments) {

    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_default_content') . '/data/';
      $arguments['list_url'] = 'dkan_data_story_list.json';
      $arguments['item_url'] = 'dkan_data_story_show?id=:id.json';
    }

    parent::__construct($arguments);
  }
}