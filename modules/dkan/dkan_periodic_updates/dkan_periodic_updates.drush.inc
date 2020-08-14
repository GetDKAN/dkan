<?php

/**
 * @file
 * Code for dkan_periodic_updates drush commands.
 */

/**
 * Implements hook_drush_command().
 */
function dkan_periodic_updates_drush_command() {
  $commands['dkan-force-resources-update'] = array(
    'description' => 'Force resources from manifest to be updated.',
    'aliases' => array('dfru'),
  );

  return $commands;
}

/**
 * Drush command logic.
 */
function drush_dkan_periodic_updates_dkan_force_resources_update() {
  $active = variable_get('dkan_periodic_updates_status');
  if ($active) {
    $items = [];
    $file = variable_get('dkan_periodic_updates_manifest');
    $file = file_load($file);
    // Get items from manifest.
    if ($file) {
      $handle = fopen($file->uri, 'r');
      $headers = fgetcsv($handle, 0, ',');

      while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
        $resource_data = array_combine($headers, $data);
        $items[$resource_data['resource_id']]['file_url'] = $resource_data['file_url'];
        $datastore = $resource_data['import_to_datastore'] === 'Y' ? TRUE : FALSE;
        $items[$resource_data['resource_id']]['datastore'] = $datastore;
      }
      fclose($handle);
    }
    else {
      watchdog('dkan_periodic_updates', 'No manifest found.');
      return [];
    }

    if (!empty($items)) {
      dkan_periodic_updates_execute_update($items);
    }
  }
}
