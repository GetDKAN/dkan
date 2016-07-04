<?php

/**
 * Migration class used to import Default Data Dashboards from fixtures.
 */
class DefaultContentDataDashboardsImport extends DefaultContentPanelizedNodesImport {

  /**
   * __construct().
   */
  public function __construct($arguments) {
    $arguments['endpoint'] = drupal_get_path('module', 'dkan_default_content') . '/data/';
    $arguments['list_url'] = $arguments['endpoint'] . 'data_dashboard_list.json';
    $arguments['item_url'] = $arguments['endpoint'] . 'data_dashboard_show?id=:id.json';
    $arguments['content_type'] = 'data_dashboard';

    parent::__construct($arguments);
  }
}