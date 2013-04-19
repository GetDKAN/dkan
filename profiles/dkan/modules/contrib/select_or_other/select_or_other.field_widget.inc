<?php

/**
 * @file
 * The Select (or other) field widget.
 */

/**
 * Implements hook_field_widget_info().
 */
function select_or_other_field_widget_info() {
  $field_types = array(
    'text',
    'number_integer',
    'number_decimal',
    'number_float',
  );
  $settings = array(
    'available_options' => '',
    'available_options_php' => '',
    'markup_available_options_php' => t('&lt;none&gt;'),
    'other' => t('Other'),
    'other_title' => '',
    'other_unknown_defaults' => 'other',
    'other_size' => 60,
  );
  return array(
    'select_or_other' => array(
      'label' => t('Select (or other) list'),
      'field types' => $field_types,
      'behaviors' => array(
        'multiple values' => FIELD_BEHAVIOR_CUSTOM,
        'default value' => FIELD_BEHAVIOR_DEFAULT,
      ),
      'settings' => $settings,
      'weight' => 2,
    ),
    'select_or_other_sort' => array(
      'label' => t('Select (or other) drag and drop lists [deprecated in favor of Field Collection]'),
      'field types' => $field_types,
      'behaviors' => array(
        'multiple values' => FIELD_BEHAVIOR_DEFAULT,
        'default value' => FIELD_BEHAVIOR_DEFAULT,
      ),
      'settings' => $settings,
      'weight' => 2,
    ),
    'select_or_other_buttons' => array(
      'label' => t('Select (or other) check boxes/radio buttons'),
      'field types' => $field_types,
      'behaviors' => array(
        'multiple values' => FIELD_BEHAVIOR_CUSTOM,
        'default value' => FIELD_BEHAVIOR_DEFAULT,
      ),
      'settings' => $settings,
      'weight' => 2,
    ),
  );
}

/**
 * Prepare a single option.
 */
function select_or_other_field_widget_form_prepare_option(&$options, $key, $opt, $settings) {
  $opt = trim($opt);
  if (empty($opt)) {
    return;
  }
  // Sanitize the user input with a permissive filter.
  $opt = filter_xss($opt);
  // If option has a key specified
  if (strpos($opt, '|') !== FALSE && empty($settings['available_options_php'])) {
    list($key, $value) = explode('|', $opt);
    $options[$key] = (isset($value) && $value !== '') ? html_entity_decode($value) : $key;
  }
  // If options from PHP
  elseif (!empty($settings['available_options_php'])) {
    $options[$key] = html_entity_decode($opt);
  }
  // If option has no key specified and is not from PHP
  else {
    $options[$opt] = html_entity_decode($opt);
  }
}

/**
 * Prepare options for the widget list.
 */
function select_or_other_field_widget_form_prepare_options($field, $instance, $has_value = FALSE) {
  $options = array();

  $settings = &$instance['widget']['settings'];

  // Create options - similar to code from content_allowed_values().
  $list = explode("\n", $settings['available_options']);

  if (!empty($settings['available_options_php'])) {
    ob_start();
    $list = eval($settings['available_options_php']);
    ob_end_clean();
  }

  foreach ($list as $key => $opt) {
    if (is_array($opt)) {
      $optgroup_options = array();
      foreach ($opt as $optgroup_key => $optgroup_opt) {
        select_or_other_field_widget_form_prepare_option($optgroup_options, $optgroup_key, $optgroup_opt, $settings);
        $options[$key] = $optgroup_options;
      }
    }
    else {
      select_or_other_field_widget_form_prepare_option($options, $key, $opt, $settings);
    }
  }

  $required = (isset($instance['required']) && $instance['required']);
  $multiple = (($instance['widget']['type'] == 'select_or_other' && $field['cardinality'] == -1)
    || ($instance['widget']['type'] == 'select_or_other_sort'));
  $multiple_checkbox = ($instance['widget']['type'] == 'select_or_other_buttons' && $field['cardinality'] == -1);

  $empty_option = array('_none' => theme('select_or_other_none', array('instance' => $instance)));
  // Multiple select.
  if ($multiple) {
    // Add a 'none' option for non-required fields.
    if (!$required) {
      $options = $empty_option + $options;
    }
  }
  // Single select.
  elseif (!$multiple_checkbox) {
    // Add a 'none' option for non-required fields, and a 'select a value'
    // option for required fields that do not come with a value selected.
    if (!$required) {
      $options = $empty_option + $options;
    }
  }

  // @todo: This isset() can probably be taken out in drupal 8.
  if (isset($settings['sort_options']) && $settings['sort_options']) {
    natcasesort($options);
  }

  return $options;
}

