<?php
/**
 * @file
 * dkan_menu.features.menu_links.inc
 */

/**
 * Implements hook_menu_default_menu_links().
 */
function dkan_menu_menu_default_menu_links() {
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
  // Exported menu link: main-menu:<front>
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
    'weight' => '-7',
  );
  // Exported menu link: main-menu:<front>
  $menu_links['main-menu:<front>'] = array(
    'menu_name' => 'main-menu',
    'link_path' => '<front>',
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
    'weight' => '-47',
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
    'weight' => '-48',
  );
  // Translatables
  // Included for use with string extractors like potx.
  t('About');
  t('Groups');


  return $menu_links;
}
