<?php

/**
 * Migration class used to import Data Stories from fixtures.
 */
class DkanPagesImport extends DkanPanelizedNodesImport {

  /**
   * __construct().
   */
  public function __construct($arguments) {

    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_fixtures') . '/data/';
      $arguments['list_url'] = 'page_list.json';
      $arguments['item_url'] = 'page_show?id=:id.json';
    }
    $arguments['content_type'] = 'page';

    parent::__construct($arguments);

    $this->addFieldMapping('path', 'path');
  }

  /**
   * Do extra processing before the node is saved.
   */
  public function prepare($node, $row) {
    parent::prepare($node, $row);
    $node->path['pathauto'] = FALSE;
  }  

}