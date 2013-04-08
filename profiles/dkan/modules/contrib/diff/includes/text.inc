<?php

/**
 * @file
 * Provide diff field functions for the Text module.
 */

/**
 * Diff field callback for parsing text field comparative values.
 */
function text_field_diff_view($items, $context) {
  $field = $context['field'];
  $instance = $context['instance'];
  $settings = $context['settings'];

  $diff_items = array();
  foreach ($items as $delta => $item) {
    $diff_items[$delta] = array();

    // Compute the format for appending to the label.
    $format_text = '';
    if ($instance['settings']['text_processing'] && $settings['compare_format']) {
      $format_id = empty($item['format']) ? filter_fallback_format() : $item['format'];
      if ($format = filter_format_load($format_id)) {
        $format_text = $format->name;
      }
      else {
        $format_text = t('Missing format !format', array('!format' => $format_id));
      }
    }

    // Compare the summary fields.
    $summary = $field['type'] == 'text_with_summary' && $settings['compare_summary'];
    if ($summary) {
      // Allow users to optionally clean system specific characters.
      if (empty($item['summary'])) {
        $diff_items[$delta][] = t('Summary field is empty.');
      }
      else {
        if ($format_text) {
          $diff_items[$delta][] = t('Summary (!text_format):', array('!text_format' => $format_text));
        }
        else {
          $diff_items[$delta][] = t('Summary:');
        }
        $diff_items[$delta][] = diff_normalise_text($item['summary']);
      }
    }

    // Only show label if field has summary displayed.
    if ($summary) {
      if ($format_text) {
        $diff_items[$delta][] = t('Content (!text_format):', array('!text_format' => $format_text));
      }
      else {
        $diff_items[$delta][] = t('Content:');
      }
    }

    // Allow users to optionally clean system specific characters.
    $diff_items[$delta][] = diff_normalise_text($item['value']);

    // If no summary, append the format selection to the bottom of the screen.
    // This prevents adding the "Content (format)" label.
    if ($format_text && !$summary) {
      $diff_items[$delta][] = t('Text format: !text_format', array('!text_format' => $format_text));
    }

    $diff_items[$delta] = $diff_items[$delta];
  }
  return $diff_items;
}

/**
 * Provide default field comparison options.
 */
function text_field_diff_default_options($field_type) {
  // Overrides the global 'markdown' setting which does not escape HTML.
  $settings = array(
    'compare_format' => 0,
    'markdown' => 'drupal_html_to_text',
    'line_counter' => '',
  );
  if ($field_type == 'text_with_summary') {
    $settings += array(
      'compare_summary' => 0,
    );
  }

  return $settings;
}

/**
 * Provide a form for setting the field comparison options.
 */
function text_field_diff_options_form($field_type, $settings) {
  $options_form = array();
  $options_form['compare_format'] = array(
    '#type' => 'checkbox',
    '#title' => t('Compare format'),
    '#default_value' => $settings['compare_format'],
    '#description' => t('This is only used if the "Text processing" instance settings are set to <em>Filtered text (user selects text format)</em>.'),
  );
  if ($field_type == 'text_with_summary') {
    $options_form['compare_summary'] = array(
      '#type' => 'checkbox',
      '#title' => t('Compare summary separately'),
      '#default_value' => $settings['compare_summary'],
      '#description' => t('This is only used if the "Summary input" option is checked in the instance settings.'),
    );
  }
  return $options_form;
}
