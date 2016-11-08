<?php

/**
 * @file
 * dkan_sitewide_search_db.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function dkan_sitewide_search_db_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "context" && $api == "context") {
    return array("version" => "3");
  }
  if ($module == "facetapi" && $api == "facetapi_defaults") {
    return array("version" => "1");
  }
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function dkan_sitewide_search_db_views_api($module = NULL, $api = NULL) {
  return array("api" => "3.0");
}

/**
 * Implements hook_default_search_api_index().
 */
function dkan_sitewide_search_db_default_search_api_index() {
  $items = array();
  $items['datasets'] = entity_import('search_api_index', '{
    "name" : "datasets",
    "machine_name" : "datasets",
    "description" : null,
    "server" : "datasets",
    "item_type" : "node",
    "options" : {
      "datasource" : { "bundles" : [
          "dataset",
          "resource",
          "group",
          "dkan_data_story",
          "data_dashboard",
          "page",
          "harvest_source"
        ]
      },
      "index_directly" : 1,
      "cron_limit" : "50",
      "fields" : {
        "author" : { "type" : "integer", "entity_type" : "user" },
        "changed" : { "type" : "date" },
        "created" : { "type" : "date" },
        "field_author" : { "type" : "string" },
        "field_harvest_source_ref" : { "type" : "integer", "entity_type" : "node" },
        "field_license" : { "type" : "string" },
        "field_resources:body:value" : { "type" : "list\\u003Ctext\\u003E" },
        "field_resources:field_format" : { "type" : "list\\u003Cinteger\\u003E", "entity_type" : "taxonomy_term" },
        "field_tags" : { "type" : "list\\u003Cinteger\\u003E", "entity_type" : "taxonomy_term" },
        "field_topic" : { "type" : "list\\u003Cinteger\\u003E", "entity_type" : "taxonomy_term" },
        "og_group_ref" : { "type" : "list\\u003Cinteger\\u003E", "entity_type" : "node" },
        "search_api_access_node" : { "type" : "list\\u003Cstring\\u003E" },
        "search_api_language" : { "type" : "string" },
        "search_api_viewed" : { "type" : "text" },
        "status" : { "type" : "boolean" },
        "title" : { "type" : "string" },
        "type" : { "type" : "string" }
      },
      "data_alter_callbacks" : {
        "search_api_alter_node_access" : { "status" : 1, "weight" : "0", "settings" : [] },
        "search_api_alter_node_status" : { "status" : 1, "weight" : "0", "settings" : [] },
        "search_api_alter_add_hierarchy" : { "status" : 0, "weight" : "0", "settings" : { "fields" : [] } },
        "search_api_alter_add_viewed_entity" : { "status" : 1, "weight" : "0", "settings" : { "mode" : "full" } },
        "search_api_alter_add_aggregation" : { "status" : 0, "weight" : "0", "settings" : [] },
        "search_api_alter_add_url" : { "status" : 0, "weight" : "0", "settings" : [] }
      },
      "processors" : {
        "search_api_case_ignore" : { "status" : 0, "weight" : "0", "settings" : { "fields" : [] } },
        "search_api_html_filter" : {
          "status" : 0,
          "weight" : "10",
          "settings" : {
            "fields" : [],
            "title" : 0,
            "alt" : 1,
            "tags" : "h1 = 5\\r\\nh2 = 3\\r\\nh3 = 2\\r\\nstrong = 2\\r\\nb = 2\\r\\nem = 1.5\\r\\nu = 1.5"
          }
        },
        "search_api_tokenizer" : {
          "status" : 1,
          "weight" : "20",
          "settings" : {
            "fields" : { "search_api_viewed" : true },
            "spaces" : "[^[:alnum:]]",
            "ignorable" : "[\\u0027]"
          }
        },
        "search_api_stopwords" : {
          "status" : 0,
          "weight" : "30",
          "settings" : {
            "fields" : [],
            "file" : "",
            "stopwords" : "but\\r\\ndid\\r\\nthe this that those\\r\\netc"
          }
        },
        "search_api_highlighting" : {
          "status" : 0,
          "weight" : "35",
          "settings" : {
            "prefix" : "\\u003Cstrong\\u003E",
            "suffix" : "\\u003C\\/strong\\u003E",
            "excerpt" : 1,
            "excerpt_length" : "256",
            "exclude_fields" : [],
            "highlight" : "always"
          }
        }
      }
    },
    "enabled" : "1",
    "read_only" : "0"
  }');
  return $items;
}

