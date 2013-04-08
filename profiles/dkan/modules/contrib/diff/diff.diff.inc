<?php

/**
 * @file
 * Includes the hooks defined by diff_hook_info().
 */

/**
 * Implements hook_entity_diff().
 *
 * Helper function to invoke the depreciated hook_diff() for node entities.
 *
 * This manually invokes hook_diff() to avoid a function name clash with the
 * PHP 5 (>= 5.3.0) date_diff() function or the Dates modules implementation.
 *
 * @param object $old_entity
 *   The older node revision.
 * @param object $new_entity
 *   The newer node revision.
 * @param array $context
 *   An associative array containing:
 *   - entity_type: The entity type; e.g., 'node' or 'user'.
 *   - old_entity: The older entity.
 *   - new_entity: The newer entity.
 *   - view_mode: The view mode to use. Defaults to FALSE.
 */
function diff_entity_diff($old_entity, $new_entity, $context) {
  $return = array();

  $entity_type = $context['entity_type'];
  $info = entity_get_info($entity_type);
  if (!empty($info['fieldable'])) {
    $return = diff_entity_fields_diff($old_entity, $new_entity, $context);
  }

  return $return;
}

/**
 * Internal callback to handle fieldable entities.
 *
 * Field comparison is handled for core modules, but is expandable to any other
 * fields if the module defines MODULE_field_diff_view().
 *
 * @param object $old_entity
 *   The older entity entity revision.
 * @param object $new_entity
 *   The newer entity entity revision.
 * @param array $context
 *   An associative array containing:
 *   - entity_type: The entity type; e.g., 'node' or 'user'.
 *   - old_entity: The older entity.
 *   - new_entity: The newer entity.
 *   - view_mode: The view mode to use. Defaults to FALSE.
 *
 * @return array
 *   An associative array of values keyed by the field name and delta value.
 */
function diff_entity_fields_diff($old_entity, $new_entity, $context) {
  $result = array();

  $entity_type = $context['entity_type'];
  $view_mode = $context['view_mode'];

  $field_context = $context;

  $actual_mode = FALSE;
  list(,, $bundle_name) = entity_extract_ids($entity_type, $new_entity);
  $instances = field_info_instances($entity_type, $bundle_name);

  // Some fields piggy back the display settings, so we need to fake these by
  // ensuring that the field mode is always set.
  if (empty($view_mode)) {
    $actual_mode = 'diff_standard';
    $field_context['custom_settings'] = FALSE;
  }
  $view_mode_settings = field_view_mode_settings($entity_type, $bundle_name);
  $actual_mode = (!empty($view_mode_settings[$view_mode]['custom_settings'])) ? $view_mode : 'default';
  if (!isset($field_context['custom_settings'])) {
    $field_context['custom_settings'] = $actual_mode && $actual_mode == $view_mode;
  }

  $field_context['old_entity'] = $old_entity;
  $field_context['new_entity'] = $new_entity;
  $field_context['bundle_name'] = $bundle_name;

  foreach ($instances as $instance) {
    // Any view mode is supported in relation to hiding fields, but only if
    // selected (todo see if this is a valid option).
    if ($actual_mode && $instance['display'][$actual_mode]['type'] == 'hidden') {
      continue;
    }
    $field_name = $instance['field_name'];
    $field = field_info_field($field_name);
    $field_context['field'] = $field;
    $field_context['instance'] = $instance;
    $field_context['display'] = $instance['display'][$actual_mode];

    // We provide a loose check on the field access.
    if (field_access('view', $field, $entity_type) || field_access('edit', $field, $entity_type)) {
      $langcode = field_language($entity_type, $new_entity, $field_name);

      $field_context['language'] = $langcode;
      $field_context['field'] = $field;
      $field_context['instance'] = $instance;

      $old_items = array();
      if (!empty($old_entity->{$field_name}[$langcode])) {
        $old_items = $old_entity->{$field_name}[$langcode];
      }

      $new_items = array();
      if (!empty($new_entity->{$field_name}[$langcode])) {
        $new_items = $new_entity->{$field_name}[$langcode];
      }

      // Load files containing the field callbacks.
      _diff_autoload($field);

      $field_context['settings'] = diff_get_field_settings($field_context);

      // Reference fields can optionally prepare objects in bulk to reduce
      // overheads related to multiple database calls. If a field considers
      // that the delta values is meaningless, they can order and rearrange
      // to provide cleaner results.
      $func = $field['module'] . '_field_diff_view_prepare';
      if (function_exists($func)) {
        $func($old_items, $new_items, $field_context);
      }
      // Allow other modules to act safely on behalf of the core field module.
      drupal_alter('field_diff_view_prepare', $old_items, $new_items, $field_context);

      // These functions compiles the items into comparable arrays of strings.
      $func = $field['module'] . '_field_diff_view';
      if (!function_exists($func)) {
        $func = 'diff_field_diff_view';
      }

      // These callbacks should be independent of revision.
      $old_context = $field_context;
      $old_context['entity'] = $old_entity;
      $old_values = $func($old_items, $old_context);
      $new_context = $field_context;
      $new_context['entity'] = $new_entity;
      $new_values = $func($new_items, $new_context);

      // Allow other modules to act safely on behalf of the core field module.
      drupal_alter('field_diff_view', $old_values, $old_items, $old_context);
      drupal_alter('field_diff_view', $new_values, $new_items, $new_context);

      $max = max(array(count($old_values), count($new_values)));
      if ($max) {
        $result[$field_name] = array(
          '#name' => $instance['label'],
          '#old' => array(),
          '#new' => array(),
          '#settings' => $field_context['settings'],
        );
        for ($delta = 0; $delta < $max; $delta++) {
          if (isset($old_values[$delta])) {
            $result[$field_name]['#old'][] = is_array($old_values[$delta]) ? implode("\n", $old_values[$delta]) : $old_values[$delta];
          }
          if (isset($new_values[$delta])) {
            $result[$field_name]['#new'][] = is_array($new_values[$delta]) ? implode("\n", $new_values[$delta]) : $new_values[$delta];
          }
        }
        $result[$field_name]['#old'] = implode("\n", $result[$field_name]['#old']);
        $result[$field_name]['#new'] = implode("\n", $result[$field_name]['#new']);

        if ($actual_mode) {
          $result[$field_name]['#weight'] = $instance['display'][$actual_mode]['weight'];
        }

      }
    }
  }
  return $result;
}

