<?php

/**
 * @file
 * Helper functions file for DKAN Dataset API.
 */

/**
 * Build PHP array and object structure in preparation for JSON output.
 *
 * @param array $nodes
 *   An array of dataset nodes.
 *
 * @return array
 *   An array of objects formatted as per Open Data schema specifications.
 */
function dkan_open_data_build_rows($nodes) {

  // First row is the catalog itself.
  $catalog_dist = new stdClass();
  $catalog_dist->accessURL = url('data.json', array('absolute' => TRUE));
  $catalog_dist->format = 'application/json';

  $catalog_row = new stdClass();
  $catalog_row->title = variable_get('dkan_catalog_desc', 'Data Catalog');
  $catalog_row->description = variable_get('dkan_catalog_desc', 'Version 1.0');
  $catalog_row->keyword = array('catalog');
  $catalog_row->modified = NULL;
  $catalog_row->publisher = variable_get('site_name');
  $catalog_row->contactPoint = variable_get('dkan_catalog_contact', $catalog_row->publisher);
  $catalog_row->mbox = variable_get('dkan_catalog_mbox', variable_get('site_mail'));
  $catalog_row->identifier = "1";
  $catalog_row->accessLevel = 'public';
  $catalog_row->distribution = array($catalog_dist);

  $rows = array($catalog_row);

  $i = 0;
  foreach ($nodes as $node) {
    $node_wrapper = entity_metadata_wrapper('node', $node);

    try {
      $row = new stdClass();

      // Common Core fields. These are required, so we don't check for empty.
      $row->title = $node_wrapper->title->value();
      $row->description = isset($node->body[$node->language]) ? $node_wrapper->body->value->value(array('sanitize' => TRUE)) : '';
      $tags = array();
      if (isset($node_wrapper->field_tags)) {
        foreach ($node_wrapper->field_tags->getIterator() as $tag) {
          $tags[] = $tag->name->value();
        }
      }
      $row->keyword = $tags;
      $row->modified = format_date($node_wrapper->changed->value(), 'custom', 'Y-m-d');
      if ($i == 0) {
        $rows[0]->modified = $row->modified;
      }

      $publisher = $node_wrapper->og_group_ref->value();
      if (!isset($publisher[0]->title)) {
        $row->publisher = "";
      }
      else {
        $row->publisher = $publisher[0]->title;
      }
      $row->contactPoint = dkan_open_data_get_field_value($node, 'field_contact_name', 'value');
      $row->mbox = dkan_open_data_get_field_value($node, 'field_contact_email', 'value');
      $row->identifier = dkan_open_data_get_field_value($node, 'uuid', 'value');
      $access_level = dkan_open_data_get_field_value($node, 'field_public_access_level', 'label');
      $row->accessLevel = $access_level ? $access_level : t('public');

      // Common Core (Required if Applicable) fields.
      $row->accessURL = url('node/' . $node->nid, array('absolute' => TRUE));
      // Fields with an if statement will break the POD json validator if left
      // blank.
      if ($data_dictionary = dkan_open_data_get_field_value($node, 'field_data_dictionary', 'value')) {
        $row->dataDictionary = $data_dictionary;
      }
      $row->webService = url('api/3/action/package_show', array('query' => array('id' => $node->uuid), 'absolute' => TRUE));
      if ($dist = dkan_open_data_build_distributions($node)) {
        $row->distribution = $dist;
      }
      $row->license = dkan_open_data_get_field_value($node, 'field_license', 'value');
      if ($spatial = dkan_open_data_get_field_value($node, 'field_spatial_geographical_cover', 'value')) {
        $row->spatial = $spatial;
      }
      if ($temporal = dkan_open_data_get_field_value($node, 'field_temporal_coverage', 'value')) {
        $row->temporal = $temporal;
      }

      // Extended fields.
      if ($accrual = strtolower(dkan_open_data_get_field_value($node, 'field_frequency', 'label'))) {
        $row->references = dkan_open_data_get_field_value($node, 'field_related_content', 'value');
      }
      $language = !isset($node->langauge) && $node->language != 'und' ? $node->language : 'english';
      $row->language = array($language);
      $row->granularity = dkan_open_data_get_field_value($node, 'field_granularity', 'value');
    }
    catch (EntityMetadataWrapperException $e) {
      $row = new stdClass();
    }

    $rows[] = $row;
    unset($row);
    $i++;
  }
  return $rows;
}

