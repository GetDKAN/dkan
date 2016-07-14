<?php

/**
 * Migration class used to import Default Visualization Entities from fixtures.
 */
class DefaultContentVisualizationEntityImport extends DkanVisualizationEntityImport {

  /**
   * __construct().
   */
  public function __construct($arguments) {

    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_default_content') . '/data/';
      $arguments['list_url'] = 'visualization_entity_list.json';
      $arguments['item_url'] = 'visualization_entity_show?id=:id.json';
    }

    parent::__construct($arguments);
  }
}