/**
 * A generic handler for parsing field values.
 *
 * This callback can only handle the most basic of fields that populates the
 * safe_value during field load or use the value column for data storage.
 *
 * @param array $items
 *   An array of field items.
 * @param array $context
 *   An associative array containing:
 *   - entity: The entity that the items belong to.
 *   - entity_type: The entity type; e.g., 'node' or 'user'.
 *   - bundle: The bundle name.
 *   - field: The field that the items belong to.
 *   - instance: The instance that the items belong to.
 *   - language: The language associated with $items.
 *   - old_entity: The older entity.
 *   - new_entity: The newer entity.
 *
 * @return array
 *   An array of strings representing the value, keyed by delta index.
 */
function diff_field_diff_view($items, $context) {
  $diff_items = array();
   $entity = clone $context['entity'];
   $langcode = field_language($context['entity_type'], $entity, $context['field']['field_name']);
   $view_mode = empty($context['view_mode']) ? 'diff_standard' : $context['view_mode'];
   $element = field_view_field($context['entity_type'], $entity, $context['field']['field_name'], $view_mode, $langcode);

   foreach (element_children($element) as $delta) {
     $diff_items[$delta] = drupal_render($element[$delta]);
   }
  return $diff_items;
}

/**
 * Helper function to get the settings for a given field or formatter.
 *
 * @param array $context
 *   This will get the settings for a field.
 *   - field (required): The field that the items belong to.
 *   - entity: The entity that we are looking up.
 *   - instance: The instance that the items belong to.
 *   - view_mode: The view mode to use. Defaults to FALSE.
 *
 * @return array
 *   The settings for this field type.
 */
function diff_get_field_settings($field_context) {
  $field = $field_context['field'];

  // Update saved settings from the global settings for this field type.
  $settings = variable_get("diff_{$field['module']}_field_{$field['type']}_default_options", array());

  $settings = _diff_field_default_settings($field['module'], $field['type'], $settings);

  // Allow modules to alter the field settings based on the current context.
  drupal_alter('diff_field_settings', $settings, $field_context);

  return $settings;
}

/**
 * Helper function to initiate any global form elements.
 */
