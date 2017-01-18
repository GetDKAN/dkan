<?php

/**
 * @file
 * dkan_sitewide.features.defaultconfig.inc
 */

/**
 * Implements hook_defaultconfig_features().
 */
function dkan_sitewide_defaultconfig_features() {
  return array(
    'dkan_sitewide' => array(
      'strongarm' => 'strongarm',
    ),
  );
}

/**
 * Implements hook_defaultconfig_strongarm().
 */
function dkan_sitewide_defaultconfig_strongarm() {
  $export = array();

  return $export;
}
