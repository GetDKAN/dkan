<?php
/**
 * @file
 * dkan_datastore_api.services.inc
 */

/**
 * Implements hook_default_services_endpoint().
 */
function dkan_datastore_api_default_services_endpoint() {
  $export = array();

  $endpoint = new stdClass();
  $endpoint->disabled = FALSE; /* Edit this to true to make a default endpoint disabled initially */
  $endpoint->api_version = 3;
  $endpoint->name = 'dkan_datastore_api';
  $endpoint->server = 'rest_server';
  $endpoint->path = 'api/action/datastore';
  $endpoint->authentication = array(
    'services' => 'services',
  );
  $endpoint->server_settings = array();
  $endpoint->resources = array(
    'dkan_datastore_search' => array(
      'alias' => 'search',
      'operations' => array(
        'index' => array(
          'enabled' => '1',
        ),
      ),
    ),
  );
  $endpoint->debug = 0;
  $export['dkan_datastore_api'] = $endpoint;

  return $export;
}
