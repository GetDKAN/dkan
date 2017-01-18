<?php

/**
 * @file
 * dkan_sitewide_menu.features.menu_custom.inc
 */

/**
 * Implements hook_menu_default_menu_custom().
 */
function dkan_sitewide_menu_menu_default_menu_custom() {
  $menus = array();

  // Exported menu: menu-command-center-menu.
  $menus['menu-command-center-menu'] = array(
    'menu_name' => 'menu-command-center-menu',
    'title' => 'Command Center Menu',
    'description' => 'Alternative admin menu for non admin users.',
  );
  // Translatables
  // Included for use with string extractors like potx.
  t('Alternative admin menu for non admin users.');
  t('Command Center Menu');

  return $menus;
}
