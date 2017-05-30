<?php

/**
 * @file
 * Migration class used to import Default Visualization Entities from fixtures.
 */

/**
 * Default content extension of visualization entity fixtures migration class.
 */
class DefaultContentVisualizationEntityImport extends DkanVisualizationEntityImport {

  /**
   * {@inheritdoc}
   */
  public function __construct($arguments) {

    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_default_content') . '/data/';
      $arguments['list_url'] = 'visualization_entity_list.json';
      $arguments['item_url'] = 'visualization_entity_show%3Fid=:id.json';
    }

    parent::__construct($arguments);
  }

}
