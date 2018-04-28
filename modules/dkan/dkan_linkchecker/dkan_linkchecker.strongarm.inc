<?php

/**
 * @file
 * dkan_linkchecker.strongarm.inc
 */

/**
 * Implements hook_strongarm().
 */
function dkan_linkchecker_strongarm() {
  $export = array();

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'linkchecker_check_links_types';
  $strongarm->value = '0';
  $export['linkchecker_check_links_types'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'linkchecker_scan_node_dataset';
  $strongarm->value = 1;
  $export['linkchecker_scan_node_dataset'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'linkchecker_scan_node_harvest_source';
  $strongarm->value = 1;
  $export['linkchecker_scan_node_harvest_source'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'linkchecker_scan_node_resource';
  $strongarm->value = 1;
  $export['linkchecker_scan_node_resource'] = $strongarm;

  return $export;
}
