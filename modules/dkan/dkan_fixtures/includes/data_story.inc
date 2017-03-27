<?php

/**
 * @file
 * Migration class used to import Data Stories from fixtures.
 */

/**
 * Data stories fixtures migration class.
 */
class DkanDataStoriesImport extends DkanPanelizedNodesImport {

  /**
   * {@inheritdoc}
   */
  public function __construct($arguments) {

    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_fixtures') . '/data/';
      $arguments['list_url'] = 'dkan_data_story_list.json';
      $arguments['item_url'] = 'dkan_data_story_show%3Fid=:id.json';
    }
    $arguments['content_type'] = 'dkan_data_story';

    parent::__construct($arguments);

    // Add field mappings.
    $this->addFieldMapping('field_image', 'field_image');
    $this->addFieldMapping('field_image:alt', 'field_image_alt');
    $this->addFieldMapping('field_tags', 'tags');
    $this->addFieldMapping('field_topic', 'topics');
    $this->addFieldMapping('path', 'path');
  }

  /**
   * Do extra processing before the node is saved.
   */
  public function prepare($node, $row) {
    parent::prepare($node, $row);
    $node->path['pathauto'] = FALSE;
  }

  /**
   * Prepare row data for import.
   */
  public function prepareRow($row) {

    parent::prepareRow($row);

    // Prepare 'field_image' field for field mapping.
    $row->field_image = DRUPAL_ROOT . '/' . $this->endpoint . $row->field_image;

    // Prepare 'Tags' for field mapping.
    $tags = array();
    if (isset($row->field_tags)) {
      foreach ($row->field_tags as $tag) {
        $tags[] = $tag->name;
      }
    }
    $row->tags = $tags;

    // Prepare 'Topics' for field mapping.
    $topics = array();
    if (isset($row->field_topic)) {
      foreach ($row->field_topic as $topic) {
        $topics[] = $topic->name;
      }
    }
    $row->topics = $topics;
  }

}
