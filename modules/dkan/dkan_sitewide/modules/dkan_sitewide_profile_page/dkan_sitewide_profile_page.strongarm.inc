<?php
/**
 * @file
 * dkan_sitewide_profile_page.strongarm.inc
 */

/**
 * Implements hook_strongarm().
 */
function dkan_sitewide_profile_page_strongarm() {
  $export = array();

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'admin_menu_source_settings';
  $strongarm->value = array(
    1 => array(
      'source' => '',
    ),
    2 => array(
      'source' => '',
    ),
    4 => array(
      'source' => 'menu-command-center-menu',
    ),
    3 => array(
      'source' => '',
    ),
    132006037 => array(
      'source' => '',
    ),
  );
  $export['admin_menu_source_settings'] = $strongarm;

  return $export;
}