/**
 * Implements hook_field_widget_form().
 */
function select_or_other_field_widget_form(&$form, &$form_state, $field, $instance, $langcode, $items, $delta, $element) {
  // Construct the element.
  $element = array(
    '#type' => 'select_or_other',
    '#title' => isset($instance['label']) ? $instance['label'] : NULL,
    '#other' => isset($instance['widget']['settings']['other']) ? $instance['widget']['settings']['other'] : t('Other'),
    '#other_title' => !empty($instance['widget']['settings']['other_title']) ? $instance['widget']['settings']['other_title'] : NULL,
    '#other_size' => $instance['widget']['settings']['other_size'],
    '#default_value' => !empty($items[$delta]['value']) ? $items[$delta]['value'] : NULL,
    '#options' => select_or_other_field_widget_form_prepare_options($field, $instance, !empty($items[$delta])),
    '#description' => isset($instance['description']) ? $instance['description'] : '',
    '#multiple' => $field['cardinality'] == 1 ? FALSE : $field['cardinality'],
    '#required' => $instance['required'],
    //'#other_delimiter' => $field['widget']['settings']['other_delimiter'] == 'FALSE' ? FALSE : $field['widget']['settings']['other_delimiter'],
    '#other_delimiter' => FALSE,
    '#other_unknown_defaults' => isset($instance['widget']['settings']['other_unknown_defaults']) ? $instance['widget']['settings']['other_unknown_defaults'] : 'other',
    '#element_validate' => array('select_or_other_field_widget_validate'),
    '#field_widget' => $instance['widget']['type'],
  );
  if (!empty($field['settings']['max_length'])) {
    $element['#maxlength'] = $field['settings']['max_length'];
  }

  // Set select type's.
  switch ($instance['widget']['type']) {
    case 'select_or_other':
      $element['#select_type'] = 'select';
      break;

    case 'select_or_other_sort':
      $element['#select_type'] = 'select';
      // Also disable multiples for this select type.
      $element['#multiple'] = FALSE;
      break;

    case 'select_or_other_buttons':
      $element['#select_type'] = $field['cardinality'] == 1 ? 'radios' : 'checkboxes';
      break;
  }

  // In situations where we handle our own multiples (checkboxes and multiple selects) set defaults differently.
  if ($element['#multiple']) {
    $element['#default_value'] = array();
    foreach ($items as $delta => $item) {
      $element['#default_value'][$delta] = $item['value'];
    }
  }

  return $element;
}

/**
 * Implements hook_field_widget_settings_form().
 */
