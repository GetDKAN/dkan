<?php
/**
 * @file
 * dkan_front.strongarm.inc
 */

/**
 * Implements hook_strongarm().
 */
function dkan_front_strongarm() {
  $export = array();

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'compact_forms_descriptions';
  $strongarm->value = 1;
  $export['compact_forms_descriptions'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'compact_forms_ids';
  $strongarm->value = 'user-login-form
views-exposed-form-dataset-page';
  $export['compact_forms_ids'] = $strongarm;

  return $export;
}
