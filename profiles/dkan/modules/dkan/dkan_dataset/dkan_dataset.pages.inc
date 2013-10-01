<?php

/**
 * @file
 * Pages for the datastore.
 */

/**
 * Callback for back link.
 */
function dkan_dataset_back($node) {
  $node_wrapper = entity_metadata_wrapper('node', $node);
  $ref = $node_wrapper->field_dataset_ref->value();

  if (isset($ref[0])) {
    drupal_goto('node/' . $ref[0]->nid);
  }
  else {
    drupal_set_message(t('There is no dataset associated with this resource.'));
  }
  return '';
}

/**
 * Callback for 'Add Resouce' tab.
 */
function dkan_dataset_add_resource($node) {
  drupal_goto('node/add/resource', array('query' => array('dataset' => $node->nid)));
}
