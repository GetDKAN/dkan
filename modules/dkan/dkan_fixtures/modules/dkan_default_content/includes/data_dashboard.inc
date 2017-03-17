<?php

/**
 * @file
 * Migration class used to import Default Data Dashboards from fixtures.
 */

/**
 * Default content extention of Data Dashboards fixtures migration class.
 */
class DefaultContentDataDashboardsImport extends DkanDataDashboardsImport {

  /**
   * {@inheritdoc}
   */
  public function __construct($arguments) {

    if (!isset($arguments['endpoint'])) {
      $arguments['endpoint'] = drupal_get_path('module', 'dkan_default_content') . '/data/';
      $arguments['list_url'] = 'data_dashboard_list.json';
      $arguments['item_url'] = 'data_dashboard_show%3Fid=:id.json';
    }

    parent::__construct($arguments);
  }

}
