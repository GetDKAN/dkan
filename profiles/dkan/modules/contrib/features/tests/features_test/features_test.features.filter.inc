<?php
/**
 * @file
 * features_test.features.filter.inc
 */

/**
 * Implements hook_filter_default_formats().
 */
function features_test_filter_default_formats() {
  $formats = array();

  // Exported format: features_test.
  $formats['features_test'] = array(
    'format' => 'features_test',
    'name' => 'features_test',
    'cache' => 1,
    'status' => 1,
    'weight' => 0,
    'filters' => array(
      'filter_autop' => array(
        'weight' => 10,
        'status' => 1,
        'settings' => array(),
      ),
      'filter_html' => array(
        'weight' => 10,
        'status' => 1,
        'settings' => array(
          'allowed_html' => '<a> <em> <strong> <cite> <blockquote> <code> <ul> <ol> <li> <dl> <dt> <dd>',
          'filter_html_help' => 1,
          'filter_html_nofollow' => 0,
        ),
      ),
      'filter_htmlcorrector' => array(
        'weight' => 10,
        'status' => 1,
        'settings' => array(),
      ),
      'filter_html_escape' => array(
        'weight' => 10,
        'status' => 1,
        'settings' => array(),
      ),
      'filter_url' => array(
        'weight' => 10,
        'status' => 1,
        'settings' => array(
          'filter_url_length' => 72,
        ),
      ),
    ),
  );

  return $formats;
}
