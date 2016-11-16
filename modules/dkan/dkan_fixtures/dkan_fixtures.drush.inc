<?php

/**
 * @file
 * Drush integration for the dkan_fixtures module.
 */

/**
 * Implements hook_drush_command().
 */
function dkan_fixtures_drush_command() {
  $items = array();
  $items['dkan-save-data'] = array(
    'description' => dt('Creates JSON files out of dkan api endpoints'),
    'arguments' => array(),
    'aliases' => array('dsd'),
  );
  return $items;
}

/**
 * Drush save_fixtures command callback.
 */
function drush_dkan_fixtures_dkan_save_data() {
  $args = drush_get_arguments();
  $nids = array();
  if (isset($args[1])) {
    $nids = array($args[1]);
  }
  dkan_fixtures_dkan_save_data($nids);
}
