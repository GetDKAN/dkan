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

  // Delete ghost datastore tables.
  $items['dkan-datastore-delete-ghost-tables'] = array(
    'aliases' => array('dkan-datastore-dgt'),
    'description' => 'Delete ghost datastore tables if they exist.',
    'callback' => 'dkan_datastore_drush_delete_ghost_tables',
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

/**
 * Delete ghost datastore tables.
 */
function dkan_datastore_drush_delete_ghost_tables() {
  $query = db_query('SHOW TABLES');
  $result = $query->fetchAll();
  $nids = [];
  $ghosts = 0;
  foreach ($result as $record) {
    $t = (array)$record;
    $db = key($t);
    $table = $record->{$db};
    if (strpos($table, 'datastore_dkan') !== false) {
      // Removes 'feeds_datastore_dkan_file_';
      $nids[] = substr($table, 26);
    }
  }
  if (!empty($nids)) {
    drush_print("Looking through " . implode(', ', $nids));
    $to_truncate = [];
    foreach ($nids as $nid) {
      $query = db_query('SELECT nid FROM {node} WHERE nid = :nid', array(':nid' => $nid));
      $result = $query->fetchAll();
      if (count($result) < 1) {
        $to_truncate[] = 'feeds_datastore_dkan_file_' . $nid;
        $ghosts++;
      }
    }
    drush_print("Ghost tables found: $ghosts");
    foreach ($to_truncate as $table) {
      $query = db_drop_table($table);
      drush_print("Deleted ghost table $table");
    }
  } else {
    drush_print("There is currently no datastore tables.");
  }
}
