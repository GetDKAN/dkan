<?php
/**
 * @file
 *
 * Is an alter plugin for defaultcontent
 *
 * Handles the main processing for importing nodes
 */

$plugin = array(
  'export_alter weight' => 1000,
);

/**
 * Handles the main transfer of data from node to export
 *
 * We did at first try to move all things that were in node
 * but some times this include node and so we had recusion issue when exporting
 * also feature were overridden way to often
 */
function node_export_alter(&$node, &$export) {
  $keys = array(
    'title',
    'status',
    'promote',
    'sticky',
    'type',
    'language',
    'created',
    'comment',
    'translate',
    'machine_name',
    'body',
    'sort_value',
  );
  // grab core properties
  foreach ($keys as $key) {
    if (isset($node->{$key})) {
      $export->{$key} = $node->{$key};
    }
  }
  //grab all fields that are not empty
  foreach (get_object_vars($node) as $key => $value) {
    if (preg_match("/^field_/", $key) && !empty($value)) {
      $export->{$key} = $node->{$key};
    }
  }
}

/**
 * Handles the importing of nodes
 *
 * TODO: use the current user id if we have one
 */
function node_import_alter(&$node) {
  $node->uid = 1;
}

/**
 * 
 * Sorts node
 * @param node $a
 * @param node $b
 */
function node_import_sort($a, $b){
  if (isset($a->sort_value) && isset($b->sort_value)) {
    return $a->sort_value < $b->sort_value;
  }  
  return isset($a->sort_value) ? -1: 1;
}