/**
 * Build PHP array and object structure in preparation for JSON output.
 *
 * @param object|array $nodes
 *   A dataset node.
 *
 * @return array
 *   An array of objects formatted as per CKAN API specifications.
 */
function dkan_open_data_build_datasets($nodes) {
  if (!is_array($nodes)) {
    $nodes = array($nodes);
  }
  foreach ($nodes as $node) {
    $node_wrapper = entity_metadata_wrapper('node', $node);

    try {
      $row = new stdClass();
      // Common Core fields. These are required, so we don't check for empty.
      $row->title = $node_wrapper->title->value();
      $row->description = isset($node->body[$node->language]) ? $node_wrapper->body->value->value(array('sanitize' => TRUE)) : '';
      $row->id = dkan_open_data_get_field_value($node, 'uuid', 'value');
      $row->license_title = dkan_open_data_get_field_value($node, 'field_license', 'value');
      $row->metadata_created = format_date($node_wrapper->created->value(), 'custom', 'Y-m-d\TH:i:s.u');
      $row->metadata_modified = format_date($node_wrapper->changed->value(), 'custom', 'Y-m-d\TH:i:s.u');
      $row->url = url('node/' . $node->nid, array('absolute' => TRUE));
      $row->author = dkan_open_data_get_field_value($node, 'field_contact_name', 'value');
      $row->author_email = dkan_open_data_get_field_value($node, 'field_contact_email', 'value');
      $row->type = 'dataset';

      $access_level = dkan_open_data_get_field_value($node, 'field_public_access_level', 'label');
      $access_level = $access_level ? $access_level : t('public');
      $row->private = $access_level == t('public') ? FALSE : TRUE;

      if (isset($node->field_resources[LANGUAGE_NONE])) {
        $resources = array();
        foreach ($node->field_resources[LANGUAGE_NONE] as $key => $resource) {
          $resources[] = $resource['target_id'];
        }
        $resources = node_load_multiple($resources);
        // Check user access.
        foreach ($resources as $key => $resource) {
          if (!node_access('view', $resource)) {
            unset($resources[$key]);
          }
        }
        $row->resources = dkan_open_data_build_dataset_resources($resources);
        $row->num_resources = count($resources);
      }

      $tags = array();
      if (isset($node_wrapper->field_tags)) {
        foreach ($node_wrapper->field_tags->getIterator() as $tag) {
          $tags[] = (object) array(
            'name' => $tag->name->value(),
            'display_name' => $tag->name->value(),
            'id' => $tag->uuid->value(),
          );
        }
      }
      $row->tags = $tags;
    }
    catch (EntityMetadataWrapperException $e) {
      $row = new stdClass();
    }
    $rows[] = $row;
  }
  return $rows;
}

/**
 * Build PHP array and object structure in preparation for JSON output.
 *
 * @param object $nodes
 *   A dataset node.
 *
 * @return array
 *   An array of resource objects formatted as per CKAN API specifications.
 */
