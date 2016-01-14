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

  // Exported menu link: main-menu_about:node/1
  $menu_links['main-menu_about:node/1'] = array(
    'menu_name' => 'main-menu',
    'link_path' => 'node/1',
    'router_path' => 'node/%',
    'link_title' => 'About',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'main-menu_about:node/1',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -49,
    'customized' => 1,
  );
  // Exported menu link: main-menu_add-dataset:node/add/dataset
  $menu_links['main-menu_add-dataset:node/add/dataset'] = array(
    'menu_name' => 'main-menu',
    'link_path' => 'node/add/dataset',
    'router_path' => 'node/add/dataset',
    'link_title' => 'Add Dataset',
    'options' => array(
      'attributes' => array(
        'title' => '',
      ),
      'identifier' => 'main-menu_add-dataset:node/add/dataset',
    ),
    'module' => 'menu',
    'hidden' => 0,
    'external' => 0,
    'has_children' => 0,
    'expanded' => 0,
    'weight' => -50,
    'customized' => 0,
  );

  // Exported menu link: main-menu_add-dataset:node/add/dataset
  $menu_links['main-menu_dataset'] = array(
      'menu_name' => 'main-menu',
      'link_path' => 'dataset',
      'router_path' => 'dataset',
      'link_title' => 'Datasets',
      'options' => array(
          'attributes' => array(
              'title' => '',
          ),
          'identifier' => 'main-menu_dataset',
          'query' => array(
              'f[0]' => 'type:dataset'
          )
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
  t('Add Dataset');
  t('Datasets');

  return $menu_links;
}
