<?php
/**
 * @file
 * Views style plugin to render nodes in the JSON data format.
 *
 * @see views_plugin_style_json.inc
 * @ingroup views_plugins
 */

/**
 * Implements hook_views_plugins().
 */
function views_json_views_plugins() {
  $formats = views_json_views_formats();
  $additional_themes = array();
  foreach ($formats as $format => $description) {
    $additional_themes['views_views_json_style_' . $format] = 'style';
  }

  $path = drupal_get_path('module', 'views_json') . '/views';
  return array(
    'module' => 'views_json',
    'style' => array(
      'views_json' => array(
        'title'           => t('JSON data document'),
        'path'            => $path . '/plugins',
        'help'            => t('Displays nodes in the JSON data format.'),
        'handler'         => 'views_plugin_style_json',
        'theme'           => 'views_views_json_style',
        'theme file'      => 'views_views_json_style.theme.inc',
        'theme path'      => $path . '/theme',
        'additional themes' => $additional_themes,
        'uses row plugin' => FALSE,
        'uses fields'     => TRUE,
        'uses options'    => TRUE,
        'type'            => 'normal',
        'help_topic'      => 'style-json',
        'even empty'      => TRUE,
      ),
    ),
  );
}

/*
 * All recognised JSON output formats
 */
function views_json_views_formats() {
  return module_invoke_all('views_json_formats');
}

/**
 * Implements hook_views_json_formats().
 *
 * hook implementation should simply return an array,
 * module_invoke_all() will merge them into one array.
 * To support additional output formats, do the following:
 * 1. Implement this hook.
 * 2. Implement template_preprocess_views_views_json_style_XXXX (see
 *    example in theme/views_views_json_style.theme.inc) in your module, make sure it is loaded!
 * 3. Create a views-views-json-style-XXXX.tpl.php file in
 *    views_datasource/theme by copying
 *    views-views-json-style-simple.tpl.php. Unfortunately this cannot
 *    be in your module it appears.
 */
function views_json_views_json_formats() {
  $formats = array(
    'simple' => t('Simple'),
    'exhibit' => t('MIT Simile/Exhibit'),
    'jqgrid' => t('To be consumed by jqGrid'),
  );
  return $formats;
}
