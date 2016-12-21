<?php

/**
 * @file
 * Theme and preprocess functions for forms.
 */

/**
 * Implements hook_form_alter().
 */
function nuboot_radix_form_alter(&$form, &$form_state, $form_id) {
  switch ($form_id) {
    case 'colorizer_admin_settings':
      $form['colorizer_global']['colorizer_cssfile']['#default_value'] = 'colorizer/colorizer.css';
      $form['colorizer_global']['colorizer_incfile']['#default_value'] = 'colorizer/colorizer.inc';
      break;
  }
}

/**
 * Implements theme_form_element().
 *
 * Remove label wrapped around font-icon checkbox input.
 */
function nuboot_radix_form_element($variables) {
  $element = &$variables['element'];
  $is_checkbox = FALSE;
  $is_radio = FALSE;
  $is_input_group = FALSE;
  $is_topic = FALSE;

  // This is also used in the installer, pre-database setup.
  $t = get_t();

  // This function is invoked as theme wrapper, but the rendered form element
  // may not necessarily have been processed by form_builder().
  $element += array(
    '#title_display' => 'before',
  );

  // Add the default form-item class.
  $attributes['class'][] = 'form-item';
  // Remove text format guidelines.
  $element['format']['guidelines']['#access'] = FALSE;

  // Add element #id for #type 'item'.
  if (isset($element['#markup']) && !empty($element['#id'])) {
    $attributes['id'] = $element['#id'];
  }
  // Add element's #type and #name as class to aid with JS/CSS selectors.
  if (!empty($element['#type'])) {
    $attributes['class'][] = 'form-type-' . strtr($element['#type'], '_', '-');
  }
  if (!empty($element['#name'])) {
    $attributes['class'][] = 'form-item-' . strtr($element['#name'], array(
      ' ' => '-',
      '_' => '-',
      '[' => '-',
      ']' => '',
    ));
  }

  // Add error class.
  if (isset($element['#parents']) && form_get_error($element) !== NULL && !empty($element['#validated'])) {
    $attributes['class'][] = 'has-error';
  }

  // Add radios, checkboxes and form-group classes.
  if (isset($element['#type'])) {
    if ($element['#type'] == "radio") {
      $attributes['class'][] = 'radio';
      $is_radio = TRUE;
    }
    elseif ($element['#type'] == "checkbox") {
      $attributes['class'][] = 'checkbox';
      $is_checkbox = TRUE;
      if (!empty($element['#name'])) {
        $name = strtr($element['#name'], array(
          ' ' => '-',
          '_' => '-',
          '[' => '',
          ']' => '',
          'und' => '',
        ));
        // Target font-icon-select fields.
        if ($name == 'field-topic-icon') {
          $is_topic = TRUE;
        }
      }
    }
    else {
      $attributes['class'][] = 'form-group';
    }
  }

  // Recognize non-standard #input_group key to use a Bootstrap input group.
  if (!empty($element['#input_group'])) {
    $attributes['class'][] = 'input-group';
    $is_input_group = TRUE;
  }

  // Add a class for disabled elements to facilitate cross-browser styling.
  if (!empty($element['#attributes']['disabled'])) {
    $attributes['class'][] = 'form-disabled';
  }
  $output = '<div' . drupal_attributes($attributes) . '>' . "\n";

  // If #title is not set, we don't display any label or required marker.
  if (!isset($element['#title'])) {
    $element['#title_display'] = 'none';
    // Add a title on VBO checkboxes for screenreaders.
    if ($is_checkbox) {
      $element['#title_display'] = 'after';
      $element['#title'] = '<span class="screenreader">' . t('Select') . '</span>';
    }
  }

  // Support for non-standard #title_display options.
  if ($element['#title_display'] == 'prefix') {
    if (empty($element['#field_prefix'])) {
      $element['#field_prefix'] = '';
    }
    $element['#field_prefix'] = theme('form_element_label', $variables) . $element['#field_prefix'];
    $element['#title_display'] = 'none';
  }
  elseif ($element['#title_display'] == 'suffix') {
    if (empty($element['#field_suffix'])) {
      $element['#field_suffix'] = '';
    }
    $element['#field_suffix'] .= theme('form_element_label', $variables);
    $element['#title_display'] = 'none';
  }

  // Generate the prefix and suffix.
  if ($is_input_group) {
    $prefix = isset($element['#field_prefix']) ? theme('radix_input_group_addon', array('content' => $element['#field_prefix'])) . ' ' : '';
    $suffix = isset($element['#field_suffix']) ? ' ' . theme('radix_input_group_addon', array('content' => $element['#field_suffix'])) : '';
  }
  else {
    $prefix = isset($element['#field_prefix']) ? '<span class="field-prefix">' . $element['#field_prefix'] . '</span> ' : '';
    $suffix = isset($element['#field_suffix']) ? ' <span class="field-suffix">' . $element['#field_suffix'] . '</span>' : '';
  }

  switch ($element['#title_display']) {
    case 'invisible':
      if ($is_radio || $is_checkbox) {
        // Render the normal invisible label for screen readers.
        $output .= ' ' . theme('form_element_label', $variables);
        // But still ensure checkbox is wrapped in an empty label.
        $element['#title_display'] = 'before';
        $element['#title'] = '';
        $output .= ' ' . $prefix . theme('form_element_label', $variables) . $suffix . "\n";
      }
      else {
        $output .= ' ' . theme('form_element_label', $variables);
        $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
      }
      break;

    case 'before':
      $output .= ' ' . theme('form_element_label', $variables);
      $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
      break;

    case 'after':
      if ($is_radio || $is_checkbox) {
        $output .= ' ' . $prefix . theme('form_element_label', $variables) . $suffix . "\n";
      }
      else {
        $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
        $output .= ' ' . theme('form_element_label', $variables) . "\n";
      }
      break;

    case 'none':
    case 'attribute':
      if ($is_radio || $is_checkbox && !$is_topic) {
        // Ensure checkboxes still wrapped in a label.
        $element['#title_display'] = 'before';
        $output .= ' ' . $prefix . theme('form_element_label', $variables) . $suffix;
      }
      else {
        // Output no label and no required marker, only the children.
        $output .= ' ' . $prefix . $element['#children'] . $suffix . "\n";
      }
      break;
  }

  if (!empty($element['#description'])) {
    $output .= '<span class="help-block">' . $element['#description'] . "</span>\n";
  }

  $output .= "</div>\n";

  return $output;
}
