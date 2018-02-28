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
          "field_harvest_source_ref" : {
            "table" : "search_api_db_datasets",
            "column" : "field_harvest_source_ref",
            "type" : "integer",
            "boost" : "1.0"
          },
          "field_topic" : {
            "table" : "search_api_db_datasets_field_topic",
            "column" : "value",
            "type" : "list\\u003Cinteger\\u003E",
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
