<?php
/**
 * @file
 * Views hooks.
 */

/**
 * Implements hook_views_data().
 */
function data_taxonomy_views_data() {
  $data = array();

  $data['data_taxonomy']['table']['group']  = t('Data taxonomy');
  $data['data_taxonomy']['table']['title']  = t('Taxonomy');
  $data['data_taxonomy']['table']['join'] = array(
    'taxonomy_term_data' => array(
      'left_field' => 'tid',
      'field' => 'tid',
    ),
    'node' => array(
      'left_table' => 'taxonomy_term_node',
      'left_field' => 'tid',
      'field' => 'tid',
    ),
  );
  $data['data_taxonomy']['tid']['relationship'] = array(
    'title' => t('Term data.'),
    'help' => t('Relate data_taxonomy to a term data.'),
    'label' => t('Relate data_taxonomy to a term data.'),
    'base' => 'taxonomy_term_data',
    'base field' => 'tid',
  );
  $data['data_taxonomy']['tid_2'] = array(
    'relationship' => array(
      'handler' => 'views_handler_relationship',
      'title' => t('Nodes with term.'),
      'help' => t('Relate data taxonomy to all nodes with same term.'),
      'label' => t('Relate data taxonomy to nodes with term.'),
      'base' => 'taxonomy_term_node',
      'base field' => 'tid',
      'relationship field' => 'tid',
    ),
  );
  $data['data_taxonomy']['item_count'] = array(
    'title' => t('Item count'),
    'help' => t('A count of the number of data items related to this tid or nid.'),
    'field' => array(
      'handler' => 'data_taxonomy_views_handler_field_item_count',
    ),
  );

  $tables = data_get_all_tables();
  foreach ($tables as $table) {
    $meta = $table->get('meta');

    if (!empty($meta['data_taxonomy']['vocabularies'])) {
      $data['data_taxonomy']['table']['join'][$table->get('name')] = array(
        'left_field' => $meta['data_taxonomy']['id'],
        'field' => 'id',
      );
      // allow term data to join directly to affected data tables
      $data['taxonomy_term_data']['table']['join'][$table->get('name')] = array(
        'left_table' => 'data_taxonomy',
        'left_field' => 'tid',
        'field' => 'tid',
      );
      // don't want to overwrite any other relationships this might have...
      $data[$table->get('name')]['data_taxonomy_' . $meta['data_taxonomy']['id']]['relationship'] = array(
        'title' => t('Term tid.'),
        'help' => t('Relate a data record to a term tid.'),
        'label' => t('Relate a data record to a term tid.'),
        'base' => 'data_taxonomy',
        'base field' => 'id',
        'relationship field' => $meta['data_taxonomy']['id'],
      );
      $data[$table->get('name')]['data_taxonomy_form'] = array(
        'field' => array(
          'title' => t('Taxonomy form'),
          'help' => t('A taxonomy form for relating table records to taxonomy terms.'),
          'handler' => 'data_taxonomy_views_handler_field_form',
        ),
      );
    }
  }
  return $data;
}

/**
 * Implements hook_views_data_alter().
 *
 * Expose node table fields/filters for terms as base table.
 * @todo This really doesn't belong here.
 */
function data_taxonomy_views_data_alter(&$data) {
  $data['node']['table']['join']['taxonomy_term_node'] = array(
    'left_table' => 'taxonomy_term_node',
    'left_field' => 'nid',
    'field' => 'nid',
  );
}

/**
 * Implements hook_views_handlers().
 */
function data_taxonomy_views_handlers() {
  return array(
    'info' => array(
      'path' => drupal_get_path('module', 'data_taxonomy') . '/views',
    ),
    'handlers' => array(
      'data_taxonomy_views_handler_field_form' => array(
        'parent' => 'views_handler_field',
      ),
      'data_taxonomy_views_handler_field_item_count' => array(
        'parent' => 'views_handler_field',
      ),
    ),
  );
}
