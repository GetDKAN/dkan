<?php

/**
 * @file
 * Shows example of CRUD for server testing.
 */

include 'dkan_datastore_api_crud.php';

print_r('DKAN Datastore CRUD example: INIT.'); echo '<br/>';
// Set parameters.
$base_url = 'http://nucivic-dkan';
$endpoint = '/api/action/datastore';
$login_url = $base_url . $endpoint . '/user/login';

$file_path_create = dirname(__FILE__) . '/files/example_file_for_create.csv';
$file_path_update = dirname(__FILE__) . '/files/example_file_for_update.csv';

/*
 * Getting cookie_session and CSRF token.
 */

print_r('- Getting cookie_session and csrf token...'); echo '<br/>';

$user_login = dkan_datastore_services_user_login('admin', 'admin', $login_url);
$cookie_session = $user_login['cookie_session'];
$csrf_token = dkan_datastore_services_get_csrf($cookie_session, $user_login['curl'], $base_url);

print_r('- Done.'); echo '<br/>';

/*
 * Creation of datastore from file.
 */

print_r('- Creation of Datastore from file...'); echo '<br/>';

// Datastore data.
$entity = 'store';
$node_data = array(
  'filesize' => filesize($file_path_create),
  'filename' => basename($file_path_create),
  'file' => base64_encode(file_get_contents($file_path_create)),
  'uid'  => 1,
);
$datastore = dkan_datastore_services_datastore_create($node_data, $csrf_token, $cookie_session, $base_url, $endpoint, $entity);
var_dump($datastore);

print_r('- Datastore created!.'); echo '<br/>';

/*
 * Creation of datastore from file.
 */

print_r('- Update of Datastore from file...'); echo '<br/>';

$entity = 'store';
$node_data = array(
  'filesize' => filesize($file_path_update),
  'filename' => basename($file_path_update),
  'file' => base64_encode(file_get_contents($file_path_update)),
  'uid'  => 1,
);
$datastore = dkan_datastore_services_datastore_update($node_data, $datastore->datastore_id, $csrf_token, $cookie_session, $base_url, $endpoint, $entity);

var_dump($datastore);

print_r('- Datastore updated!.'); echo '<br/>';

/*
 * Delete file from datastore.
 */

print_r('- Delete datastore file...'); echo '<br/>';

$entity = 'data';
$datastore = dkan_datastore_services_delete_file($datastore->datastore_id, $csrf_token, $cookie_session, $base_url, $endpoint, $entity);

var_dump($datastore);

print_r('- File deleted!.'); echo '<br/>';

print_r('DKAN Datastore CRUD example: END.');
