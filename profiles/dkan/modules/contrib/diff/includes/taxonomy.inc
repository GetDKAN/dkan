<?php

/**
 * @file
 * Implements pusdeo-hook hook_field_diff_view() for the Taxonomy module.
 */

/**
 * Diff field callback for preloading term entities.
 */
function taxonomy_field_diff_view_prepare(&$old_items, &$new_items, $context) {
  $tids = array();
  foreach (array_merge_recursive($old_items, $new_items) as $info) {
    $tids[$info['tid']] = $info['tid'];
  }
  $terms = taxonomy_term_load_multiple($tids);
  foreach ($old_items as $delta => $info) {
    $old_items[$delta]['term'] = isset($terms[$info['tid']]) ? $terms[$info['tid']] : NULL;
  }
  foreach ($new_items as $delta => $info) {
    $new_items[$delta]['term'] = isset($terms[$info['tid']]) ? $terms[$info['tid']] : NULL;
  }
}

/**
 * Diff field callback for parsing term field comparative values.
 */
function taxonomy_field_diff_view($items, $context) {
  $settings = $context['settings'];
  $instance = $context['instance'];

  $diff_items = array();
  foreach ($items as $delta => $item) {
    if (!empty($item['tid'])) {
      if ($item['tid'] == 'autocreate') {
        $diff_items[$delta] = t('!term_name (new)', array('!term_name' => $item['name']));
      }
      elseif (empty($item['term'])) {
        $diff_items[$delta] = t('Missing term reference (!tid)', array('!tid' => $item['tid']));
      }
      else {
        $output = array();
        $output['name'] = $item['term']->name;
        if ($settings['show_id']) {
          $output['tid'] = t('Term ID: !tid', array('!tid' => $item['term']->tid));
        }
        $diff_items[$delta] = implode('; ', $output);
      }
    }
  }
  if (!empty($settings['sort']) && !empty($diff_items)) {
    if ($settings['sort'] == DIFF_SORT_VALUE || $instance['widget']['type'] == 'taxonomy_autocomplete') {
      usort($diff_items, 'uasort_taxonomy_field_diff_terms');
    }
  }
  return $diff_items;
}

/**
 * Helper function for sorting terms.
 */
function uasort_taxonomy_field_diff_terms($a, $b) {
  // We need to use t() to test for string overrides.
  $missing_text = t('Missing term reference');
  $a_missing = strpos($a, $missing_text) === 0;
  $b_missing = strpos($b, $missing_text) === 0;
  if ($a_missing && $b_missing) {
    return strnatcmp($a, $b);
  }
  elseif ($a_missing xor $b_missing) {
    return $a_missing ? 100 : -100;
  }
  return strnatcmp($a, $b);
}

/**
 * Provide default field comparison options.
 */
function taxonomy_field_diff_default_options($field_type) {
  return array(
    'show_id' => 0,
    'sort' => DIFF_SORT_CUSTOM,
  );
}

/**
 * Provide a form for setting the field comparison options.
 */
function taxonomy_field_diff_options_form($field_type, $settings) {
  $options_form = array();
  $options_form['show_id'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show term ID'),
    '#default_value' => $settings['show_id'],
  );
  $options_form['sort'] = array(
    '#type' => 'radios',
    '#title' => t('Sort'),
    '#options' => array(
      DIFF_SORT_NONE => t('No sort'),
      DIFF_SORT_VALUE => t('Sort'),
      DIFF_SORT_CUSTOM => t('Sort if free tagging field'),
    ),
    '#default_value' => $settings['sort'],
  );
  return $options_form;
}
