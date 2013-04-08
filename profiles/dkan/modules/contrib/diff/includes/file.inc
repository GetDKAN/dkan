<?php

/**
 * @file
 * Provide diff field functions for the file module.
 */

/**
 * Diff field callback for preloading file entities.
 */
function file_field_diff_view_prepare(&$old_items, &$new_items, $context) {
  $fids = array();
  foreach (array_merge_recursive($old_items, $new_items) as $info) {
    $fids[$info['fid']] = $info['fid'];
  }
  $files = file_load_multiple($fids);

  foreach ($old_items as $delta => $info) {
    $old_items[$delta]['file'] = isset($files[$info['fid']]) ? $files[$info['fid']] : NULL;
  }
  foreach ($new_items as $delta => $info) {
    $new_items[$delta]['file'] = isset($files[$info['fid']]) ? $files[$info['fid']] : NULL;
  }
}

/**
 * Diff field callback for parsing file field comparative values.
 */
function file_field_diff_view($items, $context) {
  $field = $context['field'];
  $instance = $context['instance'];
  $settings = $context['settings'];

  $diff_items = array();
  foreach ($items as $delta => $item) {
    if (isset($item['file'])) {
      $output = array();

      // We populate as much as possible to allow the best flexability in any
      // string overrides.
      $t_args = array();
      foreach ($item as $key => $value) {
        if (is_scalar($value)) {
          $t_args['!' . $key] = $value;
        }
      }
      // Some states do not have the file properties in the item, so put these
      // out of the main file object.
      if (!empty($item['file'])) {
        $file_values = (array) $item['file'];
        foreach ($file_values as $key => $value) {
          if (is_scalar($value) && !isset($t_args['!' . $key])) {
            $t_args['!' . $key] = $value;
          }
        }
      }

      $output['file'] = t('File: !filename', $t_args);
      if ($settings['compare_description_field'] && !empty($instance['settings']['description_field'])) {
        if (!empty($item['description'])) {
          $output['description'] = t('Description: !description', $t_args);
        }
      }
      if ($settings['show_id']) {
        $output['fid'] = t('File ID: !fid', $t_args);
      }
      if ($settings['compare_display_field'] && !empty($field['settings']['display_field'])) {
        $output['display'] = $item['display'] ? t('Displayed') : t('Hidden');
      }
      $diff_items[$delta] = implode('; ', $output);
    }
  }

  return $diff_items;
}

/**
 * Provide default field comparison options.
 */
function file_field_diff_default_options($field_type) {
  return array(
    'show_id' => 0,
    'compare_display_field' => 0,
    'compare_description_field' => 0,
  );
}

/**
 * Provide a form for setting the field comparison options.
 */
function file_field_diff_options_form($field_type, $settings) {
  $options_form = array();
  $options_form['show_id'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show file ID'),
    '#default_value' => $settings['show_id'],
  );
  $options_form['compare_description_field'] = array(
    '#type' => 'checkbox',
    '#title' => t('Compare description field'),
    '#default_value' => $settings['compare_description_field'],
    '#description' => t('This is only used if the "Enable <em>Description</em> field" is checked in the instance settings.'),
  );
  $options_form['compare_display_field'] = array(
    '#type' => 'checkbox',
    '#title' => t('Compare display state field'),
    '#default_value' => $settings['compare_display_field'],
    '#description' => t('This is only used if the "Enable <em>Display</em> field" is checked in the field settings.'),
  );
  return $options_form;
}
