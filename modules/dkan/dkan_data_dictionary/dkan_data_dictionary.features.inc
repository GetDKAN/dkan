<?php
/**
 * @file
 * dkan_data_dictionary.features.inc
 */

/**
 * Implements hook_paragraphs_info().
 */
function dkan_data_dictionary_paragraphs_info() {
  $items = array(
    'data_dictionary' => array(
      'name' => 'Data Dictionary',
      'bundle' => 'data_dictionary',
      'locked' => '1',
    ),
  );
  return $items;
}
