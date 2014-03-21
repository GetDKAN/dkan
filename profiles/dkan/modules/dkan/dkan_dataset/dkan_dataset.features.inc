<?php
/**
 * @file
 * dkan_dataset.features.inc
 */

/**
 * Implements hook_ctools_plugin_api().
 */
function dkan_dataset_ctools_plugin_api($module = NULL, $api = NULL) {
  if ($module == "strongarm" && $api == "strongarm") {
    return array("version" => "1");
  }
}

/**
 * Implements hook_views_api().
 */
function dkan_dataset_views_api($module = NULL, $api = NULL) {
  return array("api" => "3.0");
}

/**
 * Implements hook_rdf_default_mappings().
 */
function dkan_dataset_rdf_default_mappings() {
  $rdf_mappings = array();

  // Exported RDF mapping: dataset.
  $rdf_mappings['node']['dataset'] = array(
    'rdftype' => array(
      0 => 'sioc:Item',
      1 => 'foaf:Document',
      2 => 'dcat:Dataset',
    ),
    'title' => array(
      'predicates' => array(
        0 => 'dc:title',
      ),
      'type' => 'property',
    ),
    'created' => array(
      'predicates' => array(
        0 => 'dc:date',
        1 => 'dc:created',
      ),
      'datatype' => 'xsd:dateTime',
      'callback' => 'date_iso8601',
    ),
    'changed' => array(
      'predicates' => array(
        0 => 'dc:modified',
      ),
      'datatype' => 'xsd:dateTime',
      'callback' => 'date_iso8601',
    ),
    'body' => array(
      'predicates' => array(
        0 => 'dc:description',
      ),
      'type' => 'property',
    ),
    'uid' => array(
      'predicates' => array(
        0 => 'sioc:has_creator',
      ),
      'type' => 'rel',
    ),
    'name' => array(
      'predicates' => array(
        0 => 'foaf:name',
      ),
    ),
    'comment_count' => array(
      'predicates' => array(
        0 => 'sioc:num_replies',
      ),
      'datatype' => 'xsd:integer',
    ),
    'last_activity' => array(
      'predicates' => array(
        0 => 'sioc:last_activity_date',
      ),
      'datatype' => 'xsd:dateTime',
      'callback' => 'date_iso8601',
    ),
    'og_group_ref' => array(
      'type' => 'property',
      'datatype' => 'xsd:string',
      'predicates' => array(
        0 => 'dc:publisher',
      ),
    ),
    'field_frequency' => array(
      'type' => 'property',
      'datatype' => 'xsd:string',
      'predicates' => array(
        0 => 'dc:Frequency',
      ),
    ),
    'field_spatial' => array(
      'type' => 'property',
      'predicates' => array(
        0 => 'dc:spatial',
      ),
    ),
    'field_spatial_geographical_cover' => array(
      'type' => 'property',
      'predicates' => array(
        0 => 'dc:spatial',
      ),
    ),
    'field_temporal_coverage' => array(
      'type' => 'property',
      'predicates' => array(
        0 => 'dc:temporal',
      ),
    ),
    'field_granularity' => array(
      'type' => 'property',
      'datatype' => 'xsd:string',
      'predicates' => array(
        0 => 'dcat:granularity',
      ),
    ),
    'field_data_dictionary' => array(
      'type' => 'property',
      'predicates' => array(
        0 => 'dcat:dataDictionary',
      ),
    ),
    'field_author' => array(
      'type' => 'property',
      'datatype' => 'xsd:string',
      'predicates' => array(
        0 => 'foaf:name',
      ),
    ),
    'field_tags' => array(
      'type' => 'property',
      'datatype' => 'xsd:string',
      'predicates' => array(
        0 => 'dcat:keyword',
      ),
    ),
    'field_resource' => array(
      'type' => 'property',
      'predicates' => array(
        0 => 'dcat:Distribution',
      ),
    ),
    'field_dataset_ref' => array(
      'type' => 'property',
      'predicates' => array(
        0 => 'dcat:Distribution',
      ),
    ),
    'field_resources' => array(
      'type' => 'property',
      'predicates' => array(
        0 => 'dcat:Distribution',
      ),
    ),
  );

  return $rdf_mappings;
}
