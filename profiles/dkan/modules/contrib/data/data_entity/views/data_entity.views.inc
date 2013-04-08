<?php
/**
 * @file
 * Contains Views hooks.
 */

/**
 * Implements hook_views_data_alter().
 */
function data_entity_views_data_alter(&$data) {
  $tables = data_entity_get_entity_tables();
  foreach (array_keys($tables) as $table) {
    $data[$table]['edit_link'] = array(
      'field' => array(
        'title' => t('Edit link'),
        'help' => t('Displays an edit link to the data item'),
        'handler' => 'data_entity_views_handler_field_edit_link',
      ),
    );
  }
}

/**
 * Implements hook_views_default_views_alter().
 *
 * Add our field to the default data table views.
 */
function data_entity_views_default_views_alter(&$views) {
  $tables = data_entity_get_entity_tables();

  foreach ($tables as $table) {
    $view_name = $table_name = $table->get('name');
    // Not all tables have views, eg if they have no primary key.
    if (isset($views[$view_name])) {
      $view = $views[$view_name];

      $handler =& $view->display['default']->handler;
      /* Field: User: Name */
      $handler->display->display_options['fields']['edit_link']['id'] = 'edit_link';
      $handler->display->display_options['fields']['edit_link']['table'] = $table_name;
      $handler->display->display_options['fields']['edit_link']['field'] = 'edit_link';
    }
  }
}

