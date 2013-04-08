<?php

/**
 * @file
 * Provide diff field functions for the image module.
 */

/**
 * Diff field callback for preloading the image file entities.
 */
function image_field_diff_view_prepare(&$old_items, &$new_items, $context) {
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
 * Diff field callback for parsing image field comparative values.
 */
function image_field_diff_view($items, $context) {
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

      $output[] = t('Image: !filename', $t_args);
      if ($settings['compare_alt_field'] && !empty($instance['settings']['alt_field'])) {
        if (!empty($item['alt'])) {
          $output[] = t('Alt: !alt', $t_args);
        }
      }
      if ($settings['compare_title_field'] && !empty($instance['settings']['title_field'])) {
        if (!empty($item['title'])) {
          $output[] = t('Title: !title', $t_args);
        }
      }
      if ($settings['show_id']) {
        $output[] = t('File ID: !fid', $t_args);
      }
      $diff_items[$delta] = implode('; ', $output);
    }
  }

  return $diff_items;
}

/**
 * Provide default field comparison options.
 */
function image_field_diff_default_options($field_type) {
  return array(
    'show_id' => 0,
    'compare_alt_field' => 0,
    'compare_title_field' => 0,
  );
}

/**
 * Provide a form for setting the field comparison options.
 */
function image_field_diff_options_form($field_type, $settings) {
  $options_form = array();
  $options_form['show_id'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show image ID'),
    '#default_value' => $settings['show_id'],
  );
  $options_form['compare_alt_field'] = array(
    '#type' => 'checkbox',
    '#title' => t('Compare <em>Alt</em> field'),
    '#default_value' => $settings['compare_alt_field'],
    '#description' => t('This is only used if the "Enable <em>Alt</em> field" is checked in the instance settings.'),
  );
  $options_form['compare_title_field'] = array(
    '#type' => 'checkbox',
    '#title' => t('Compare <em>Title</em> field'),
    '#default_value' => $settings['compare_title_field'],
    '#description' => t('This is only used if the "Enable <em>Title</em> field" is checked in the instance settings.'),
  );
  return $options_form;
}
