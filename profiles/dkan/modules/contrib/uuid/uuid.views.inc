<?php
/**
 * @file
 * Views Implementation for UUID
 */

/**
 * Implements hook_views_data_alter().
 */
function uuid_views_data_alter(&$data) {
  foreach (entity_get_info() as $info) {
    if (isset($info['uuid']) && $info['uuid'] == TRUE && !empty($info['entity keys']['uuid'])) {
      $table = $info['base table'];
      $field = $info['entity keys']['uuid'];
      $data[$table][$field] = array(
        'title' => t('@type UUID', array('@type' => $info['label'])),
        'help' => t('The universally unique ID of the @type.', array('@type' => $info['label'])),
        'field' => array(
          'handler' => 'views_handler_field',
          'click sortable' => TRUE,
        ),
        'filter' => array(
          'handler' => 'views_handler_filter_string',
          'allow empty' => TRUE,
        ),
        'argument' => array(
          'handler' => 'views_handler_argument_string',
        ),
        'sort' => array(
          'handler' => 'views_handler_sort',
        ),
      );
    }
  }
}
