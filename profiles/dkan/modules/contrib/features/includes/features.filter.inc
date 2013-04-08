<?php

/**
 * Implements hook_features_api().
 */
function filter_features_api() {
  return array(
    'filter' => array(
      'name' => t('Text formats'),
      'default_hook' => 'filter_default_formats',
      'default_file' => FEATURES_DEFAULTS_INCLUDED,
      'feature_source' => TRUE
    ),
  );
}

/**
 * Implements hook_features_export_options().
 */
function filter_features_export_options() {
  $options = array();
  foreach (filter_formats() as $format => $info) {
    $options[$format] = $info->name;
  }
  return $options;
}

/**
 * Implements hook_features_export().
 */
function filter_features_export($data, &$export, $module_name = '') {
  // The filter_default_formats() hook integration is provided by the
  // features module so we need to add it as a dependency.
  $export['dependencies']['features'] = 'features';

  $filter_info = filter_get_filters();
  foreach ($data as $name) {
    if ($format = features_filter_format_load($name)) {
      // Add format to exports
      $export['features']['filter'][$format->format] = $format->format;

      // Iterate through filters and ensure each filter's module is included as a dependency
      foreach (array_keys($format->filters) as $name) {
        if (isset($filter_info[$name], $filter_info[$name]['module'])) {
          $module = $filter_info[$name]['module'];
          $export['dependencies'][$module] = $module;
        }
      }
    }
  }

  $pipe = array();
  return $pipe;
}

/**
 * Implements hook_features_export_render().
 */
function filter_features_export_render($module, $data, $export = NULL) {
  $code = array();
  $code[] = '  $formats = array();';
  $code[] = '';

  foreach ($data as $name) {
    if ($format = features_filter_format_load($name)) {
      $format_export = features_var_export($format, '  ');
      $format_identifier = features_var_export($format->format);
      $code[] = "  // Exported format: {$format->name}.";
      $code[] = "  \$formats[{$format_identifier}] = {$format_export};";
      $code[] = "";
    }
  }

  $code[] = '  return $formats;';
  $code = implode("\n", $code);
  return array('filter_default_formats' => $code);
}

/**
 * Implements hook_features_revert().
 */
function filter_features_revert($module) {
  return filter_features_rebuild($module);
}

/**
 * Implements hook_features_rebuild().
 */
function filter_features_rebuild($module) {
  if ($defaults = features_get_default('filter', $module)) {
    foreach ($defaults as $format) {
      $format = (object) $format;
      filter_format_save($format);
    }
  }
}

/**
 * Load a filter format by its name.
 */
function features_filter_format_load($name) {
  // Use machine name for retrieving the format if available.
  $query = db_select('filter_format');
  $query->fields('filter_format');
  $query->condition('format', $name);

  // Retrieve filters for the format and attach.
  if ($format = $query->execute()->fetchObject()) {
    $format->filters = array();
    foreach (filter_list_format($format->format) as $filter) {
      if (!empty($filter->status)) {
        $format->filters[$filter->name]['weight'] = $filter->weight;
        $format->filters[$filter->name]['status'] = $filter->status;
        $format->filters[$filter->name]['settings'] = $filter->settings;
      }
    }
    return $format;
  }
  return FALSE;
}
