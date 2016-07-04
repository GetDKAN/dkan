<?php

/**
 * Migration class used to import Default Data Stories from fixtures.
 */
class DefaultContentPagesImport extends DefaultContentPanelizedNodesImport {

  /**
   * __construct().
   */
  public function __construct($arguments) {
    $arguments['endpoint'] = drupal_get_path('module', 'dkan_default_content') . '/data/';
    $arguments['list_url'] = $arguments['endpoint'] . 'page_list.json';
    $arguments['item_url'] = $arguments['endpoint'] . 'page_show?id=:id.json';
    $arguments['content_type'] = 'page';

    parent::__construct($arguments);

    // Add field mappings.
    $this->addFieldMapping('body', 'body_value');
    $this->addFieldMapping('body:summary', 'body_summary');
    $this->addFieldMapping('body:format', 'body_format');
  }

  /**
   *
   */
  public function prepareRow($row) {
    // Prepare 'body' field for field mapping.
    $body = $row->body->und[0];
    $row->body_value = $body->value;
    $row->body_summary = $body->summary;
    $row->body_format = $body->format;
  }

  /**
   *
   */
  public function complete($entity, $row) {

    parent::complete($entity, $row);

    // Setup imported page as the homepage.
    if (isset($row->is_homepage)) {
      variable_set('site_frontpage', 'node/' . $entity->nid);
    }
  }
}