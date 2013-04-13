<?php

/**
 * @file
 * Pages for the datastore.
 */

/**
 * Callback for back link.
 */
function dkan_dataset_back($node) {
  if (isset($node->field_dataset_ref[$node->language][0]['target_id'])) {
    drupal_goto('node/' . $node->field_dataset_ref[$node->language][0]['target_id']);
  }
  return '';
}

/**
 * Callback for 'Add Resouce' tab.
 */
function dkan_dataset_add_resource($node) {
  drupal_goto('node/add/resource', array('query' => array('dataset' => $node->nid)));
}
