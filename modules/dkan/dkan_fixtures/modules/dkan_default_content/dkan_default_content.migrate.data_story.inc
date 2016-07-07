<?php

/**
 * Migration class used to import Default Data Stories from fixtures.
 */
class DefaultContentDataStoriesImport extends DefaultContentPanelizedNodesImport {

  /**
   * __construct().
   */
  public function __construct($arguments) {
    $arguments['endpoint'] = drupal_get_path('module', 'dkan_default_content') . '/data/';
    $arguments['list_url'] = $arguments['endpoint'] . 'dkan_data_story_list.json';
    $arguments['item_url'] = $arguments['endpoint'] . 'dkan_data_story_show?id=:id.json';
    $arguments['content_type'] = 'dkan_data_story';

    parent::__construct($arguments);

    // Add field mappings.
    $this->addFieldMapping('field_image', 'field_image');
    $this->addFieldMapping('body', 'body_value');
    $this->addFieldMapping('body:summary', 'body_summary');
    $this->addFieldMapping('body:format', 'body_format');
    $this->addFieldMapping('field_tags', 'tags');
    $this->addFieldMapping('field_topic', 'topics');
  }

  /**
   * Prepare row data for import.
   */
  public function prepareRow($row) {

    parent::prepareRow($row);

    // Prepare 'field_image' field for field mapping.
    $row->field_image = DRUPAL_ROOT . '/' . $this->endpoint . $row->field_image;

    // Prepare 'body' field for field mapping.
    $body = $row->body->und[0];
    $row->body_value = $body->value;
    $row->body_summary = $body->summary;
    $row->body_format = $body->format;

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