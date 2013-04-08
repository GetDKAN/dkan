<?php

/**
 * @file
 * Drush implementation for the uuid module.
 */

/**
 * Implementats hook_drush_command().
 */
function uuid_drush_command() {
  $items = array();
  $items['uuid-create-missing'] = array(
    'description' => 'Create missing UUIDs for enabled entities.',
    'aliases' => array('uuid-create'),
  );
  return $items;
}

/**
 * Implementats of hook_drush_help().
 */
function uuid_drush_help($section) {
  switch ($section) {
    case 'drush:uuid-create-missing':
      return dt("This command will create missing UUIDs for those content types specified in the module settings for automatic generation.");
  }
}

/**
 * Drush command callback.
 */
function drush_uuid_create_missing() {
  if (!drush_confirm(dt('Are you sure?'))) {
    return drush_user_abort();
  }
  module_load_include('inc', 'uuid', 'uuid');

  drush_log(dt('Beginning bulk creation of UUIDs.'), 'ok');
  uuid_sync_all();
}
