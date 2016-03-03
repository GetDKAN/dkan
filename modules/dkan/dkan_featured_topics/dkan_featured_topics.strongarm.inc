<?php
/**
 * @file
 * dkan_featured_topics.strongarm.inc
 */

/**
 * Implements hook_strongarm().
 */
function dkan_featured_topics_strongarm() {
  $export = array();

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'field_bundle_settings_taxonomy_term__dkan_topics';
  $strongarm->value = array(
    'view_modes' => array(),
    'extra_fields' => array(
      'form' => array(
        'path' => array(
          'weight' => '6',
        ),
        'name' => array(
          'weight' => '0',
        ),
        'description' => array(
          'weight' => '1',
        ),
      ),
      'display' => array(),
    ),
  );
  $export['field_bundle_settings_taxonomy_term__dkan_topics'] = $strongarm;

  return $export;
}
