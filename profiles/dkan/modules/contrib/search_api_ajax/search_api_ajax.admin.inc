<?php

/**
 * @file Administrative functions for Search API AJAX
 */

/*
 * Settings callback
 */
function search_api_ajax_settings() {
  $form = array();
  $pages = variable_get('search_api_ajax_pages', FALSE);
  $visibility = variable_get('search_api_ajax_visibility', SEARCH_API_AJAX_VISIBILITY_NOTLISTED);

  // Per-path visibility.
  $form['path'] = array(
    '#type' => 'fieldset',
    '#title' => t('Pages'),
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#weight' => 0,
  );
  $form['path']['search_api_ajax_visibility'] = array(
    '#type' => 'radios',
    '#title' => t('Show AJAX scripts on specific pages'),
    '#options' => array(
      SEARCH_API_AJAX_VISIBILITY_NOTLISTED => t('All pages except those listed'),
      SEARCH_API_AJAX_VISIBILITY_LISTED => t('Only the listed pages'),
    ),
    '#default_value' => isset($pages) ? $visibility : SEARCH_API_AJAX_VISIBILITY_NOTLISTED,
  );
  $form['path']['search_api_ajax_pages'] = array(
    '#type' => 'textarea',
    '#title' => '<span class="element-invisible">' . t('Pages') . '</span>',
    '#default_value' => isset($pages) ? $pages : '',
    '#description' => t("Specify pages by using their paths. Enter one path per line. The '*' character is a wildcard. Example paths are %blog for the blog page and %blog-wildcard for every personal blog. %front is the front page.", array(
      '%blog' => 'blog',
      '%blog-wildcard' => 'blog/*',
      '%front' => '<front>'
    )),
  );
  return system_settings_form($form);
}
