<?php

/**
 * @file
 * Documentation of hooks.
 */

/**
 * Invoked after a data record has been inserted.
 */
function hook_data_insert($record, $table_name) {
}

/**
 * Invoked after a data record has been updated.
 */
function hook_data_update($record, $table_name) {
}

/**
 * Invoked before data record(s) have been deleted.
 */
function hook_data_table_delete_rows($table_handler, $clause) {
}

/**
 * Expose default tables.
 *
 * Note:
 *
 * - Implementor is responsible for creating this table on installation and for
 *   proper updates in case of schema changes (hook_install(), hook_update_N())
 * - Implement hook_ctools_plugin_api() to make this hook discoverable by
 *   CTools - see below.
 */
function hook_data_default() {
  $export = array();
  $data_table = new stdClass;
  $data_table->disabled = FALSE; /* Edit this to true to make a default data_table disabled initially */
  $data_table->api_version = 1;
  $data_table->title = 'Example';
  $data_table->name = 'data_table_example';
  $data_table->table_schema = array(
    'fields' => array(
      'id' => array(
        'type' => 'int',
        'size' => 'normal',
        'unsigned' => TRUE,
      ),
    ),
    'primary key' => array(
      '0' => 'id',
    ),
  );
  $data_table->meta = array(
    'fields' => array(
      'id' => array(
        'label' => 'Identifier',
      ),
    ),
  );

  $export['data_table_example'] = $data_table;
  return $export;
}

/**
 * Example for a CTools Plugin API implementation for hook_data_default().
 */
function hook_ctools_plugin_api() {
  $args = func_get_args();
  $module = array_shift($args);
  $api = array_shift($args);
  if ($module == "data" && $api == "data_default") {
    return array("version" => 1);
  }
}

/**
 * Declare additional Views handlers to the views data configuration options.
 *
 * Handlers that are not used by anything declared in hook_views_data() should
 * use this hook to add themselves to the options on the 'Configure views' page.
 */
function data_data_views_handlers_alter(&$handlers) {
  $handlers['field']['views_handler_field_data_markup'] = 'views_handler_field_data_markup';
}

/**
 * Declare additional field types for use with data module.
 *
 * @see data_get_field_definitions()
 */
function hook_data_field_definitions_alter(&$data_types) {
  $data_types['timestamp'] = array(
    'type' => 'int',
    'unsigned' => TRUE,
    'not null' => FALSE,
  );
}
