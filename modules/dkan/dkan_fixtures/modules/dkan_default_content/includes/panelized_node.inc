<?php

/**
 * Migration class used to import Default Panelized Nodes from fixtures.
 */
class DefaultContentPanelizedNodesImport extends DkanPanelizedNodesImport {

  /**
   * __construct().
   */
  public function __construct($arguments) {

    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_default_content') . '/data/';
      $arguments['list_url'] = 'panelized_node_list.json';
      $arguments['item_url'] = 'panelized_node_show?id=:id.json';
    }

    parent::__construct($arguments);
  }
}