<?php

/**
 * @file
 * dkan_sitewide.features.filter.inc
 */

/**
 * Implements hook_filter_default_formats().
 */
function dkan_sitewide_filter_default_formats() {
  $formats = array();

  // Exported format: Markdown HTML.
  $formats['html'] = array(
    'format' => 'html',
    'name' => 'Markdown HTML',
    'cache' => 1,
    'status' => 1,
    'weight' => 0,
    'filters' => array(
      'filter_markdown' => array(
        'weight' => -50,
        'status' => 1,
        'settings' => array(),
      ),
      'filter_html' => array(
        'weight' => -48,
        'status' => 1,
        'settings' => array(
          'allowed_html' => '<a> <em> <strong> <cite> <blockquote> <code> <ul> <ol> <li> <dl> <dt> <dd> <p><img><br><h2><h3><center><iframe>',
          'filter_html_help' => 1,
          'filter_html_nofollow' => 0,
        ),
      ),
      'filter_url' => array(
        'weight' => -45,
        'status' => 1,
        'settings' => array(
          'filter_url_length' => 72,
        ),
      ),
      'filter_htmlcorrector' => array(
        'weight' => -44,
        'status' => 1,
        'settings' => array(),
      ),
      'filter_autop' => array(
        'weight' => 0,
        'status' => 1,
        'settings' => array(),
      ),
    ),
  );

  return $formats;
}
