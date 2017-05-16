<?php

/**
 * @file
 * Dkan_datastore_api.drush.inc.
 */

/**
 * Implements hook_drush_command().
 */
function dkan_datastore_api_drush_command() {

  // Drop datastore.
  $items['datastore-drop'] = array(
    'drupal dependencies' => array('dkan_datastore_api'),
    'aliases' => array('dsd'),
    'description' => 'Drops the datastore.',
    'callback' => 'dkan_datastore_api_datastore_drop_command',
    'arguments' => array(
      'id' => 'Datastore (or resource) id.',
    ),
  );

  // Get the number of rows in the datastore.
  $items['datastore-rows'] = array(
    'drupal dependencies' => array('dkan_datastore_api'),
    'aliases' => array('dsr'),
    'description' => 'Get the number of rows in the datastore.',
    'callback' => 'dkan_datastore_api_datastore_rows_command',
    'arguments' => array(
      'id' => 'Datastore (or resource) id.',
    ),
  );

  // Update datastore by file.
  $items['datastore-update'] = array(
    'drupal dependencies' => array('dkan_datastore_api'),
    'aliases' => array('dsu'),
    'description' => 'Updates the datastore with the file.',
    'callback' => 'dkan_datastore_api_datastore_update_command',
    'arguments' => array(
      'id' => 'Datastore (or resource) id.',
      'file_path' => 'The path to the file.',
    ),
  );

  // Create datastore by file.
  $items['datastore-create'] = array(
    'drupal dependencies' => array('dkan_datastore_api'),
    'aliases' => array('dsc'),
    'description' => 'Creates a datastore with the file.',
    'callback' => 'dkan_datastore_api_datastore_create_command',
    'arguments' => array(
      'file' => 'The path to the file.',
    ),
  );

  // Delete datastore file.
  $items['datastore-file-delete'] = array(
    'drupal dependencies' => array('dkan_datastore_api'),
    'aliases' => array('dsfd'),
    'description' => 'Deletes the file on a datastore.',
    'callback' => 'dkan_datastore_api_datastore_file_delete_command',
    'arguments' => array(
      'id' => 'Datastore (or resource) id.',
    ),
  );

  // Show the URI of the datastore file.
  $items['datastore-file-uri'] = array(
    'drupal dependencies' => array('dkan_datastore_api'),
    'aliases' => array('dsfuri'),
    'description' => 'Shows the URI of the datastore file.',
    'callback' => 'dkan_datastore_api_datastore_file_uri_command',
    'arguments' => array(
      'id' => 'Datastore (or resource) id.',
    ),
  );

  // Show the URL of the datastore file.
  $items['datastore-file-url'] = array(
    'drupal dependencies' => array('dkan_datastore_api'),
    'aliases' => array('dsfurl'),
    'description' => 'Shows the URL of the datastore file.',
    'callback' => 'dkan_datastore_api_datastore_file_url_command',
    'arguments' => array(
      'id' => 'Datastore (or resource) id.',
    ),
  );

  return $items;
}

/**
 * Callback for the datastore-drop command.
 */
function dkan_datastore_api_datastore_drop_command($id = NULL) {

  drush_print('The command is not implemented yet.');
}

/**
 * Callback for the datastore-rows command.
 */
function dkan_datastore_api_datastore_rows_command($id = NULL) {

  drush_print('The command is not implemented yet.');
}

/**
 * Callback for the datastore-update command.
 */
function dkan_datastore_api_datastore_update_command($id = NULL, $file_path = NULL) {
  // Sanity check the arguments.
  foreach (array('id', 'file_path') as $argument) {
    if (!$$argument) {
      drush_log(t('%argument is required.', array('%argument' => $argument)),
        'error');
      return FALSE;
    }
  }

  $datastore = get_datastore($id);

  if (!$datastore) {
    $message = t('Failed to load Datastore with id "%id"', array('%id' => $id));
    drush_print($message);
    return FALSE;
  }
  else {
    $message = t('Datastore loaded.');
    drush_print($message);
  }

  if (!$datastore->updateFromFileUri($file_path, TRUE)) {
    drush_print(t('Failed to upload the file.'));
    return FALSE;
  }
  else {
    drush_print(t('File copied and uploaded.'));
  }

  if (!$datastore->importByCli()) {
    $message = t('Failed to import the file to the datastore.');
    drush_print($message);
    return FALSE;
  }
  else {
    $message = t('File imported to the datastore.');
    drush_print($message);
  }
}

/**
 * Callback for the datastore-create command.
 */
function dkan_datastore_api_datastore_create_command($file = NULL) {

  if ($datastore = dkan_datastore_go()) {

    $result = $datastore->createFromFileUri($file->uri, TRUE);

    if ($result) {
      drush_print('The new file has been saved and the items were imported.');
      drush_print('A new datastore was created with ID: ' . $datastore->node->uuid);
    }
    else {
      drush_print('The file could not be loaded.');
    }
  }
}

/**
 * Callback for the datastore-file-delete command.
 */
function dkan_datastore_api_datastore_file_delete_command($id = NULL) {

  if ($datastore = get_datastore($id)) {
    $result = $datastore->deleteFile();

    if ($result) {
      drush_print('The file has been deleted and the items were removed.');
    }
    else {
      drush_print('The file could not be deleted.');
    }
  }
}

/**
 * Callback for the datastore-file-uri command.
 */
function dkan_datastore_api_datastore_file_uri_command($id = NULL) {

  if ($datastore = get_datastore($id)) {

    if ($datastore->file()) {
      drush_print('The URI of the file in the datastore is: ' . $datastore->fileUri());
    }
    else {
      drush_print('The datastore has no file.');
    }

  }

}

/**
 * Callback for the datastore-file-url command.
 */
function dkan_datastore_api_datastore_file_url_command($id = NULL) {

  if ($datastore = get_datastore($id)) {

    if ($datastore->file()) {
      drush_print('The URL of the file in the datastore is: ' . $datastore->fileUrl());
    }
    else {
      drush_print('The datastore has no file.');
    }
  }
}

/**
 * Get datastore based on id.
 */
function get_datastore($id) {

  // Show an error if the resource_id is not present.
  if (!$id) {
    drush_print('No datastore id was provided.');
    return FALSE;
  }

  // Search for a datastore associated with the resource.
  try {
    $datastore = dkan_datastore_go($id);

  }
  catch (Exception $e) {
    drush_print('There are no datastores with the specified id.');
    return FALSE;
  }

  return $datastore;
}
