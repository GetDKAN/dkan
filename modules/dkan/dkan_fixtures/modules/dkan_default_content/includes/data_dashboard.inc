<?php

/**
 * Migration class used to import Default Data Dashboards from fixtures.
 */
class DefaultContentDataDashboardsImport extends DkanDataDashboardsImport {

  /**
   * __construct().
   */
  public function __construct($arguments) {

    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_default_content') . '/data/';
      $arguments['list_url'] = 'data_dashboard_list.json';
      $arguments['item_url'] = 'data_dashboard_show?id=:id.json';
    }

    parent::__construct($arguments);
  }
}