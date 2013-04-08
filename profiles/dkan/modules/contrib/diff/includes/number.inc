<?php

/**
 * @file
 * Provide diff field functions for the Number module.
 */

/**
 * Diff field callback for parsing number field comparative values.
 */
function number_field_diff_view($items, $context) {
  $diff_items = array();
  foreach ($items as $delta => $item) {
    $diff_items[$delta] = $item['value'];
  }
  return $diff_items;
}
