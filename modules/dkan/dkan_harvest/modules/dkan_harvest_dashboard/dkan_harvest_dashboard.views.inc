<?php

/**
 * @file
 * Views hooks for dkan_harvest_dashboard.
 */

/**
 * Implements hook_views_data().
 */
function dkan_harvest_dashboard_views_data() {
  $data = array();

  $data['dkan_harvest_dashboard']['table']['group'] = t('Dkan Harvest');

  $data['dkan_harvest_dashboard']['table']['join'] = array(
    // #global is a special flag which let's a table appear all the time.
    '#global' => array(),
  );

  $data['dkan_harvest_dashboard']['dkan_harvest_harvest_date'] = array(
    'title' => t('Harvest Migrate Last Updated'),
    'help' => t('Date of the Last Harvest Migrate operation for a Harvest Source.'),
    'field' => array(
      'handler' => 'views_handler_field_date_harvest_date',
      'click sortable' => TRUE,
    ),

    // XXX Not sure if we need this if we are using the "click sortable" in the
    // field above.
    'sort' => array(
      'handler' => 'views_handler_sort_date',
    ),
  );

  $data['dkan_harvest_dashboard']['dkan_harvest_harvest_count'] = array(
    'title' => t('Harvest Migrate Datasets Count'),
    'help' => t('Count of the Dataset Harvested for a Harvest Source'),
    'field' => array(
      'handler' => 'views_handler_field_numeric_harvest_count',
      'click sortable' => TRUE,
    ),
  );

  $data['dkan_harvest_dashboard']['dkan_harvest_harvest_status'] = array(
    'title' => t('Harvest Migrate Status'),
    'help' => t('Status of the last harvest migration'),
    'field' => array(
      'handler' => 'views_handler_field_boolean_harvest_status',
      'click sortable' => TRUE,
    ),
  );

  return $data;
}
