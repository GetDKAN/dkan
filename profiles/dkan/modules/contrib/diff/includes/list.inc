<?php

/**
 * @file
 * Provide diff field functions for the List module.
 */

/**
 * Diff field callback for parsing list field comparative values.
 */
function list_field_diff_view($items, $context) {
  $field = $context['field'];
  $instance = $context['instance'];
  $settings = $context['settings'];

  $diff_items = array();
  $allowed_values = list_allowed_values($field, $instance, $context['entity_type'], $context['entity']);
  foreach ($items as $delta => $item) {
    // Fairly complex condition to prevent duplicate "key (key)" type rendering.
    if (isset($allowed_values[$item['value']]) &&
        $allowed_values[$item['value']] != $item['value'] &&
        strlen($allowed_values[$item['value']])) {
      switch ($settings['compare']) {
        case 'both':
          $diff_items[$delta] = $allowed_values[$item['value']] . ' (' . $item['value'] . ')';
          break;

        case 'label':
          $diff_items[$delta] = $allowed_values[$item['value']];
          break;

        default:
          $diff_items[$delta] = $item['value'];
          break;

      }
    }
    else {
      // If no match was found for the label, fall back to the key.
      $diff_items[$delta] = $item['value'];
    }
  }
  return $diff_items;
}

/**
 * Provide default field comparison options.
 */
function list_field_diff_default_options($field_type) {
  return array(
    'compare' => 'label',
  );
}

/**
 * Provide a form for setting the field comparison options.
 */
function list_field_diff_options_form($field_type, $settings) {
  $options_form = array();
  $options_form['compare'] = array(
    '#type' => 'radios',
    '#title' => t('Comparison method'),
    '#options' => array(
      'label' => t('Label'),
      'key' => t('Key'),
      'both' => t('Label (key)'),
    ),
    '#default_value' => $settings['compare'],
  );
  return $options_form;
}
