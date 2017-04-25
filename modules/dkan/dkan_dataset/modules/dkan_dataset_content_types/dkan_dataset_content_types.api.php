<?php

/**
 * @file
 * Let there be hooks.
 */

/**
 * Adds a set of license options for the dkan dataset license field.
 */
function hook_license_subscribe() {
  return array(
    'license-key' => array(
      'label' => 'license label (pretty representation for license key)',
      'uri' => '(Optional) uri to license page',
    ),
  );
}

/**
 * Removes a set of license options for the dkan dataset license field.
 */
function hook_license_unsubscribe() {
  return array(
    'license-key',
  );
}
