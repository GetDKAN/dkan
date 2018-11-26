<?php

/**
 * @file
 * Dkan_datastore.drush.inc.
 */

use Dkan\Datastore\LockableDrupalVariables;

/**
 * Implements hook_drush_command().
 */
function dkan_datastore_drush_command() {
  // Run harvest operation on the source passed as argument.
  $items['dkan-datastore-delete-config'] = array(
    'description' => 'Remove datastore store configuration for a resource. (Only use if configuration have been orphaned - The configuration exists but the resource does not). For any other scenario use the datasotre UI.',
    'callback' => 'dkan_datastore_drush_delete_config',
    'arguments' => array(
      'resource_nid' => "Resource NID"
    ),
  );

  return $items;
}

function dkan_datastore_drush_delete_config($resource_id) {
  $node = node_load($resource_id);
  if ($node === FALSE) {
    $state_storage = new LockableDrupalVariables("dkan_datastore");
    $state_storage->delete($resource_id);
  }
  else {
    print_r("Can not delete the configuration for an existing node.");
  }
}