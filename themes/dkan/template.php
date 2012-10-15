<?php

/**
 * @file
 * Customizations for DKAN.
 */

/**
 * Implements theme_breadcrumb().
 */
function dkan_breadcrumb($variables) {
  $breadcrumb = $variables['breadcrumb'];
  $contexts = array();

  if (!empty($breadcrumb)) {
    // Custom breadcrumb elements for specific contexts.
    if (module_exists('context')) {
      $contexts = context_active_contexts();
    }
    if (isset($contexts['dataset'])) {
      $first = array_slice($breadcrumb, 0, 1, true);
      $count = count($breadcrumb);
      $datasets = array($count => '<a href="/dataset">Datasets</a>');
      $rest = array_slice($breadcrumb, 1, count($breadcrumb) -1, true);
      $breadcrumb =  $first + $datasets + $rest;
    }
    $output = '<h2 class="element-invisible">' . t('You are here') . '</h2>';

    $crumbs = '<ul class="breadcrumb">';

    foreach($breadcrumb as $value) {
      $crumbs .= '<li>'.$value.'</li>';
    }
    $crumbs .= '</ul>';
    return $crumbs;
  }
}
