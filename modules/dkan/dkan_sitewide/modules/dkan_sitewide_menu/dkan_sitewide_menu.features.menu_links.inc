<?php
/**
 * @file
 * dkan_sitewide_menu.features.menu_links.inc
 */

/**
 * Implements hook_menu_default_menu_links().
 */
function dkan_sitewide_menu_menu_default_menu_links() {
  $menu_links = array();

  // Exported menu link: main-menu:node/add/dataset
  $menu_links['main-menu:node/add/dataset'] = array(
    'menu_name' => 'main-menu',
    'link_path' => 'node/add/dataset',
    'router_path' => '',
    'link_title' => 'Add Dataset',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
    ),
    'module' => 'menu',
    'hidden' => '0',
    'external' => '1',
    'has_children' => '0',
    'expanded' => '0',
    'weight' => '-7',
  );
  // Exported menu link: main-menu:dataset
  $menu_links['main-menu:dataset'] = array(
    'menu_name' => 'main-menu',
    'link_path' => 'dataset',
    'router_path' => '',
    'link_title' => 'Datasets',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
    ),
    'module' => 'menu',
    'hidden' => '0',
    'external' => '0',
    'has_children' => '0',
    'expanded' => '0',
    'weight' => '-6',
  );
  // Exported menu link: main-menu:about
  $menu_links['main-menu:about'] = array(
    'menu_name' => 'main-menu',
    'link_path' => 'about',
    'router_path' => '',
    'link_title' => 'About',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
    ),
    'module' => 'menu',
    'hidden' => '0',
    'external' => '1',
    'has_children' => '0',
    'expanded' => '0',
    'weight' => '-5',
  );
  // Exported menu link: main-menu:groups
  $menu_links['main-menu:groups'] = array(
    'menu_name' => 'main-menu',
    'link_path' => 'groups',
    'router_path' => 'groups',
    'link_title' => 'Groups',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
    ),
    'module' => 'menu',
    'hidden' => '0',
    'external' => '0',
    'has_children' => '0',
    'expanded' => '0',
    'weight' => '-4',
  );
  // Translatables
  // Included for use with string extractors like potx.
  t('Add dataset');
  t('Datasets');
  t('About');
  t('Groups');


  return $menu_links;
}
