<?php
/**
 * @file
 * Views hooks.
 */

/**
 * Implements hook_views_data().
 */
function data_node_views_data() {
  $data = array();

  $data['data_table_node']['table']['group']  = t('Data node');
  $data['data_table_node']['table']['title']  = t('Node');
  $data['data_table_node']['table']['join'] = array(
    'node' => array(
      // Describe how data_table_node joins to node table.
      'left_field' => 'nid',
      'field' => 'nid',
    ),
  );
  // Add an explicit relationship to the node table.
  $data['data_table_node']['nid']['relationship'] = array(
    'help' => t('Relate data_table_node to node table.'),
    'label' => t('Relate data_table_node to node table'),
    'base' => 'node',
    'base field' => 'nid',
  );
  $data['data_table_node']['item_count'] = array(
    'title' => t('Item count'),
    'help' => t('A count of the number of data items related to this node.'),
    'field' => array(
      'handler' => 'data_node_views_handler_field_item_count',
    ),
  );

  // Add an add/remove handler for each data table that supports adding/removing.
  $tables = data_get_all_tables();
  foreach ($tables as $table) {
    $meta = $table->get('meta');

    if (!empty($meta['data_node']['content_type'])) {

      // Add an explicit relationship to data_table_node.
      $data[$table->get('name')]['data_table_node_rel'] = array(
        'group' => t('Data node'),
        'title' => "Data table",
        'relationship' => array(
          'help' => t('Relate this data table to data_table_node.'),
          'label' => t('Relate this data table to data_table_node'),
          'base' => 'data_table_node',
          'base field' => 'id',
          'relationship field' => $meta['data_node']['id'],
        ),
      );
      $data[$table->get('name')]['data_node_add_remove'] = array(
        'field' => array(
          'title' => t('Add to/remove from node link'),
          'help' => t('Provide a link to add or remove a data item to a node.'),
          'handler' => 'data_node_views_handler_field_add_remove',
        ),
      );
      $data[$table->get('name')]['data_node_list'] = array(
        'field' => array(
          'title' => t('List of nodes'),
          'help' => t('Provide a list of nodes this item is related to.'),
          'handler' => 'data_node_views_handler_field_node_list',
        ),
      );
    }
  }

  return $data;
}

/**
 * Implements hook_views_handlers().
 */
function data_node_views_handlers() {
  return array(
    'info' => array(
      'path' => drupal_get_path('module', 'data_node') . '/views',
    ),
    'handlers' => array(
      // field handlers
      'data_node_views_handler_field_item_count' => array(
        'parent' => 'views_handler_field',
      ),
      'data_node_views_handler_field_add_remove' => array(
        'parent' => 'views_handler_field',
      ),
      'data_node_views_handler_field_node_list' => array(
        'parent' => 'views_handler_field',
      ),
    ),
  );
}
