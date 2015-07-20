<?php
/**
 * @file
 * hhs_search.features.inc
 */

/**
 * Implements hook_default_search_api_server().
 */
function dkan_acquia_search_solr_default_search_api_server() {
  $items = array();
  $items['acquia_solr_server'] = entity_import('search_api_server', '{
    "name" : "Acquia Solr Server",
    "machine_name" : "acquia_solr_server",
    "description" : "",
    "class" : "acquia_search_service",
    "options" : {
      "clean_ids" : true,
      "site_hash" : true,
      "scheme" : "http",
      "host" : "search.acquia.com",
      "port" : "80",
      "path" : "\\/solr\\/",
      "edismax" : 0,
      "modify_acquia_connection" : false,
      "acquia_override_subscription" : {
        "acquia_override_auto_switch" : 1,
        "acquia_override_selector" : "default",
        "acquia_override_subscription_id" : "",
        "acquia_override_subscription_key" : "",
        "acquia_override_subscription_corename" : ""
      },
      "http_user" : "",
      "http_pass" : "",
      "excerpt" : 0,
      "retrieve_data" : 0,
      "highlight_data" : 0,
      "skip_schema_check" : 0,
      "solr_version" : "",
      "http_method" : "AUTO"
    },
    "enabled" : "1",
    "rdf_mapping" : []
  }');
  $items['local_solr_server'] = entity_import('search_api_server', '{
    "name" : "Local Solr Server",
    "machine_name" : "local_solr_server",
    "description" : "",
    "class" : "search_api_solr_service",
    "options" : {
      "clean_ids" : true,
      "site_hash" : true,
      "scheme" : "http",
      "host" : "localhost",
      "port" : "8080",
      "path" : "\\/solr-hhs",
      "http_user" : "",
      "http_pass" : "",
      "excerpt" : 0,
      "retrieve_data" : 0,
      "highlight_data" : 0,
      "skip_schema_check" : 0,
      "solr_version" : "",
      "http_method" : "AUTO"
    },
    "enabled" : "1",
    "rdf_mapping" : []
  }');
  return $items;
}
