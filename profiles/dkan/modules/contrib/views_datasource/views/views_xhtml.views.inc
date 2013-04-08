<?php
/**
 * @file
 * Views style plugin to render nodes as XHTML microformats.
 *
 * @see views_plugin_style_xhtml.inc
 * @ingroup views_plugins
 */

/**
 * Implements hook_views_plugins().
 */
function views_xhtml_views_plugins() {
  $formats = views_xhtml_views_formats();
  $additional_themes = array();
  foreach ($formats as $format => $description) {
    $additional_themes['views_views_xhtml_style_' . $format] = 'style';
  }

  $path = drupal_get_path('module', 'views_xhtml') . '/views';
  return array(
    'module' => 'views_xhtml',
    'style' => array(
      'views_xhtml' => array(
        'title'           => t('XHTML data document'),
        'path'            => $path . '/plugins',
        'help'            => t('Displays nodes as XHTML microformats.'),
        'handler'         => 'views_plugin_style_xhtml',
        'theme'           => 'views_views_xhtml_style',
        'theme file'      => 'views_views_xhtml_style.theme.inc',
        'theme path'      => $path . '/theme',
        'additional themes' => $additional_themes,
        'uses row plugin' => FALSE,
        'uses fields'     => TRUE,
        'uses options'    => TRUE,
        'type'            => 'normal',
        'help_topic'      => 'style-xhtml',
        'even empty'      => TRUE,
      ),
    ),
  );
}

/**
 * All recognised XHTML output formats
 */
function views_xhtml_views_formats() {
  return module_invoke_all('views_xhtml_formats');
}

/**
 * Implements hook_views_xhtml_formats().
 */
function views_xhtml_views_xhtml_formats() {
  $formats = array(
    'hcard' => t('hCard'),
    'hcalendar' => t('hCalendar'),
  );
  return $formats;
}
