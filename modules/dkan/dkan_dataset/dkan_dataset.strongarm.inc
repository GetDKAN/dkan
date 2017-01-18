<?php

/**
 * @file
 * dkan_dataset.strongarm.inc
 */

/**
 * Implements hook_strongarm().
 */
function dkan_dataset_strongarm() {
  $export = array();

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'pathauto_node_dataset_pattern';
  $strongarm->value = 'dataset/[node:title]';
  $export['pathauto_node_dataset_pattern'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'pathauto_node_resource_pattern';
  $strongarm->value = 'dataset/[node:field-dataset-ref:0:title]/resource/[node:uuid]';
  $export['pathauto_node_resource_pattern'] = $strongarm;

  return $export;
}
