<?php

/**
 * Migration class used to import Data Dashboards from fixtures.
 */
class DkanDataDashboardsImport extends DkanPanelizedNodesImport {

  /**
   * __construct().
   */
  public function __construct($arguments) {

    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_fixtures') . '/data/';
      $arguments['list_url'] = 'data_dashboard_list.json';
      $arguments['item_url'] = 'data_dashboard_show?id=:id.json';
    }
    $arguments['content_type'] = 'data_dashboard';

    parent::__construct($arguments);

    // Add Field mappings.
    $this->addFieldMapping('field_topic', 'topics');
  }

  /**
   * Prepare row data for import.
   */
  public function prepareRow($row) {

    parent::prepareRow($row);

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