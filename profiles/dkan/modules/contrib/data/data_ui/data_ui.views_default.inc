<?php
/**
 * @file
 * Views default views implementation.
 */

/**
 * Implements hook_views_default_views().
 */
function data_ui_views_default_views() {
  $views = array();
  $tables = data_get_all_tables();

  foreach ($tables as $table) {
    // Create new view for each table.
    $view = new view;
    $view->name = $table->get('name');
    $view->description = t('Default view for data table !title', array('!title' => $table->get('title')));
    $view->tag = 'data table';

    // Try to link a table to an existing table. If there are no linked tables,
    // check whether this table is a base table. If not, continue.
    // @todo: allow relationships to tables, 2 or more joins away.
    $schema = $table->get('table_schema');
    $meta = $table->get('meta');
    if (isset($meta['join']) && is_array($meta['join'])) {
      $left_table = key($meta['join']);
      $left_table_schema = drupal_get_schema($left_table);

      if (isset($left_table_schema['primary key']) && count($left_table_schema['primary key'])) {
        $view->base_table = $left_table;
      }
    }
    elseif (isset($schema['primary key']) && count($schema['primary key'])) {
      $view->base_table = $table->get('name');
    }
    else {
      continue;
    }

    // @todo: maybe yes?
    $view->is_cacheable = FALSE;
    $view->api_version = 2;
    $view->disabled = FALSE;
    $handler = $view->new_display('default', 'Default', 'default');

    // Add our columns to the view's fields.
    $fields = array();
    $meta_fields = $meta['fields'];
    foreach ($schema['fields'] as $field_name => $field) {
      $fields[$field_name] = array(
        'label' => isset($meta_fields[$field_name]['label']) ? $meta_fields[$field_name]['label'] : $field_name,
        'id' => $field_name,
        'table' => $table->get('name'),
        'field' => $field_name,
        'exclude' => 0,
        'relationship' => 'none',
      );
    }
    $handler->override_option('fields', $fields);

    // Add a default argument for the first column.
    $first = key($schema['fields']);
    $handler->override_option('arguments', array(
      $first => array(
        'default_action' => 'ignore',
        'style_plugin' => 'default_summary',
        'style_options' => array(),
        'wildcard' => 'all',
        'wildcard_substitution' => 'All',
        // These correspond to user-entered data and so should not be escaped.
        'title' => $table->get('title') . ' %1',
        'id' => $first,
        'table' => $table->get('name'),
        'field' => $first,
      ),
    ));

    $handler->override_option('access', array(
      'type' => 'none',
    ));
    $handler->override_option('title', $table->get('title'));
    $handler->override_option('empty', 'There is no data in this table.');
    $handler->override_option('empty_format', '1');
    $handler->override_option('items_per_page', 50);
    $handler->override_option('use_pager', '1');
    $handler->override_option('style_plugin', 'table');

    // Add $fields into the style options.
    $field_names = array();
    $info = array();
    foreach ($schema['fields'] as $field_name => $field) {
      $field_names[$field_name] = $field_name;
      $info[$field_name] = array(
        'sortable' => 1,
        'separator' => '',
      );
    }
    $handler->override_option('style_options', array(
      'grouping' => '',
      'override' => 1,
      'sticky' => 1,
      'order' => 'asc',
      'columns' => $field_names,
      'info' => $info,
      'default' => -1,
    ));

    // Add a page.
    $handler = $view->new_display('page', 'Page', 'page_1');
    $handler->override_option('path', data_ui_get_default_path($table->get('name')));
    $handler->override_option('menu', array(
      'type' => 'normal',
      'title' => $table->get('title'),
      'description' => '',
      'weight' => '0',
      'name' => 'navigation',
    ));
    $handler->override_option('tab_options', array(
      'type' => 'none',
      'title' => '',
      'description' => '',
      'weight' => 0,
    ));
    $views[$view->name] = $view;
  }

  return $views;
}