function dkan_open_data_build_dataset_resources($nodes) {
  $resources = array();
  foreach ($nodes as $node) {
    $node_wrapper = entity_metadata_wrapper('node', $node);
    try {
      $row = new stdClass();
      $row->title = $node_wrapper->title->value();
      $row->description = isset($node->body[$node->language]) ? $node_wrapper->body->value->value(array('sanitize' => TRUE)) : '';
      $row->id = $node_wrapper->uuid->value();
      $row->revision_id = $node_wrapper->vuuid->value();
      $row->created = format_date($node_wrapper->created->value(), 'custom', 'Y-m-d\TH:i:s.u');
      $row->last_modified = format_date($node_wrapper->changed->value(), 'custom', 'Y-m-d\TH:i:s.u');
      if (isset($node_wrapper->field_upload)) {
        $row->resource_type = 'file';
        $file = $node->field_upload[$node->language][0];
        $row->url = file_create_url($file['uri']);
        if (!empty($file['filesize'])) {
          $row->size = (int) $file['filesize'];
        }
        $row->mimetype = isset($file['filemime']) ? $file['filemime'] : '';
        $row->active = $file['status'] ? TRUE : FALSE;
      }
      elseif (isset($node_wrapper->field_link_remote_file)) {
        $row->resource_type = 'remote_file';
        $row->url = $node_wrapper->field_link_remote_file->value();
      }
      elseif (isset($node_wrapper->field_link_api)) {
        $row->resource_type = 'api';
        $row->url = $node_wrapper->field_link_api->value();
      }
    }
    catch (EntityMetadataWrapperException $e) {
      $row = new stdClass();
    }
    $resources[] = $row;
  }
  return $resources;
}

/**
 * Build the distributions property for a JSON row.
 *
 * @param object $node
 *   An opendata_dataset node.
 *
 * @return array
 *   An array of distribution objects.
 */
function dkan_open_data_build_distributions($node) {
  $distributions = array();

  $nid = $node->nid;

  $nodes = dkan_dataset_get_resource_nodes($nid);
  if (!empty($nodes)) {
    foreach ($nodes as $node) {
      $row = new stdClass();
      if (isset($node->field_upload[$node->language][0])) {
        $row->accessURL = file_create_url($node->field_upload[$node->language][0]['uri']);
        if (!empty($node->field_upload[$node->language][0]['filesize'])) {
          $row->size = (int) $node->field_upload[$node->language][0]['filesize'];
        }
        $row->format = $node->field_upload[$node->language][0]['filemime'];

        $distributions[] = $row;
      }

    }
  }

  return $distributions;
}

/**
 * Sets a JSON row property given a node field name.
 *
 * @param object $node
 *   The node containing the field value.
 * @param string $field_name
 *   The machine name of the field.
 * @param string $column_name
 *   The column containing the primary value for a given field.
 */
function dkan_open_data_get_field_value($node, $field_name, $column_name) {
  $node_wrapper = entity_metadata_wrapper('node', $node);

  try {
    // Field is a list, needs this gross formatting.
    if ($field_name == 'field_license') {
      $key = $node_wrapper->$field_name->value();
      $field = field_info_instance('node', 'field_license', 'dataset');
      $raw_options = $field['widget']['settings']['available_options'];
      foreach (explode("\n", $raw_options) as $option) {
        $option = explode("|", $option);
        $options[$option[0]] = $option[1];
      }
      $field_value = isset($options[$key]) ? trim($options[$key]) : NULL;
    }
    // Link field, can be external or external path, preparing url if present.
    elseif ($field_name == 'field_related_content') {
      $field_value = $node_wrapper->$field_name->$column_name();
      $references = '';
      foreach ($field_value as $instance) {
        if (isset($instance['url'])) {
          $references .= url($instance['url'], array('absolute' => TRUE)) . ',';
        }
      }
      return trim($references, ',');
    }
    else {
      $field_value = $node_wrapper->$field_name->$column_name();
    }
    if (!empty($field_value)) {
      if ($field_name == 'field_temporal_coverage') {
        $time = $node_wrapper->$field_name->value();
        $start_time = $time['value'];
        $field_value = $start_time;

        if (isset($time['value2'])) {
          $end_time = $time['value2'];
          $field_value = $field_value . ',' . $end_time;
        }

      }
      return $field_value;
    }
    else {
      return '';
    }
  }
  catch (EntityMetadataWrapperException $e) {
    return '';
  }
}