function select_or_other_field_widget_settings_form($field, $instance) {
  $form = array();

  $settings = &$instance['widget']['settings'];

  $form['available_options'] = array(
    '#type' => 'textarea',
    '#title' => t('Available options'),
    '#description' => t('A list of values that are, by default, available for selection. Enter one value per line, in the format key|label. The key is the value that will be stored in the database, and the label is what will be displayed to the user.'),
    '#default_value' => isset($settings['available_options']) ? $settings['available_options'] : '',
    '#element_validate' => array('select_or_other_field_widget_settings_validate'),
  );

  if (user_access('use PHP for settings')) {
    $form['available_options_php'] = array(
      '#type' => 'textarea',
      '#title' => t('Available options PHP'),
      '#default_value' => !empty($settings['available_options_php']) ? $settings['available_options_php'] : '',
      '#rows' => 6,
      '#description' => t('Advanced usage only: PHP code that returns a keyed array of available options. Should not include &lt;?php ?&gt; delimiters. If this field is filled out, the array returned by this code will override the available options list above.'),
    );
  }
  else {
    $form['available_options_php'] = array(
      '#type' => 'value',
      '#value' => !empty($settings['available_options_php']) ? $settings['available_options_php'] : '',
    );
    if (!empty($settings['available_options_php'])) {
      $form['markup_available_options_php'] = array(
        '#type' => 'item',
        '#title' => t('Available options PHP'),
        '#value' => '<code>' . check_plain($settings['available_options_php']) . '</code>',
        '#description' => empty($settings['available_options_php']) ? t("You're not allowed to input PHP code.") : t('This PHP code was set by an administrator and will override the allowed values list above.'),
      );
    }
  }
  $form['other'] = array(
    '#type' => 'textfield',
    '#title' => t('<em>Other</em> option'),
    '#description' => t('Label for the option that the user will choose when they want to supply an <em>other</em> value.'),
    '#default_value' => isset($settings['other']) ? $settings['other'] : t('Other'),
    '#required' => TRUE,
  );
  $form['other_title'] = array(
    '#type' => 'textfield',
    '#title' => t('<em>Other</em> field title'),
    '#description' => t('Label for the field in which the user will supply an <em>other</em> value.'),
    '#default_value' => isset($settings['other_title']) ? $settings['other_title'] : '',
  );
  $form['other_unknown_defaults'] = array(
    '#type' => 'select',
    '#title' => t('<em>Other</em> value as default value'),
    '#description' => t("If any incoming default values do not appear in <em>available options</em> (i.e. set as <em>other</em> values), what should happen?"),
    '#options' => array(
      'other' => t('Add the values to the other textfield'),
      'append' => t('Append the values to the current list'),
      'available' => t('Append the values to the available options'),
      'ignore' => t('Ignore the values'),
    ),
    '#default_value' => isset($settings['other_unknown_defaults']) ? $settings['other_unknown_defaults'] : 'other',
    '#required' => TRUE,
  );
  $form['other_size'] = array(
    '#type' => 'textfield',
    '#title' => t('<em>Other</em> field size'),
    '#default_value' => $settings['other_size'],
    '#required' => TRUE,
    '#element_validate' => array('element_validate_integer_positive'),
  );
  $form['sort_options'] = array(
    '#type' => 'checkbox',
    '#title' => t('Sort options'),
    '#description' => t("Sorts the options in the list alphabetically by value."),
    '#default_value' => isset($settings['sort_options']) ? $settings['sort_options'] : 0,
  );
  /*
  There are design issues with saving multiple other values with some field widgets - this needs a rethink.

  $form['other_delimiter'] = array(
    '#type' => 'textfield',
    '#title' => t('Other delimiter'),
    '#description' => t("Delimiter string to delimit multiple 'other' values into the <em>other</em> textfield.  If you enter <em>FALSE</em> only the last value will be used."),
    '#default_value' => isset($settings['other_delimiter']) ? $settings['other_delimiter'] : ', ',
    '#required' => TRUE,
    '#size' => 5,
  );
  */
  $form['#validate'][] = 'select_or_other_field_widget_settings_validate';
  return $form;
}

/**
 * Validate callback for a Select (or other) field widget settings form.
 */
function select_or_other_field_widget_settings_validate($element, &$form_state, $form) {
  $settings = &$form_state['values']['instance']['widget']['settings'];
  if (empty($settings['available_options']) && empty($settings['available_options_php'])) {
    form_set_error(implode('][', $element['#parents']), t('You must provide <em>Available options</em>.'));
  }
}

/**
 * Transforms submitted form values into field storage format.
 * Align with Drupal core select list, NULL (-None-) value.
 * http://drupal.org/node/1830090
 */
function _select_or_other_options_form_to_storage($value) {
  if (isset($value[0])) {
    $index = array_search('_none', $value[0], TRUE);
    if ($index !== FALSE) {
      unset($value[0][$index]);
    }
  }
  return $value;
}

/**
 * Element validate callback for a Select (or other) field widget.
 */
