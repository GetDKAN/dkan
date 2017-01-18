<?php

/**
 * @file
 * Group fixtures migration class.
 */

/**
 * Group fixtures migration class.
 */
class DkanGroupImport extends MigrateCkanGroupBase {

  /**
   * Here we go.
   */
  public function __construct($arguments) {

    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_fixtures') . '/data/';
      $arguments['list_url'] = 'group_list.json';
      $arguments['item_url'] = 'group_show?id=:id.json';
    }

    parent::__construct($arguments);

    // Add field mappings.
    $this->addFieldMapping('field_image', 'image_display_url');
    $this->addFieldMapping('field_image:alt', 'field_image_alt');
    $this->addFieldMapping('path', 'path');
  }

  /**
   * Prepare all data for import.
   */
  public function prepareRow($row) {

    parent::prepareRow($row);

    // Prepare 'field_image' field for field mapping.
    $row->image_display_url = DRUPAL_ROOT . '/' . $this->endpoint . $row->image_display_url;

  }

  /**
   * Do extra processing before the node is saved.
   */
  public function prepare($node, $row) {
    parent::prepare($node, $row);
    $node->path['pathauto'] = FALSE;
    // Set user as admin.
    $node->uid = 1;
    // Set node as published.
    $node->status = NODE_PUBLISHED;
    // Set body format.
    $node->body[LANGUAGE_NONE][0]['value_format'] = 'html';
    $node->body[LANGUAGE_NONE][0]['format'] = 'html';
  }

}
