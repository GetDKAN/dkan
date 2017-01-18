<?php

/**
 * @file
 * dkan_topics.facetapi_defaults.inc
 */

/**
 * Implements hook_facetapi_default_facet_settings().
 */
function dkan_topics_facetapi_default_facet_settings() {
  $export = array();

  $facet = new stdClass();
  $facet->disabled = FALSE; /* Edit this to true to make a default facet disabled initially */
  $facet->api_version = 1;
  $facet->name = 'search_api@datasets:block:field_topic';
  $facet->searcher = 'search_api@datasets';
  $facet->realm = 'block';
  $facet->facet = 'field_topic';
  $facet->enabled = TRUE;
  $facet->settings = array(
    'weight' => 0,
    'widget' => 'terms',
    'filters' => array(
      'active_items' => array(
        'status' => 0,
        'weight' => '-50',
      ),
      'current_depth' => array(
        'status' => 0,
        'weight' => '-49',
      ),
      'exclude_items' => array(
        'status' => 0,
        'weight' => '-48',
      ),
      'rewrite_items' => array(
        'status' => 1,
        'weight' => '-47',
      ),
      'narrow_results' => array(
        'status' => 1,
        'weight' => '-46',
      ),
      'show_if_minimum_items' => array(
        'status' => 0,
        'weight' => '-45',
      ),
      'deepest_level_items' => array(
        'status' => 0,
        'weight' => '-44',
      ),
    ),
    'active_sorts' => array(
      'active' => 'active',
      'count' => 'count',
      'display' => 'display',
      'natural' => 0,
      'indexed' => 0,
    ),
    'sort_weight' => array(
      'active' => '-50',
      'count' => '-49',
      'display' => '-48',
      'natural' => '0',
      'indexed' => '0',
    ),
    'sort_order' => array(
      'active' => '3',
      'count' => '3',
      'display' => '4',
      'natural' => '4',
      'indexed' => '4',
    ),
    'empty_behavior' => 'none',
    'soft_limit' => '20',
    'nofollow' => 1,
    'show_expanded' => 0,
    'empty_text' => array(
      'value' => '',
      'format' => 'html',
    ),
    'pretty_paths_alias' => 'field_topic',
    'pretty_paths_taxonomy_pathauto' => 0,
    'pretty_paths_taxonomy_pathauto_vocabulary' => 'dkan_topics',
    'submit_realm' => 'Save and go back to realm settings',
    'exclude' => '',
    'regex' => 0,
    'show_minimum_items' => 2,
    'rewrite_items' => 1,
  );
  $export['search_api@datasets:block:field_topic'] = $facet;

  return $export;
}