function select_or_other_field_widget_validate($element, &$form_state) {
  //$field_name = $element['#parents'][count($element['#parents']) - 2];
  $field_name = $element['#field_name'];
  $field_info = field_info_field($field_name);

  $other_selected = FALSE;

  if (is_array($element['select']['#value']) && isset($element['select']['#value']['select_or_other'])) {
    // This is a multiselect. assoc arrays
    $other_selected = TRUE;
    $value = $element['select']['#value'];
    unset($value['select_or_other']);
    $value[$element['other']['#value']] = $element['other']['#value'];
  }
  elseif (is_string($element['select']['#value']) && $element['select']['#value'] == 'select_or_other') {
    // This is a single select.
    $other_selected = TRUE;
    $value = $element['other']['#value'];
  }
  else {
    $value = $element['select']['#value'];
  }
  if ($other_selected && !$element['other']['#value']) {
    form_error($element['other'], t('!name: !title is required', array('!name' => t($element['select']['#title']), '!title' => $element['#other'])));
  }
  if (isset($value) && $value !== "") {
    if (in_array($element['#field_widget'], array('select_or_other', 'select_or_other_buttons'))) {
      // Filter out 'none' value (if present, will always be in key 0)
      if (isset($items[0]['value']) && $items[0]['value'] === '') {
        unset($items[0]);
      }
      if ($element['#multiple'] >= 2 && count($value) > $element['#multiple']) {
        form_error($element['select'], t('!name: cannot hold more than @count values.', array('!name' => t($element['select']['#title']), '@count' => $element['#multiple'])));
      }
      $delta = 0;
      $values = array();
      foreach ((array)$value as $v) {
        if ($field_info['type'] == 'number_integer' && !preg_match('/^-?\d+$/', $v)) {
          form_error($element, t('!name field must be a valid integer.', array('%name' => t($element['select']['#title']))));
          break;
        }
        if (($field_info['type'] == 'number_float' || $field_info['type'] == 'number_decimal') && !is_numeric($v)) {
          form_error($element, t('!name field must be a valid integer or decimal.', array('!name' => t($element['select']['#title']))));
          break;
        }
        elseif ($field_info['type'] == 'text' && drupal_strlen($v) > $field_info['settings']['max_length']) {
          form_error($element, t('!name field must be a string at most @max characters long.', array('!name' => t($element['select']['#title']), '@max' => $field_info['settings']['max_length'])));
          break;
        }
        $values[$delta++]['value'] = $v;
      }
      $value = $values;
    }
    elseif ($element['#field_widget'] == 'select_or_other_sort') {
      $value = array('value' => $value);
    }

    // Align with Drupal core select list, NULL (-None-) value.
    // http://drupal.org/node/1830090
    $value = _select_or_other_options_form_to_storage($value);

    form_set_value($element, $value, $form_state);
    $form_state['clicked_button']['#post'][$element['#name']] = $value; // Is this something we should do?
  }
  else {
    form_set_value($element, array(array('value' => '')), $form_state);
  }

  // Add values to available options is configured to do so.
  if (!empty($form_state['field'][$field_name][$element['#parents'][1]]['instance'])) {
    $instance = $form_state['field'][$field_name][$element['#parents'][1]]['instance'];
  }
  else {
    $instance = field_widget_instance($element, $form_state);
  }

  if ($instance['widget']['settings']['other_unknown_defaults'] == 'available') {
    if (
      ($element['select']['#value'] == 'select_or_other' || (is_array($element['select']['#value']) && isset($element['select']['#value']['select_or_other']))) &&
      !empty($element['other']['#value']) &&
      !isset($element['#options'][$element['other']['#value']])
    ) {
      // Get the latest instance.
      $instance = field_read_instance($instance['entity_type'], $instance['field_name'], $instance['bundle']);
      // Make the change.
      $instance['widget']['settings']['available_options'] .= "\n" . $element['other']['#value'];
      // Save the instance.
      field_update_instance($instance);
    }
  }

  //return $element;
}

/**
 * Implements hook_field_formatter_info().
 */
function select_or_other_field_formatter_info() {
  return array(
    'select_or_other_formatter' => array(
      'label' => t('Select or other'),
      'field types' => array('text', 'number_integer', 'number_decimal', 'number_float'),
    ),
  );
}

/**
 * Implements hook_field_formatter_view().
 */
function select_or_other_field_formatter_view($entity_type, $entity, $field, $instance, $langcode, $items, $display) {
  $element = array();
  $settings = $display['settings'];
  $field_options = array();

  if (isset($instance['widget']['settings']['available_options'])) {
    $field_options = explode("\n", $instance['widget']['settings']['available_options']);
    $pos = strpos($instance['widget']['settings']['available_options'], '|');

    if ($pos !== FALSE) {
      // There are keys.
      foreach ($field_options as $field_item) {
        $exploded = explode('|', $field_item);
        $temp_options[$exploded[0]] = $exploded[1];
      }
      $field_options = $temp_options;
    }
  }

  foreach ($items as $delta => $item) {
    if (array_key_exists($item['value'], $field_options)) {
      $element[$delta] = array('#markup' => $field_options[$item['value']]);
    }
    else {
      $element[$delta] = array('#markup' => $item['value']);
    }
  }

  return $element;
}


/**
 * Implements hook_field_widget_error().
 */
function select_or_other_field_widget_error($element, $error, $form, &$form_state) {
  form_error($element, $error['message']);
}
