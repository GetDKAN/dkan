<?php
/**
 * @file
 * dkan_harvest.path_breadcrumbs.inc
 */

/**
 * Implements hook_path_breadcrumbs_settings_info().
 */
function dkan_harvest_path_breadcrumbs_settings_info() {
  $export = array();

  $path_breadcrumb = new stdClass();
  $path_breadcrumb->api_version = 1;
  $path_breadcrumb->machine_name = 'harvest_source';
  $path_breadcrumb->name = 'harvest_source';
  $path_breadcrumb->path = 'node/%node';
  $path_breadcrumb->data = array(
    'titles' => array(
      0 => 'Harvest Sources',
      1 => '%node:title',
    ),
    'paths' => array(
      0 => 'admin/dkan/harvest/dashboard',
      1 => '<none>',
    ),
    'home' => 1,
    'translatable' => 0,
    'arguments' => array(
      'node' => array(
        'position' => 1,
        'argument' => 'entity_id:node',
        'settings' => array(
          'identifier' => 'Node: ID',
        ),
      ),
    ),
    'access' => array(
      'plugins' => array(
        0 => array(
          'name' => 'entity_bundle:node',
          'settings' => array(
            'type' => array(
              'harvest_source' => 'harvest_source',
            ),
          ),
          'context' => 'node',
          'not' => FALSE,
        ),
      ),
      'logic' => 'and',
    ),
  );
  $path_breadcrumb->weight = 0;
  $export['harvest_source'] = $path_breadcrumb;

  $path_breadcrumb = new stdClass();
  $path_breadcrumb->api_version = 1;
  $path_breadcrumb->machine_name = 'harvest_source_pages';
  $path_breadcrumb->name = 'harvest_source_pages';
  $path_breadcrumb->path = 'node/%node/%section';
  $path_breadcrumb->data = array(
    'titles' => array(
      0 => 'Harvest Sources',
      1 => '%node:title',
      2 => '!page_title',
    ),
    'paths' => array(
      0 => 'admin/dkan/harvest/dashboard',
      1 => '%node:url',
      2 => '<none>',
    ),
    'home' => 1,
    'translatable' => 0,
    'arguments' => array(
      'node' => array(
        'position' => 1,
        'argument' => 'entity_id:node',
        'settings' => array(
          'identifier' => 'Node: ID',
        ),
      ),
      'section' => array(
        'position' => 2,
      ),
    ),
    'access' => array(
      'plugins' => array(
        0 => array(
          'name' => 'entity_bundle:node',
          'settings' => array(
            'type' => array(
              'harvest_source' => 'harvest_source',
            ),
          ),
          'context' => 'node',
          'not' => FALSE,
        ),
      ),
      'logic' => 'and',
    ),
  );
  $path_breadcrumb->weight = 0;
  $export['harvest_source_pages'] = $path_breadcrumb;

  return $export;
}
