<?php
/**
 * @file
 * dkan_sitewide_menu.features.content_menu_links.inc
 */

/**
 * Implements hook_content_menu_links_defaults().
 */
function dkan_sitewide_menu_content_menu_links_defaults() {
  $menu_links = array();

  // Exported menu link: main-menu:node/1
  $menu_links['main-menu:node/1'] = array(
    'menu_name' => 'main-menu',
    'link_path' => 'node-name/about',
    'router_path' => 'node/%',
    'link_title' => 'About',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -47,
    'customized' => 0,
  );
  // Translatables
  // Included for use with string extractors like potx.
  t('About');


  return $menu_links;
}
