<?php

/**
 * @file
 * Callbacks for datastore pages.
 */

/**
 * Copy of feeds_access.
 */
function dkan_datastore_feeds_access($action, $param) {
  if (!in_array($action, array('import', 'clear', 'unlock'))) {
    // If $action is not one of the supported actions, we return access denied.
    return FALSE;
  }

  if (is_string($param)) {
    $importer_id = $param;
  }
  elseif ($param->type) {
    $importer_id = feeds_get_importer_id($param->type);
  }

  // Check for permissions if feed id is present, otherwise return FALSE.
  if ($importer_id) {
    if (user_access('administer feeds') || user_access("{$action} {$importer_id} feeds")) {
      return TRUE;
    }
  }
  return FALSE;
}
/**
 * Access callback for Data API instructions.
 */
function dkan_datastore_datastore_api_access($node) {
  if ($node->type != 'resource' || !isset($node->field_upload[$node->language][0])) {
    return FALSE;
  }
  else {
    return node_access('view', $node);
  }
}

/**
 * Access callback for download tab.
 */
function dkan_datastore_download_access($node) {
  if ($node->type != 'resource' || !isset($node->field_upload[$node->language][0])) {
    return FALSE;
  }
  else {
    return node_access('view', $node);
  }
}

/**
 * Access callback for back link.
 */
function dkan_datastore_back_access($node) {
  if ($node->type != 'resource') {
    return FALSE;
  }
  else {
    return node_access('view', $node);
  }
}

/**
 * Access callback for 'Add Resource' tab.
 */
function dkan_add_resource($node) {
  if ($node->type != 'dataset') {
    return FALSE;
  }
  else {
    return _node_add_access();
  }
}

