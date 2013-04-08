<?php
/**
 * @file
 * uuid_services_example.services.inc
 */

/**
 * Implements hook_default_services_endpoint().
 */
function uuid_services_example_default_services_endpoint() {
  $export = array();

  $endpoint = new stdClass;
  $endpoint->disabled = FALSE; /* Edit this to true to make a default endpoint disabled initially */
  $endpoint->api_version = 3;
  $endpoint->name = 'uuid_services_example';
  $endpoint->server = 'rest_server';
  $endpoint->path = 'api';
  $endpoint->authentication = array(
    'services' => 'services',
  );
  $endpoint->server_settings = array(
    'formatters' => array(
      'json' => TRUE,
      'bencode' => FALSE,
      'jsonp' => FALSE,
      'php' => FALSE,
      'rss' => FALSE,
      'xml' => FALSE,
      'yaml' => FALSE,
    ),
    'parsers' => array(
      'application/json' => TRUE,
      'application/x-www-form-urlencoded' => TRUE,
      'multipart/form-data' => TRUE,
      'application/vnd.php.serialized' => FALSE,
      'application/x-yaml' => FALSE,
    ),
  );
  $endpoint->resources = array(
    'comment' => array(
      'operations' => array(
        'update' => array(
          'enabled' => 1,
        ),
      ),
    ),
    'file' => array(
      'operations' => array(
        'update' => array(
          'enabled' => 1,
        ),
      ),
    ),
    'node' => array(
      'operations' => array(
        'update' => array(
          'enabled' => 1,
        ),
      ),
    ),
    'taxonomy_term' => array(
      'operations' => array(
        'update' => array(
          'enabled' => 1,
        ),
      ),
    ),
    'user' => array(
      'operations' => array(
        'update' => array(
          'enabled' => 1,
        ),
      ),
      'actions' => array(
        'login' => array(
          'enabled' => 1,
        ),
        'logout' => array(
          'enabled' => 1,
        ),
      ),
    ),
  );
  $endpoint->debug = 1;
  $export['uuid_services_example'] = $endpoint;

  return $export;
}