function diff_global_settings_form(&$subform, $form_state, $type, $settings) {
  $subform['show_header'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show field title'),
    '#default_value' => $settings['show_header'],
    '#weight' => -5,
  );
  $subform['markdown'] = array(
    '#type' => 'select',
    '#title' => t('Markdown callback'),
    '#default_value' => $settings['markdown'],
    '#options' => array(
      'drupal_html_to_text' => t('Drupal HTML to Text'),
      'filter_xss' => t('Filter XSS (some tags)'),
      'diff_filter_xss' => t('Filter XSS (all tags)'),
    ),
    '#description' => t('These provide ways to clean markup tags to make comparisons easier to read.'),
    '#empty_option' => t('- Do not process -'),
  );
  $subform['line_counter'] = array(
    '#type' => 'radios',
    '#title' => t('Line counter'),
    '#default_value' => $settings['line_counter'],
    '#description' => t('This outputs the (approximate) line numbers as a heading before every change.'),
    '#options' => array(
      '' => t('None. Counter ignore and not incremented.'),
      'hidden' => t('Count lines but do not show line headers.'),
      'line' => t('Count and show lines, restarting counter at 0.'),
      'line_continuous' => t('Count and show lines, incrementing counter from last item.'),
    ),
  );

  /*
This would be cool, but to do anything else than inline with the text appears
to be very hard, requiring a refactoring of both the modules API but also the
DiffFormatter and Diff classes. Diff 8.x-4.x maybe.

  $subform['show_delta'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show delta values'),
    '#default_value' => $settings['show_delta'],
  );
  $subform['delta_format'] = array(
    '#type' => 'radios',
    '#title' => t('Delta insertion method'),
    '#default_value' => $settings['delta_format'],
    '#options' => array(
      'inline' => t('Prefix to item'),
      'row' => t('Individual row'),
    ),
    '#states' => array(
      'invisible' => array(
        "input[id$='show-delta']" => array('checked' => FALSE),
      ),
    ),
  );
  */
}

/**
 * Helper function to populate the settings array.
 */
function _diff_field_default_settings($module, $field_type, $settings = array()) {
  // Load files containing the field callbacks.
  _diff_autoload($module);

  // Populate any missing values from CALLBACK_field_diff_default_options().
  $func = $module . '_field_diff_default_options';
  if (function_exists($func)) {
    $settings += $func($field_type);
  }

  // Check for Diff support. If it doesn't exist, the default markdown should
  // escape the field display, otherwise a raw format should be used.
  $func = $module . '_field_diff_view';

  // Global settings.
  $settings += array(
    'markdown' => function_exists($func) ? '' : 'drupal_html_to_text',
    'line_counter' => '',
    'show_header' => 1,
    // Can we? This seems too hard to track in the DiffFormatter as all we
    // have is a string or an array of strings.
    //'show_delta' => 0,
    //'delta_format' => 'row',
  );
  return $settings;
}

/**
 * Private helper function to load field includes.
 *
 * @param array|string $field_or_module
 *   The field definition array or the module that implements the field.
 */
function _diff_autoload($field_or_module) {
  $includes = &drupal_static(__FUNCTION__, FALSE);
  if (!$includes) {
    $includes = array(
      'file' => module_exists('file'),
      'image' => module_exists('image'),
      'list' => module_exists('list'),
      'taxonomy' => module_exists('taxonomy'),
      'text' => module_exists('text'),
      'number' => module_exists('number'),
    );
  }

  $module = is_string($field_or_module) ? $field_or_module : $field_or_module['module'];

  // Since field hooks are not real hooks, we manually load the field modules
  // MODULE.diff.inc. We handle the five core field defining modules.
  if (!isset($includes[$module])) {
    module_load_include('diff.inc', $module);
    $includes[$module] = 0;
  }
  elseif (!empty($includes[$module])) {
    module_load_include('inc', 'diff', 'includes/' . $module);
    $includes[$module] = 0;
  }
}

/**
 * Helper function to parse out the state in the diff results.
 */
function diff_extract_state($diff, $state = 'raw') {
  $states = array(
    0 => NULL,
    1 => NULL,
  );
  if (isset($diff['#states'][$state])) {
    if (isset($diff['#states'][$state]['#old'])) {
      $states[0] = $diff['#states'][$state]['#old'];
    }
    if (isset($diff['#states'][$state]['#new'])) {
      $states[1] = $diff['#states'][$state]['#new'];
    }
  }
  return $states;
}
