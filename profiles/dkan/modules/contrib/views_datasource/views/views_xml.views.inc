<?php
/**
 * @file
 * Views style plugins to render nodes in the XML data format.
 *
 * @see views_plugin_style_xml.inc
 * @ingroup views_plugins
 */

/**
 * Implements hook_views_plugins().
 */
function views_xml_views_plugins() {
  $formats = views_xml_views_formats();
  $additional_themes = array();
  foreach ($formats as $format => $description) {
    $additional_themes['views_views_xml_style_' . $format] = 'style';
  }

  $path = drupal_get_path('module', 'views_xml') . '/views';
  return array(
    'module' => 'views_xml',
    'style' => array(
      'views_xml_style' => array(
        'title'           => t('XML data document'),
        'path'            => $path . '/plugins',
        'help'            => t('Display a view as an XML document.'),
        'handler'         => 'views_plugin_style_xml',
        'theme'           => 'views_views_xml_style',
        'theme file'      => 'views_views_xml_style.theme.inc',
        'theme path'      => $path . '/theme',
        'additional themes' => $additional_themes,
        'uses row plugin' => FALSE,
        'uses fields'     => TRUE,
        'uses options'    => TRUE,
        'type'            => 'normal',
        'help_topic'      => 'style-xml',
        'even empty'      => TRUE,
      ),
    ),
  );
}

/**
 * All recognised XML output formats
 */
function views_xml_views_formats() {
  return module_invoke_all('views_xml_formats');
}

/**
 * Implements hook_views_xml_formats().
 */
function views_xml_views_xml_formats() {
  $formats = array(
    'raw' => t('Raw XML'),
    'opml' => t('OPML'),
    'atom' => t('Atom'),
  );
  return $formats;
}
