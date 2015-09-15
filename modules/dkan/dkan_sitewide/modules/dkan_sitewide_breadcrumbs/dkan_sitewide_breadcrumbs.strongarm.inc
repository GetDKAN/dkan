<?php
/**
 * @file
 * dkan_sitewide_breadcrumbs.strongarm.inc
 */

/**
 * Implements hook_strongarm().
 */
function dkan_sitewide_breadcrumbs_strongarm() {
  $export = array();

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'page_manager_node_edit_disabled';
  $strongarm->value = FALSE;
  $export['page_manager_node_edit_disabled'] = $strongarm;

  return $export;
}
