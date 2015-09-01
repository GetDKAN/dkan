<?php
/**
 * @file
 * dkan_workflow.views_default.inc
 */

/**
 * Implementes hook_views_default_views_alter(&$views).
 *
 * Load all views from /views folder. Overriding any existing views if any.
 */
function dkan_workflow_views_default_views_alter(&$views) {
  $files = file_scan_directory(drupal_get_path('module', 'dkan_workflow'). '/views', '/.*\.view.inc$/');
  foreach ($files as $filepath => $file) {
    require $filepath;
    if (isset($view)) {
      $views[$view->name] = $view;
    }
  }
}
