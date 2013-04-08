<?php
/**
 * @file
 * Views style plugin to render nodes in the RDF data format.
 *
 * @see views-view-rdf.tpl.php
 * @ingroup views_plugins
 */

/**
 * Implements hook_views_plugins().
 */
function views_rdf_views_plugins() {
  $formats = views_rdf_views_formats();
  $additional_themes = array();
  foreach ($formats as $format => $description) {
    $additional_themes['views_views_rdf_style_' . $format] = 'style';
  }

  $path = drupal_get_path('module', 'views_rdf') . '/views';
  return array(
    'module' => 'views_rdf',
    'style' => array(
      'views_rdf' => array(
        'title'           => t('RDF data document'),
        'path'            => $path . '/plugins',
        'help'            => t('Displays nodes in a view using the RDF data format.'),
        'handler'         => 'views_plugin_style_rdf',
        'theme'           => 'views_views_rdf_style',
        'theme file'      => 'views_views_rdf_style.theme.inc',
        'theme path'      => $path . '/theme',
        'additional themes' => $additional_themes,
        'uses row plugin' => FALSE,
        'uses fields'     => TRUE,
        'uses options'    => TRUE,
        'type'            => 'normal',
        'help_topic'      => 'style-rdf',
        'even empty'      => TRUE,
      ),
    ),
  );
}

/**
 * All recognised RDF output formats
 */
function views_rdf_views_formats() {
  return module_invoke_all('views_rdf_formats');
}

/**
 * Implements hook_views_rdf_formats().
 */
function views_rdf_views_rdf_formats() {
  $formats = array(
    'foaf' => t('FOAF'),
    'sioc' => t('SIOC'),
    'doap' => t('DOAP'),
  );
  return $formats;
}
