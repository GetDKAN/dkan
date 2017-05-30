<?php

/**
 * @file
 * dkan_dataset_voting.strongarm.inc
 */

/**
 * Implements hook_strongarm().
 */
function dkan_dataset_voting_strongarm() {
  $export = array();

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'ajax_comments_node_types';
  $strongarm->value = array(
    'dataset' => 'dataset',
    'group' => 0,
    'page' => 0,
    'resource' => 0,
  );
  $export['ajax_comments_node_types'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'ajax_comments_notify';
  $strongarm->value = 1;
  $export['ajax_comments_notify'] = $strongarm;

  return $export;
}
