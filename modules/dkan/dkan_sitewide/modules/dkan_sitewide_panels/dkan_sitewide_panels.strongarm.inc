<?php
/**
 * @file
 * dkan_sitewide_panels.strongarm.inc
 */

/**
 * Implements hook_strongarm().
 */
function dkan_sitewide_panels_strongarm() {
  $export = array();

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'path_breadcrumbs_cache_enabled';
  $strongarm->value = 1;
  $export['path_breadcrumbs_cache_enabled'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'path_breadcrumbs_cache_lifetime';
  $strongarm->value = '-1';
  $export['path_breadcrumbs_cache_lifetime'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'path_breadcrumbs_decode_entities';
  $strongarm->value = 1;
  $export['path_breadcrumbs_decode_entities'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'path_breadcrumbs_delimiter';
  $strongarm->value = '»';
  $export['path_breadcrumbs_delimiter'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'path_breadcrumbs_hide_single_breadcrumb';
  $strongarm->value = 0;
  $export['path_breadcrumbs_hide_single_breadcrumb'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'path_breadcrumbs_home_link_enabled';
  $strongarm->value = 0;
  $export['path_breadcrumbs_home_link_enabled'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'path_breadcrumbs_home_link_title';
  $strongarm->value = 'Home';
  $export['path_breadcrumbs_home_link_title'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'path_breadcrumbs_internal_render';
  $strongarm->value = 0;
  $export['path_breadcrumbs_internal_render'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'path_breadcrumbs_internal_render_themes';
  $strongarm->value = array();
  $export['path_breadcrumbs_internal_render_themes'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'path_breadcrumbs_rich_snippets';
  $strongarm->value = 0;
  $export['path_breadcrumbs_rich_snippets'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'path_breadcrumbs_truncate_title_length';
  $strongarm->value = '';
  $export['path_breadcrumbs_truncate_title_length'] = $strongarm;

  $strongarm = new stdClass();
  $strongarm->disabled = FALSE; /* Edit this to true to make a default strongarm disabled initially */
  $strongarm->api_version = 1;
  $strongarm->name = 'path_breadcrumbs_url_cleaning_method';
  $strongarm->value = 'none';
  $export['path_breadcrumbs_url_cleaning_method'] = $strongarm;

  return $export;
}
