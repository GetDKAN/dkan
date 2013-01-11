<?php
/**
 * @file
 * dkan_sitewide.strongarm.inc
 */

/**
 * Implements hook_strongarm().
 */
function dkan_sitewide_strongarm() {
  $export = array();

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'pathauto_node_pattern';
  $strongarm->value = '[node:title]';
  $export['pathauto_node_pattern'] = $strongarm;

  return $export;
}
