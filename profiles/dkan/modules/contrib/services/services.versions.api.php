<?php
/**
 * @file
 * Explains how to use versions
 */

/*
 * All functions that want to be considered for updates need to use a specific naming convetion
 * WE took the same approach as the standard Drupal hook_instal and .install methods.
 * For clients that want to request a specific version they need to pass a certain header
 * services_RESOURCE_METHOD_version = version
 * as an example, services_system_set_variable_version = 1.2
 * Services by default will always use the originl resource shipped
 * with services. If you wish to change this you can goto the resource page,
 * and select an api version for the specific resource. The version option will
 * only be enabled if version changes exists
 *
 */



function _system_resource_set_variable_update_1_1() {
  $new_set = array(
    'help' => 'Create a node with an nid test',
  );
  return $new_set;
}
function _system_resource_set_variable_update_1_2() {
  $new_set = array(
    'help' => 'Create a node with an nid optional prams.',
    'args' => array(
      array(
        'name' => 'name',
        'optional' => TRUE,
        'source' => array('data' => 'name'),
        'description' => t('The name of the variable to set.'),
        'type' => 'string',
      ),
      array(
        'name' => 'value',
        'optional' => TRUE,
        'source' => array('data' => 'value'),
        'description' => t('The value to set.'),
        'type' => 'string',
      ),
    ),
  );
  return $new_set;
}
