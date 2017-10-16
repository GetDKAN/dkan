<?php

/**
 * @file
 * Theme and preprocess functions for breadcrumbs, messages, tabs..etc.
 */

/**
 * Implements hook_css_alter().
 */
function nuboot_radix_css_alter(&$css) {
  // Remove dkan button styles so we can use our own.
  unset($css[drupal_get_path('module', 'dkan_dataset') . '/css/dkan_dataset_btn.css']);
}

/**
 * Implements template_preprocess_page().
 */
function nuboot_radix_preprocess_page(&$variables) {
  $variables['display_login_menu'] = (theme_get_setting('display_login_menu') === NULL) ? 1 : theme_get_setting('display_login_menu');
  $logo = theme_get_setting('toggle_logo');
  $theme = variable_get('theme_default');
  $default = theme_get_setting('default_logo');
  $logo_fid = theme_get_setting('svg_logo');
  $logo_file = !empty($logo_fid) ? file_load($logo_fid) : FALSE;
  if ($logo) {
    if ($default) {
      $svg = drupal_get_path('theme', $theme) . '/logo.svg';
      if (file_exists($svg)) {
        $variables['logo'] = '/' . drupal_get_path('theme', $theme) . '/logo.svg';
      }
      else {
        $variables['logo'] = '/' . drupal_get_path('theme', $theme) . '/logo.png';
      }
    }
    elseif ($logo_file) {
      $logo_uri = $logo_file->uri;
      $variables['logo'] = file_create_url($logo_uri);
    }
    else {
      $variables['logo'] = file_create_url(theme_get_setting('logo_path'));
    }
  }
  // Add copyright to theme.
  if ($copyright = theme_get_setting('copyright')) {
    $variables['copyright'] = isset($copyright['value']) ? check_markup($copyright['value'], 'html') : t('Powered by <a href="http://getdkan.com/">DKAN</a>');
  }
  // Use front page template for node front page.
  $alias = drupal_get_path_alias($_GET['q']);
  if ($alias == 'welcome') {
    $variables['is_front'] = TRUE;
  }
  if (drupal_is_front_page()) {
    $variables['title'] = '';
    unset($variables['page']['content']['system_main']['default_message']);
  }
  // Add external css files.
  $external_css = array(
    '#attached' => array(
      'css' => array(
        '//fonts.googleapis.com/css?family=Open+Sans::400,300,700' => array(
          'type' => 'external',
        ),
      ),
    ),
  );
  drupal_render($external_css);
}
