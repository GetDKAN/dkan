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
DKAN_DATASORE_TABLE_NAME="  
  
  // Run harvest operation on the source passed as argument.
  $items['dkan-datastore-delete-config'] = array(
    'description' => 'Remove datastore store configuration for a resource. (Only use if configuration have been orphaned - The configuration exists but the resource does not). For any other scenario use the datasotre UI.',
    'callback' => 'dkan_datastore_drush_delete_config',
    'arguments' => array(
      'resource_nid' => "Resource NID"
    ),
  );
  $items['dkan-datastore-prune-tables'] = array(
    'description' => 'Remove datastore tables that are not tied to a node.',
    'callback' => 'drush_dkan_datastore_prune_tables'
  );
  return $items;
}

function drush_dkan_datastore_prune_tables() {
  $query = db_query('SHOW TABLES');
  $result = $query->fetchAll();
  $nids = []; 
  foreach ($result as $record) {
    $t = (array)$record;
    $db = key($t);
    $table = $record->{$db};
    if (strpos($a, 'feeds_datastore_dkan') !== false) {
      // Removes 'feeds_datastore_dkan_file_';
      $nids[] = substr($table, 26);
    }
  }
  drush_print("Looking through $nids");
  $to_truncate=[];
  foreach ($nids as $nid) {
    $query = db_query('SELECT nid FROM {node} WHERE nid = :nid', array(':nid' => $nid));
    $result = $query->fetchAll();
    if (count($result < 1)) {
      var_dump("Delete table feeds_datastore_dkan_file_$nid");
      $to_truncacte[] = $nid;
    }
  }
  foreach ($to_truncate as $table) {
    drush_print("Dropping table: $table");
    $query = db_query("DROP TABLE $table");
    $result = $query->fetchAll();
  }
}