/**
 * Implements hook_default_search_api_server().
 */
function dkan_sitewide_search_db_default_search_api_server() {
  $items = array();
  $items['datasets'] = entity_import('search_api_server', '{
    "name" : "datasets",
    "machine_name" : "datasets",
    "description" : "",
    "class" : "search_api_db_service",
    "options" : {
      "database" : "default:default",
      "min_chars" : "2",
      "partial_matches" : 0,
      "indexes" : { "datasets" : {
          "title" : {
            "table" : "search_api_db_datasets",
            "column" : "title",
            "type" : "string",
            "boost" : "1.0"
          },
          "author" : {
            "table" : "search_api_db_datasets",
            "column" : "author",
            "type" : "integer",
            "boost" : "1.0"
          },
          "field_tags" : {
            "table" : "search_api_db_datasets_field_tags",
            "column" : "value",
            "type" : "list\\u003Cinteger\\u003E",
            "boost" : "1.0"
          },
          "field_author" : {
            "table" : "search_api_db_datasets",
            "column" : "field_author",
            "type" : "string",
            "boost" : "1.0"
          },
          "field_license" : {
            "table" : "search_api_db_datasets",
            "column" : "field_license",
            "type" : "string",
            "boost" : "1.0"
          },
          "search_api_language" : {
            "table" : "search_api_db_datasets",
            "column" : "search_api_language",
            "type" : "string",
            "boost" : "1.0"
          },
          "status" : {
            "table" : "search_api_db_datasets",
            "column" : "status",
            "type" : "boolean",
            "boost" : "1.0"
          },
          "search_api_viewed" : {
            "table" : "search_api_db_datasets_text",
            "type" : "text",
            "boost" : "1.0"
          },
          "search_api_access_node" : {
            "table" : "search_api_db_datasets_search_api_access_node",
            "column" : "value",
            "type" : "list\\u003Cstring\\u003E",
            "boost" : "1.0"
          },
          "field_resources:field_format" : {
            "table" : "search_api_db_datasets_field_resources_field_format",
            "column" : "value",
            "type" : "list\\u003Cinteger\\u003E",
            "boost" : "1.0"
          },
          "field_resources:body:value" : {
            "table" : "search_api_db_datasets_text",
            "type" : "list\\u003Ctext\\u003E",
            "boost" : "1.0"
          },
          "og_group_ref" : {
            "boost" : "1.0",
            "column" : "value",
            "table" : "search_api_db_datasets_og_group_ref_2",
            "type" : "list\\u003Cinteger\\u003E"
          },
          "created" : {
            "table" : "search_api_db_datasets",
            "column" : "created",
            "type" : "date",
            "boost" : "1.0"
          },
          "changed" : {
            "table" : "search_api_db_datasets",
            "column" : "changed",
            "type" : "date",
            "boost" : "1.0"
          },
          "type" : {
            "table" : "search_api_db_datasets",
            "column" : "type",
            "type" : "string",
            "boost" : "1.0"
          },
          "field_topic" : {
            "table" : "search_api_db_datasets_field_topic",
            "column" : "value",
            "type" : "list\\u003Cinteger\\u003E",
            "boost" : "1.0"
          },
          "field_harvest_source_ref" : {
            "table" : "search_api_db_datasets",
            "column" : "field_harvest_source_ref",
            "type" : "integer",
            "boost" : "1.0"
          }
        }
      }
    },
    "enabled" : "1"
  }');
  return $items;
}
