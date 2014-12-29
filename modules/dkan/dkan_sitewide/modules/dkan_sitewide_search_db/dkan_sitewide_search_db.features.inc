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
    "name" : "Datasets",
    "machine_name" : "datasets",
    "description" : null,
    "server" : "datasets",
    "item_type" : "node",
    "options" : {
      "index_directly" : 1,
      "cron_limit" : "50",
      "fields" : {
        "type" : { "type" : "string" },
        "title" : { "type" : "string" },
        "status" : { "type" : "boolean" },
        "changed" : { "type" : "date" },
        "author" : { "type" : "integer", "entity_type" : "user" },
        "field_license" : { "type" : "string" },
        "field_tags" : { "type" : "list\\u003Cinteger\\u003E", "entity_type" : "taxonomy_term" },
        "og_group_ref" : { "type" : "list\\u003Cinteger\\u003E", "entity_type" : "node" },
        "search_api_language" : { "type" : "string" },
        "search_api_viewed" : { "type" : "text" },
        "search_api_access_node" : { "type" : "list\\u003Cstring\\u003E" },
        "field_resources:field_format" : { "type" : "list\\u003Cinteger\\u003E", "entity_type" : "taxonomy_term" },
        "field_resources:body:value" : { "type" : "list\\u003Ctext\\u003E" }
      },
      "data_alter_callbacks" : {
        "search_api_alter_bundle_filter" : {
          "status" : 1,
          "weight" : "-10",
          "settings" : { "default" : "0", "bundles" : { "dataset" : "dataset" } }
        },
        "search_api_alter_node_access" : { "status" : 1, "weight" : "0", "settings" : [] },
        "search_api_alter_node_status" : { "status" : 0, "weight" : "0", "settings" : [] },
        "search_api_alter_add_hierarchy" : { "status" : 0, "weight" : "0", "settings" : { "fields" : [] } },
        "search_api_alter_add_viewed_entity" : { "status" : 1, "weight" : "0", "settings" : { "mode" : "full" } },
        "search_api_alter_add_url" : { "status" : 0, "weight" : "0", "settings" : [] },
        "search_api_alter_add_aggregation" : { "status" : 0, "weight" : "0", "settings" : [] }
      },
      "processors" : {
        "search_api_case_ignore" : {
          "status" : 0,
          "weight" : "0",
          "settings" : { "fields" : { "title" : true } }
        },
        "search_api_html_filter" : {
          "status" : 0,
          "weight" : "10",
          "settings" : {
            "fields" : { "title" : true },
            "title" : 0,
            "alt" : 1,
            "tags" : "h1 = 5\\r\\nh2 = 3\\r\\nh3 = 2\\r\\nstrong = 2\\r\\nb = 2\\r\\nem = 1.5\\r\\nu = 1.5"
          }
        },
        "search_api_tokenizer" : {
          "status" : 0,
          "weight" : "20",
          "settings" : { "fields" : [], "spaces" : "[^[:alnum:]]", "ignorable" : "[\\u0027]" }
        },
        "search_api_stopwords" : {
          "status" : 0,
          "weight" : "30",
          "settings" : {
            "fields" : { "title" : true },
            "file" : "",
            "stopwords" : "but\\r\\ndid\\r\\nthe this that those\\r\\netc"
          }
        }
      }
    },
    "enabled" : "1",
    "read_only" : "0",
    "rdf_mapping" : []
  }');
  return $items;
}

/**
 * Implements hook_default_search_api_server().
 */
function dkan_sitewide_search_db_default_search_api_server() {
  $items = array();
  $items['datasets'] = entity_import('search_api_server', '{
    "name" : "Datasets",
    "machine_name" : "datasets",
    "description" : "",
    "class" : "search_api_db_service",
    "options" : {
      "database" : "default:default",
      "min_chars" : "1",
      "indexes" : { "datasets" : {
          "type" : {
            "table" : "search_api_db_datasets_type",
            "type" : "string",
            "boost" : "1.0"
          },
          "title" : {
            "table" : "search_api_db_datasets_title",
            "type" : "string",
            "boost" : "1.0"
          },
          "status" : {
            "table" : "search_api_db_datasets_status",
            "type" : "boolean",
            "boost" : "1.0"
          },
          "author" : {
            "table" : "search_api_db_datasets_author",
            "type" : "integer",
            "boost" : "1.0"
          },
          "search_api_language" : {
            "table" : "search_api_db_datasets_search_api_language",
            "type" : "string",
            "boost" : "1.0"
          },
          "search_api_viewed" : {
            "table" : "search_api_db_datasets_search_api_viewed",
            "type" : "text",
            "boost" : "1.0"
          },
          "search_api_access_node" : {
            "table" : "search_api_db_datasets_search_api_access_node",
            "type" : "list\\u003Cstring\\u003E",
            "boost" : "1.0"
          },
          "changed" : {
            "table" : "search_api_db_datasets",
            "column" : "changed",
            "type" : "date",
            "boost" : "1.0"
          },
          "field_license" : {
            "table" : "search_api_db_datasets",
            "type" : "string",
            "boost" : "1.0",
            "column" : "field_license"
          },
          "field_tags" : {
            "table" : "search_api_db_datasets_field_tags",
            "column" : "value",
            "type" : "list\\u003Cinteger\\u003E",
            "boost" : "1.0"
          },
          "og_group_ref" : {
            "table" : "search_api_db_datasets_og_group_ref",
            "column" : "value",
            "type" : "list\\u003Cinteger\\u003E",
            "boost" : "1.0"
          },
          "field_resources:field_format" : {
            "table" : "search_api_db_datasets_field_resources_field_format",
            "column" : "value",
            "type" : "list\\u003Cinteger\\u003E",
            "boost" : "1.0"
          },
          "field_resources:body:value" : {
            "table" : "search_api_db_datasets_field_resources_body_value",
            "type" : "list\\u003Ctext\\u003E",
            "boost" : "1.0"
          }
        }
      }
    },
    "enabled" : "1",
    "rdf_mapping" : []
  }');
  return $items;
}
