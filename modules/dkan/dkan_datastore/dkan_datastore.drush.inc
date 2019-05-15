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

  // Drop orphan datastore tables.
  $items['dkan-datastore-drop-orphan-tables'] = array(
    'aliases' => array('dkan-datastore-dot'),
    'description' => 'Drop orphan datastore tables if they exist.',
    'callback' => 'dkan_datastore_drush_drop_orphan_tables',
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
 * Drop orphan datastore tables.
 */
function dkan_datastore_drush_drop_orphan_tables() {
  $query = db_query('SHOW TABLES');
  $result = $query->fetchAll();
  $nids = [];
  $orphans = 0;
  foreach ($result as $record) {
    $t = (array)$record;
    $db = key($t);
    $table = $record->{$db};
    if (strpos($table, 'dkan_datastore') !== false) {
      // Removes 'dkan_datastore_';
      $nids[] = substr($table, 15);
    }
  }
  if (!empty($nids)) {
    drush_print("Checking for the following nodes " . implode(', ', $nids));
    $to_truncate = [];
    foreach ($nids as $nid) {
      $query = db_query('SELECT nid FROM {node} WHERE nid = :nid', array(':nid' => $nid));
      $result = $query->fetchAll();
      if (count($result) < 1) {
        $to_truncate[] = 'dkan_datastore_' . $nid;
        $orphans++;
      }
    }
    drush_print("Orphan tables found: $orphans");
    foreach ($to_truncate as $table) {
      $query = db_drop_table($table);
      drush_print("Deleted orphan table $table");
    }
  } else {
    drush_print("There are currently no datastore tables on this site.");
  }
}
