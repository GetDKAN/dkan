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

  // Exported menu: main-menu.
  $menus['main-menu'] = array(
    'menu_name' => 'main-menu',
    'title' => 'Main menu',
    'description' => 'The <em>Main</em> menu is used on many sites to show the major sections of the site, often in a top navigation bar.',
  );
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
  t('Main menu');
  t('The <em>Main</em> menu is used on many sites to show the major sections of the site, often in a top navigation bar.');


  return $menus;
}
