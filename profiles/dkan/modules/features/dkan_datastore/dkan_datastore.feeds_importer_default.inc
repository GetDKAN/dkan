<?php
/**
 * @file
 * dkan_datastore.feeds_importer_default.inc
 */

/**
 * Implements hook_feeds_importer_default().
 */
function dkan_datastore_feeds_importer_default() {
  $export = array();

  $feeds_importer = new stdClass();
  $feeds_importer->disabled = FALSE; /* Edit this to true to make a default feeds_importer disabled initially */
  $feeds_importer->api_version = 1;
  $feeds_importer->id = 'dkan_field_importer';
  $feeds_importer->config = array(
    'name' => 'DKAN Field Importer',
    'description' => '',
    'fetcher' => array(
      'plugin_key' => 'EntityFieldFetcher',
      'config' => array(
        'file_field' => 'field_upload',
      ),
    ),
    'parser' => array(
      'plugin_key' => 'FeedsCSVParser',
      'config' => array(
        'delimiter' => ',',
        'no_headers' => 0,
      ),
    ),
    'processor' => array(
      'plugin_key' => 'FieldDataProcessor',
      'config' => array(
        'update_existing' => 0,
        'expire' => -1,
        'mappings' => array(),
        'delete_with_source' => FALSE,
      ),
    ),
    'content_type' => 'resource',
    'update' => 0,
    'import_period' => '-1',
    'expire_period' => 3600,
    'import_on_create' => 0,
    'process_in_background' => 0,
  );
  $export['dkan_field_importer'] = $feeds_importer;

  return $export;
}
