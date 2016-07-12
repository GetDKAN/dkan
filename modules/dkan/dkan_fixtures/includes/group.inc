<?php

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
  }

  /**
   * Prepare all data for import.
   */
  public function prepareRow($row) {

    parent::prepareRow($row);

    // Import file for Group image.
    if (isset($row->image_display_url) && trim ($row->image_display_url)) {
      if (strpos($row->image_display_url, 'http://') === FALSE && strpos($row->image_display_url, 'https://') === FALSE) {
        $file_orig_dir = DRUPAL_ROOT . '/' . $this->endpoint;
        $file = dkan_fixtures_import_file($row->image_display_url, $file_orig_dir);
        $row->file = $file->fid;
      }
    }
  }

  /**
   * Do extra processing before the node is saved.
   */
  public function prepare($node, $row) {

    // Set user as admin.
    $node->uid = 1;
    // Set node as published.
    $node->status = NODE_PUBLISHED;
    // Set group image.
    $node->field_image[LANGUAGE_NONE][] = array(
      'fid' => $row->file,
      'display' => 1,
      'description' => '',
    );
    // Set body format.
    $node->body[LANGUAGE_NONE][0]['value_format'] = 'html';
    $node->body[LANGUAGE_NONE][0]['format'] = 'html';
  }
}
