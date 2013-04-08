<?php


/**
 * @file
 * Provides support for the Views module.
 */

/**
 * Implements hook_views_plugins().
 */
function og_context_views_plugins() {
  return array(
    'argument default' => array(
      'og_context' => array(
        'title' => t('Current OG group from context'),
        'handler' => 'og_context_plugin_argument_default_group_context',
        'parent' => 'views_plugin_argument_default',
      ),
    ),
    'access' => array(
      'og_context' => array(
        'title' => t('OG permission'),
        'handler' => 'og_context_plugin_access_og_perm',
        'uses options' => TRUE,
      ),
    ),
  );
}