<?php

/**
 * Implementation of hook_views_data().
 */
function multistep_views_data() {
  $data['multistep']['table']['group'] = t('Multistep');
  // This table references the {node} table.
  $data['multistep']['table']['join'] = array(
    'node' => array(
      'left_field' => 'nid',
      'field' => 'nid',
      'extra' => "multistep.status = 'unsubmitted'",
    ),
  );
  // Status of a multistep form.
  $data['multistep']['status'] = array(
    'title' => t('Status'),
    'help' => t('Status of the current step in the referenced node.'),
    'field' => array(
      'handler' => 'multistep_views_handler_field_status',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'multistep_views_handler_filter_status',
    ),
    'sort' => array(
      'handler' => 'multistep_views_handler_sort_status',
    ),
  );
  return $data;
}

/**
 * Implementation of hook_views_handlers() to register all of the basic handlers
 * views uses.
 */
function multistep_views_handlers() {
  return array(
    'info' => array(
      'path' => drupal_get_path('module', 'multistep') . '/includes',
    ),
    'handlers' => array(
      // field handlers
      'multistep_views_handler_field_status' => array(
        'parent' => 'views_handler_field',
      ),
      // sort handlers
      'multistep_views_handler_sort_status' => array(
        'parent' => 'views_handler_sort',
      ),
      // filter handlers
      'multistep_views_handler_filter_status' => array(
        'parent' => 'views_handler_filter',
      ),
    ),
  );
}
