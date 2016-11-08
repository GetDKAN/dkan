<?php

/**
 * @file
 * dkan_sitewide_search_db.facetapi_defaults.inc
 */

/**
 * Implements hook_facetapi_default_facet_settings().
 */
function dkan_sitewide_search_db_facetapi_default_facet_settings() {
  $export = array();

  $facet = new stdClass();
  $facet->disabled = FALSE; /* Edit this to true to make a default facet disabled initially */
  $facet->api_version = 1;
  $facet->name = 'search_api@datasets::type';
  $facet->searcher = 'search_api@datasets';
  $facet->realm = '';
  $facet->facet = 'type';
  $facet->enabled = FALSE;
  $facet->settings = array(
    'operator' => 'and',
    'hard_limit' => '50',
    'dependencies' => array(
      'facets' => array(),
      'force_deactivation' => TRUE,
      'regex' => FALSE,
    ),
    'facet_mincount' => '1',
    'facet_missing' => '0',
    'flatten' => 0,
    'query_type' => 'term',
    'default_true' => TRUE,
    'facet_search_ids' => array(),
    'exclude' => 0,
    'pretty_paths_alias' => 'type',
  );
  $export['search_api@datasets::type'] = $facet;

  $facet = new stdClass();
  $facet->disabled = FALSE; /* Edit this to true to make a default facet disabled initially */
  $facet->api_version = 1;
  $facet->name = 'search_api@datasets:block:author';
  $facet->searcher = 'search_api@datasets';
  $facet->realm = 'block';
  $facet->facet = 'author';
  $facet->enabled = TRUE;
  $facet->settings = array(
    'weight' => 0,
    'widget' => 'facetapi_links',
    'filters' => array(
      'active_items' => array(
        'status' => 0,
        'weight' => '-50',
      ),
      'exclude_items' => array(
        'status' => 0,
        'weight' => '-49',
      ),
      'rewrite_items' => array(
        'status' => 1,
        'weight' => '-48',
      ),
      'narrow_results' => array(
        'status' => 0,
        'weight' => '-47',
      ),
      'show_if_minimum_items' => array(
        'status' => 0,
        'weight' => '-46',
      ),
      'deepest_level_items' => array(
        'status' => 0,
        'weight' => '-45',
      ),
    ),
    'active_sorts' => array(
      'active' => 'active',
      'count' => 'count',
      'display' => 'display',
    ),
    'sort_weight' => array(
      'active' => -50,
      'count' => -49,
      'display' => -48,
    ),
    'sort_order' => array(
      'active' => 3,
      'count' => 3,
      'display' => 4,
    ),
    'empty_behavior' => 'none',
    'soft_limit' => 20,
    'nofollow' => 1,
    'show_expanded' => 0,
    'rewrite_items' => 1,
  );
  $export['search_api@datasets:block:author'] = $facet;

  $facet = new stdClass();
  $facet->disabled = FALSE; /* Edit this to true to make a default facet disabled initially */
  $facet->api_version = 1;
  $facet->name = 'search_api@datasets:block:field_format';
  $facet->searcher = 'search_api@datasets';
  $facet->realm = 'block';
  $facet->facet = 'field_format';
  $facet->enabled = TRUE;
  $facet->settings = array(
    'weight' => 0,
    'widget' => 'facetapi_links',
    'filters' => array(
      'active_items' => array(
        'status' => 0,
        'weight' => '-50',
      ),
      'exclude_items' => array(
        'status' => 0,
        'weight' => '-49',
      ),
      'rewrite_items' => array(
        'status' => 1,
        'weight' => '-48',
      ),
      'narrow_results' => array(
        'status' => 0,
        'weight' => '-47',
      ),
      'show_if_minimum_items' => array(
        'status' => 0,
        'weight' => '-46',
      ),
      'deepest_level_items' => array(
        'status' => 0,
        'weight' => '-45',
      ),
    ),
    'active_sorts' => array(
      'active' => 'active',
      'count' => 'count',
      'display' => 'display',
    ),
    'sort_weight' => array(
      'active' => -50,
      'count' => -49,
      'display' => -48,
    ),
    'sort_order' => array(
      'active' => 3,
      'count' => 3,
      'display' => 4,
    ),
    'empty_behavior' => 'none',
    'soft_limit' => 20,
    'nofollow' => 1,
    'show_expanded' => 0,
    'rewrite_items' => 1,
  );
  $export['search_api@datasets:block:field_format'] = $facet;

  $facet = new stdClass();
  $facet->disabled = FALSE; /* Edit this to true to make a default facet disabled initially */
  $facet->api_version = 1;
  $facet->name = 'search_api@datasets:block:field_license';
  $facet->searcher = 'search_api@datasets';
  $facet->realm = 'block';
  $facet->facet = 'field_license';
  $facet->enabled = TRUE;
  $facet->settings = array(
    'weight' => 0,
    'widget' => 'facetapi_links',
    'filters' => array(
      'active_items' => array(
        'status' => 0,
        'weight' => '-50',
      ),
      'exclude_items' => array(
        'status' => 0,
        'weight' => '-49',
      ),
      'rewrite_items' => array(
        'status' => 1,
        'weight' => '-48',
      ),
      'narrow_results' => array(
        'status' => 0,
        'weight' => '-47',
      ),
      'show_if_minimum_items' => array(
        'status' => 0,
        'weight' => '-46',
      ),
      'deepest_level_items' => array(
        'status' => 0,
        'weight' => '-45',
      ),
    ),
    'active_sorts' => array(
      'active' => 'active',
      'count' => 'count',
      'display' => 'display',
    ),
    'sort_weight' => array(
      'active' => -50,
      'count' => -49,
      'display' => -48,
    ),
    'sort_order' => array(
      'active' => 3,
      'count' => 3,
      'display' => 4,
    ),
    'empty_behavior' => 'none',
    'soft_limit' => 20,
    'nofollow' => 1,
    'show_expanded' => 0,
    'exclude' => '',
    'regex' => 0,
    'show_minimum_items' => 2,
    'rewrite_items' => 1,
  );
  $export['search_api@datasets:block:field_license'] = $facet;

  $facet = new stdClass();
  $facet->disabled = FALSE; /* Edit this to true to make a default facet disabled initially */
  $facet->api_version = 1;
  $facet->name = 'search_api@datasets:block:field_resources:field_format';
  $facet->searcher = 'search_api@datasets';
  $facet->realm = 'block';
  $facet->facet = 'field_resources:field_format';
  $facet->enabled = TRUE;
  $facet->settings = array(
    'weight' => 0,
    'widget' => 'facetapi_links',
    'filters' => array(
      'active_items' => array(
        'status' => 0,
        'weight' => '-50',
      ),
      'exclude_items' => array(
        'status' => 0,
        'weight' => '-49',
      ),
      'rewrite_items' => array(
        'status' => 1,
        'weight' => '-48',
      ),
      'narrow_results' => array(
        'status' => 0,
        'weight' => '-47',
      ),
      'show_if_minimum_items' => array(
        'status' => 0,
        'weight' => '-46',
      ),
      'deepest_level_items' => array(
        'status' => 0,
        'weight' => '-45',
      ),
    ),
    'active_sorts' => array(
      'active' => 'active',
      'count' => 'count',
      'display' => 'display',
    ),
    'sort_weight' => array(
      'active' => -50,
      'count' => -49,
      'display' => -48,
    ),
    'sort_order' => array(
      'active' => 3,
      'count' => 3,
      'display' => 4,
    ),
    'empty_behavior' => 'none',
    'soft_limit' => 20,
    'nofollow' => 1,
    'show_expanded' => 0,
    'rewrite_items' => 1,
  );
  $export['search_api@datasets:block:field_resources:field_format'] = $facet;

  $facet = new stdClass();
  $facet->disabled = FALSE; /* Edit this to true to make a default facet disabled initially */
  $facet->api_version = 1;
  $facet->name = 'search_api@datasets:block:field_tags';
  $facet->searcher = 'search_api@datasets';
  $facet->realm = 'block';
  $facet->facet = 'field_tags';
  $facet->enabled = TRUE;
  $facet->settings = array(
    'weight' => 0,
    'widget' => 'facetapi_links',
    'filters' => array(
      'active_items' => array(
        'status' => 0,
        'weight' => '-50',
      ),
      'exclude_items' => array(
        'status' => 0,
        'weight' => '-49',
      ),
      'rewrite_items' => array(
        'status' => 1,
        'weight' => '-48',
      ),
      'narrow_results' => array(
        'status' => 0,
        'weight' => '-47',
      ),
      'show_if_minimum_items' => array(
        'status' => 0,
        'weight' => '-46',
      ),
      'deepest_level_items' => array(
        'status' => 0,
        'weight' => '-45',
      ),
    ),
    'active_sorts' => array(
      'active' => 'active',
      'count' => 'count',
      'display' => 'display',
    ),
    'sort_weight' => array(
      'active' => -50,
      'count' => -49,
      'display' => -48,
    ),
    'sort_order' => array(
      'active' => 3,
      'count' => 3,
      'display' => 4,
    ),
    'empty_behavior' => 'none',
    'soft_limit' => 20,
    'nofollow' => 1,
    'show_expanded' => 0,
    'rewrite_items' => 1,
  );
  $export['search_api@datasets:block:field_tags'] = $facet;

  $facet = new stdClass();
  $facet->disabled = FALSE; /* Edit this to true to make a default facet disabled initially */
  $facet->api_version = 1;
  $facet->name = 'search_api@datasets:block:og_group_ref';
  $facet->searcher = 'search_api@datasets';
  $facet->realm = 'block';
  $facet->facet = 'og_group_ref';
  $facet->enabled = TRUE;
  $facet->settings = array(
    'weight' => 0,
    'widget' => 'facetapi_links',
    'filters' => array(
      'active_items' => array(
        'status' => 0,
        'weight' => '-50',
      ),
      'exclude_items' => array(
        'status' => 0,
        'weight' => '-49',
      ),
      'rewrite_items' => array(
        'status' => 1,
        'weight' => '-48',
      ),
      'narrow_results' => array(
        'status' => 0,
        'weight' => '-47',
      ),
      'show_if_minimum_items' => array(
        'status' => 0,
        'weight' => '-46',
      ),
      'deepest_level_items' => array(
        'status' => 0,
        'weight' => '-45',
      ),
    ),
    'active_sorts' => array(
      'active' => 'active',
      'count' => 'count',
      'display' => 'display',
    ),
    'sort_weight' => array(
      'active' => -50,
      'count' => -49,
      'display' => -48,
    ),
    'sort_order' => array(
      'active' => 3,
      'count' => 3,
      'display' => 4,
    ),
    'empty_behavior' => 'none',
    'soft_limit' => 20,
    'nofollow' => 1,
    'show_expanded' => 0,
    'rewrite_items' => 1,
  );
  $export['search_api@datasets:block:og_group_ref'] = $facet;

  $facet = new stdClass();
  $facet->disabled = FALSE; /* Edit this to true to make a default facet disabled initially */
  $facet->api_version = 1;
  $facet->name = 'search_api@datasets:block:search_api_language';
  $facet->searcher = 'search_api@datasets';
  $facet->realm = 'block';
  $facet->facet = 'search_api_language';
  $facet->enabled = FALSE;
  $facet->settings = array(
    'weight' => 0,
    'widget' => 'facetapi_links',
    'filters' => array(
      'active_items' => array(
        'status' => 0,
        'weight' => '-50',
      ),
      'exclude_items' => array(
        'status' => 0,
        'weight' => '-49',
      ),
      'rewrite_items' => array(
        'status' => 1,
        'weight' => '-48',
      ),
      'narrow_results' => array(
        'status' => 0,
        'weight' => '-47',
      ),
      'show_if_minimum_items' => array(
        'status' => 0,
        'weight' => '-46',
      ),
      'deepest_level_items' => array(
        'status' => 0,
        'weight' => '-45',
      ),
    ),
    'active_sorts' => array(
      'active' => 'active',
      'count' => 'count',
      'display' => 'display',
    ),
    'sort_weight' => array(
      'active' => -50,
      'count' => -49,
      'display' => -48,
    ),
    'sort_order' => array(
      'active' => 3,
      'count' => 3,
      'display' => 4,
    ),
    'empty_behavior' => 'none',
    'soft_limit' => 20,
    'nofollow' => 1,
    'show_expanded' => 0,
    'rewrite_items' => 1,
  );
  $export['search_api@datasets:block:search_api_language'] = $facet;

  $facet = new stdClass();
  $facet->disabled = FALSE; /* Edit this to true to make a default facet disabled initially */
  $facet->api_version = 1;
  $facet->name = 'search_api@datasets:block:search_api_viewed';
  $facet->searcher = 'search_api@datasets';
  $facet->realm = 'block';
  $facet->facet = 'search_api_viewed';
  $facet->enabled = FALSE;
  $facet->settings = array(
    'weight' => 0,
    'widget' => 'facetapi_links',
    'filters' => array(
      'active_items' => array(
        'status' => 0,
        'weight' => '-50',
      ),
      'exclude_items' => array(
        'status' => 0,
        'weight' => '-49',
      ),
      'rewrite_items' => array(
        'status' => 1,
        'weight' => '-48',
      ),
      'narrow_results' => array(
        'status' => 0,
        'weight' => '-47',
      ),
      'show_if_minimum_items' => array(
        'status' => 0,
        'weight' => '-46',
      ),
      'deepest_level_items' => array(
        'status' => 0,
        'weight' => '-45',
      ),
    ),
    'active_sorts' => array(
      'active' => 'active',
      'count' => 'count',
      'display' => 'display',
    ),
    'sort_weight' => array(
      'active' => -50,
      'count' => -49,
      'display' => -48,
    ),
    'sort_order' => array(
      'active' => 3,
      'count' => 3,
      'display' => 4,
    ),
    'empty_behavior' => 'none',
    'soft_limit' => 20,
    'nofollow' => 1,
    'show_expanded' => 0,
    'rewrite_items' => 1,
  );
  $export['search_api@datasets:block:search_api_viewed'] = $facet;

  $facet = new stdClass();
  $facet->disabled = FALSE; /* Edit this to true to make a default facet disabled initially */
  $facet->api_version = 1;
  $facet->name = 'search_api@datasets:block:title';
  $facet->searcher = 'search_api@datasets';
  $facet->realm = 'block';
  $facet->facet = 'title';
  $facet->enabled = FALSE;
  $facet->settings = array(
    'weight' => 0,
    'widget' => 'facetapi_links',
    'filters' => array(
      'active_items' => array(
        'status' => 0,
        'weight' => '-50',
      ),
      'exclude_items' => array(
        'status' => 0,
        'weight' => '-49',
      ),
      'rewrite_items' => array(
        'status' => 1,
        'weight' => '-48',
      ),
      'narrow_results' => array(
        'status' => 0,
        'weight' => '-47',
      ),
      'show_if_minimum_items' => array(
        'status' => 0,
        'weight' => '-46',
      ),
      'deepest_level_items' => array(
        'status' => 0,
        'weight' => '-45',
      ),
    ),
    'active_sorts' => array(
      'active' => 'active',
      'count' => 'count',
      'display' => 'display',
    ),
    'sort_weight' => array(
      'active' => -50,
      'count' => -49,
      'display' => -48,
    ),
    'sort_order' => array(
      'active' => 3,
      'count' => 3,
      'display' => 4,
    ),
    'empty_behavior' => 'none',
    'soft_limit' => 20,
    'nofollow' => 1,
    'show_expanded' => 0,
    'rewrite_items' => 1,
  );
  $export['search_api@datasets:block:title'] = $facet;

  $facet = new stdClass();
  $facet->disabled = FALSE; /* Edit this to true to make a default facet disabled initially */
  $facet->api_version = 1;
  $facet->name = 'search_api@datasets:block:type';
  $facet->searcher = 'search_api@datasets';
  $facet->realm = 'block';
  $facet->facet = 'type';
  $facet->enabled = TRUE;
  $facet->settings = array(
    'weight' => 0,
    'widget' => 'content_types',
    'filters' => array(
      'active_items' => array(
        'status' => 0,
        'weight' => '-50',
      ),
      'exclude_items' => array(
        'status' => 0,
        'weight' => '-49',
      ),
      'rewrite_items' => array(
        'status' => 1,
        'weight' => '-48',
      ),
      'narrow_results' => array(
        'status' => 0,
        'weight' => '-47',
      ),
      'show_if_minimum_items' => array(
        'status' => 0,
        'weight' => '-46',
      ),
      'deepest_level_items' => array(
        'status' => 0,
        'weight' => '-45',
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
    'submit_realm' => 'Save and go back to realm settings',
    'rewrite_items' => 1,
  );
  $export['search_api@datasets:block:type'] = $facet;

  $facet = new stdClass();
  $facet->disabled = FALSE; /* Edit this to true to make a default facet disabled initially */
  $facet->api_version = 1;
  $facet->name = 'search_api@default_node_index:block:author';
  $facet->searcher = 'search_api@default_node_index';
  $facet->realm = 'block';
  $facet->facet = 'author';
  $facet->enabled = TRUE;
  $facet->settings = array(
    'weight' => 0,
    'widget' => 'facetapi_links',
    'filters' => array(
      'active_items' => array(
        'status' => 0,
        'weight' => '-50',
      ),
      'exclude_items' => array(
        'status' => 0,
        'weight' => '-49',
      ),
      'rewrite_items' => array(
        'status' => 1,
        'weight' => '-48',
      ),
      'narrow_results' => array(
        'status' => 0,
        'weight' => '-47',
      ),
      'show_if_minimum_items' => array(
        'status' => 0,
        'weight' => '-46',
      ),
      'deepest_level_items' => array(
        'status' => 0,
        'weight' => '-45',
      ),
    ),
    'active_sorts' => array(
      'active' => 'active',
      'count' => 'count',
      'display' => 'display',
    ),
    'sort_weight' => array(
      'active' => -50,
      'count' => -49,
      'display' => -48,
    ),
    'sort_order' => array(
      'active' => 3,
      'count' => 3,
      'display' => 4,
    ),
    'empty_behavior' => 'none',
    'soft_limit' => 20,
    'nofollow' => 1,
    'show_expanded' => 0,
    'rewrite_items' => 1,
  );
  $export['search_api@default_node_index:block:author'] = $facet;

  $facet = new stdClass();
  $facet->disabled = FALSE; /* Edit this to true to make a default facet disabled initially */
  $facet->api_version = 1;
  $facet->name = 'search_api@default_node_index:block:body:value';
  $facet->searcher = 'search_api@default_node_index';
  $facet->realm = 'block';
  $facet->facet = 'body:value';
  $facet->enabled = FALSE;
  $facet->settings = array(
    'weight' => 0,
    'widget' => 'facetapi_links',
    'filters' => array(
      'active_items' => array(
        'status' => 0,
        'weight' => '-50',
      ),
      'exclude_items' => array(
        'status' => 0,
        'weight' => '-49',
      ),
      'rewrite_items' => array(
        'status' => 1,
        'weight' => '-48',
      ),
      'narrow_results' => array(
        'status' => 0,
        'weight' => '-47',
      ),
      'show_if_minimum_items' => array(
        'status' => 0,
        'weight' => '-46',
      ),
      'deepest_level_items' => array(
        'status' => 0,
        'weight' => '-45',
      ),
    ),
    'active_sorts' => array(
      'active' => 'active',
      'count' => 'count',
      'display' => 'display',
    ),
    'sort_weight' => array(
      'active' => -50,
      'count' => -49,
      'display' => -48,
    ),
    'sort_order' => array(
      'active' => 3,
      'count' => 3,
      'display' => 4,
    ),
    'empty_behavior' => 'none',
    'soft_limit' => 20,
    'nofollow' => 1,
    'show_expanded' => 0,
    'rewrite_items' => 1,
  );
  $export['search_api@default_node_index:block:body:value'] = $facet;

  $facet = new stdClass();
  $facet->disabled = FALSE; /* Edit this to true to make a default facet disabled initially */
  $facet->api_version = 1;
  $facet->name = 'search_api@default_node_index:block:created';
  $facet->searcher = 'search_api@default_node_index';
  $facet->realm = 'block';
  $facet->facet = 'created';
  $facet->enabled = TRUE;
  $facet->settings = array(
    'weight' => 0,
    'widget' => 'facetapi_links',
    'filters' => array(
      'active_items' => array(
        'status' => 0,
        'weight' => '-50',
      ),
      'exclude_items' => array(
        'status' => 0,
        'weight' => '-49',
      ),
      'rewrite_items' => array(
        'status' => 1,
        'weight' => '-48',
      ),
      'narrow_results' => array(
        'status' => 0,
        'weight' => '-47',
      ),
      'show_if_minimum_items' => array(
        'status' => 0,
        'weight' => '-46',
      ),
      'deepest_level_items' => array(
        'status' => 0,
        'weight' => '-45',
      ),
    ),
    'active_sorts' => array(
      'active' => 'active',
      'count' => 'count',
      'display' => 'display',
    ),
    'sort_weight' => array(
      'active' => -50,
      'count' => -49,
      'display' => -48,
    ),
    'sort_order' => array(
      'active' => 3,
      'count' => 3,
      'display' => 4,
    ),
    'empty_behavior' => 'none',
    'soft_limit' => 20,
    'nofollow' => 1,
    'show_expanded' => 0,
    'rewrite_items' => 1,
  );
  $export['search_api@default_node_index:block:created'] = $facet;

  $facet = new stdClass();
  $facet->disabled = FALSE; /* Edit this to true to make a default facet disabled initially */
  $facet->api_version = 1;
  $facet->name = 'search_api@default_node_index:block:search_api_language';
  $facet->searcher = 'search_api@default_node_index';
  $facet->realm = 'block';
  $facet->facet = 'search_api_language';
  $facet->enabled = FALSE;
  $facet->settings = array(
    'weight' => 0,
    'widget' => 'facetapi_links',
    'filters' => array(
      'active_items' => array(
        'status' => 0,
        'weight' => '-50',
      ),
      'exclude_items' => array(
        'status' => 0,
        'weight' => '-49',
      ),
      'rewrite_items' => array(
        'status' => 1,
        'weight' => '-48',
      ),
      'narrow_results' => array(
        'status' => 0,
        'weight' => '-47',
      ),
      'show_if_minimum_items' => array(
        'status' => 0,
        'weight' => '-46',
      ),
      'deepest_level_items' => array(
        'status' => 0,
        'weight' => '-45',
      ),
    ),
    'active_sorts' => array(
      'active' => 'active',
      'count' => 'count',
      'display' => 'display',
    ),
    'sort_weight' => array(
      'active' => -50,
      'count' => -49,
      'display' => -48,
    ),
    'sort_order' => array(
      'active' => 3,
      'count' => 3,
      'display' => 4,
    ),
    'empty_behavior' => 'none',
    'soft_limit' => 20,
    'nofollow' => 1,
    'show_expanded' => 0,
    'rewrite_items' => 1,
  );
  $export['search_api@default_node_index:block:search_api_language'] = $facet;

  $facet = new stdClass();
  $facet->disabled = FALSE; /* Edit this to true to make a default facet disabled initially */
  $facet->api_version = 1;
  $facet->name = 'search_api@default_node_index:block:title';
  $facet->searcher = 'search_api@default_node_index';
  $facet->realm = 'block';
  $facet->facet = 'title';
  $facet->enabled = TRUE;
  $facet->settings = array(
    'weight' => 0,
    'widget' => 'facetapi_links',
    'filters' => array(
      'active_items' => array(
        'status' => 0,
        'weight' => '-50',
      ),
      'exclude_items' => array(
        'status' => 0,
        'weight' => '-49',
      ),
      'rewrite_items' => array(
        'status' => 1,
        'weight' => '-48',
      ),
      'narrow_results' => array(
        'status' => 0,
        'weight' => '-47',
      ),
      'show_if_minimum_items' => array(
        'status' => 0,
        'weight' => '-46',
      ),
      'deepest_level_items' => array(
        'status' => 0,
        'weight' => '-45',
      ),
    ),
    'active_sorts' => array(
      'active' => 'active',
      'count' => 'count',
      'display' => 'display',
    ),
    'sort_weight' => array(
      'active' => -50,
      'count' => -49,
      'display' => -48,
    ),
    'sort_order' => array(
      'active' => 3,
      'count' => 3,
      'display' => 4,
    ),
    'empty_behavior' => 'none',
    'soft_limit' => 20,
    'nofollow' => 1,
    'show_expanded' => 0,
    'rewrite_items' => 1,
  );
  $export['search_api@default_node_index:block:title'] = $facet;

  return $export;
}
