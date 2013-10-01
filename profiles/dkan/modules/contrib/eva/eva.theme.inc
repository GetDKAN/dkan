<?php

/**
 * @file
 * Preprocess handlers for theme functions
 */

function template_preprocess_eva_display_entity_view(&$vars) {
  template_preprocess_views_view($vars);
  $view = $vars['view'];
  $display = $view->display_handler;
  $vars['title'] = $display->get_option('show_title') ? filter_xss_admin($view->get_title()) : '';
}

function template_process_eva_display_entity_view(&$vars) {
  template_process_views_view($vars);
}

