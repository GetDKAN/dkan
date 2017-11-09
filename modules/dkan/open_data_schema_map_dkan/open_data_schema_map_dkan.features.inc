<?php

/**
 * @file
 * open_data_schema_map_dkan.features.inc
 */

/**
 * Implements hook_open_data_schema_apis_defaults().
 */
function open_data_schema_map_dkan_open_data_schema_apis_defaults() {
  $open_data_schema_apis = array();

  // Exported Open Data API Map: CKAN Current Package List With Resources.
  $open_data_schema_apis['ckan_current_package_list_with_resources'] = array(
    'name' => 'CKAN Current Package List With Resources',
    'enabled' => 1,
    'machine_name' => 'ckan_current_package_list_with_resources',
    'api_schema' => 'ckan_dataset',
    'description' => NULL,
    'type' => 'node',
    'bundle' => 'dataset',
    'mapping' => array(
      'id' => array(
        'value' => '[node:uuid]',
        'type' => 'string',
      ),
      'revision_id' => array(
        'value' => '[node:vuuid]',
        'type' => 'string',
      ),
      'name' => array(
        'value' => '[node:url:args:last]',
        'type' => 'string',
      ),
      'title' => array(
        'value' => '[node:title]',
        'type' => 'string',
      ),
      'author' => array(
        'value' => '[node:field_author]',
        'type' => 'string',
      ),
      'author_email' => array(
        'value' => '[node:field-contact-email]',
        'type' => 'string',
      ),
      'maintainer' => array(
        'value' => '[site:name]',
        'type' => 'string',
      ),
      'maintainer_email' => array(
        'value' => '[site:mail]',
        'type' => 'string',
      ),
      'license_title' => array(
        'value' => '[node:field_license]',
        'type' => 'string',
      ),
      'license_id' => array(
        'value' => '',
        'type' => 'string',
      ),
      'notes' => array(
        'value' => '[node:body]',
        'type' => 'string',
      ),
      'url' => array(
        'value' => '[node:url]',
        'type' => 'string',
      ),
      'version' => array(
        'value' => '',
        'type' => 'string',
      ),
      'state' => array(
        'value' => 'Active',
        'type' => 'string',
      ),
      'owner_org' => array(
        'value' => '',
        'type' => 'string',
      ),
      'log_message' => array(
        'value' => '[node:log]',
        'type' => 'string',
      ),
      'private' => array(
        'value' => '[node:status]',
        'type' => 'boolean',
      ),
      'relationships_as_object' => array(
        'value' => '',
        'type' => 'object',
      ),
      'revision_timestamp' => array(
        'value' => '[node:changed]',
        'type' => 'string',
      ),
      'metadata_created' => array(
        'value' => '[node:field_harvest_source_issued] || [node:created]',
        'type' => 'string',
      ),
      'metadata_modified' => array(
        'value' => '[node:field_harvest_source_modified] || [node:changed]',
        'type' => 'string',
      ),
      'creator_user_id' => array(
        'value' => '[node:author:uuid]',
        'type' => 'string',
      ),
      'type' => array(
        'value' => 'Dataset',
        'type' => 'string',
      ),
      'resources' => array(
        'odsm_entity_reference' => array(
          'value' => 'field_resources',
          'type' => '',
        ),
        'id' => array(
          'value' => '[node:field-resources:Nth:uuid]',
          'type' => '',
        ),
        'revision_id' => array(
          'value' => '[node:field-resources:Nth:vuuid]',
          'type' => '',
        ),
        'url' => array(
          'value' => '[node:field-resources:Nth:field-link-api:url] || [node:field-resources:Nth:field_link_remote_file] || [node:field-resources:Nth:field-upload:url]',
          'type' => '',
        ),
        'description' => array(
          'value' => '[node:field-resources:Nth:body]',
          'type' => '',
        ),
        'format' => array(
          'value' => '[node:field-resources:Nth:field_format]',
          'type' => '',
        ),
        'hash' => array(
          'value' => '',
          'type' => '',
        ),
        'state' => array(
          'value' => 'Active',
          'type' => '',
        ),
        'Position' => array(
          'value' => '',
          'type' => '',
        ),
        'revision_timestamp' => array(
          'value' => '[node:field-resources:Nth:changed]',
          'type' => '',
        ),
        'name' => array(
          'value' => '[node:field-resources:Nth:title]',
          'type' => '',
        ),
        'resource_type' => array(
          'value' => '',
          'type' => '',
        ),
        'url_type' => array(
          'value' => '',
          'type' => '',
        ),
        'mimetype' => array(
          'value' => '[node:field-resources:Nth:field-upload:mime] || [node:field-resources:Nth:field_format]',
          'type' => '',
        ),
        'webstore_url' => array(
          'value' => '',
          'type' => '',
        ),
        'size' => array(
          'value' => '[node:field-resources:Nth:field-upload:size]',
          'type' => '',
        ),
        'created' => array(
          'value' => '[node:field-resources:Nth:created]',
          'type' => '',
        ),
        'resource_group_id' => array(
          'value' => '[node:field-resources:Nth:og-group-ref:0:uuid]',
          'type' => '',
        ),
        'cache_last_updated' => array(
          'value' => '',
          'type' => '',
        ),
        'webstore_last_updated' => array(
          'value' => '',
          'type' => '',
        ),
        'datastore_active' => array(
          'value' => '',
          'type' => '',
        ),
        'last_modified' => array(
          'value' => 'Date changed [node:field-resources:Nth:changed]',
          'type' => '',
        ),
      ),
      'tags' => array(
        'odsm_entity_reference' => array(
          'value' => 'field_tags',
          'type' => '',
        ),
        'id' => array(
          'value' => '[node:field-tags:Nth:uuid]',
          'type' => '',
        ),
        'vocabulary_id' => array(
          'value' => '[node:field-tags:Nth:vocabulary:vid]',
          'type' => '',
        ),
        'name' => array(
          'value' => '[node:field-tags:Nth:name]',
          'type' => '',
        ),
        'revision_timestamp' => array(
          'value' => '',
          'type' => '',
        ),
        'state' => array(
          'value' => '',
          'type' => '',
        ),
      ),
      'groups' => array(
        'odsm_entity_reference' => array(
          'value' => 'og_group_ref',
          'type' => '',
        ),
        'display_name' => array(
          'value' => '[node:title]',
          'type' => '',
        ),
        'description' => array(
          'value' => '[node:og-group-ref:Nth:body]',
          'type' => '',
        ),
        'id' => array(
          'value' => '[node:og-group-ref:Nth:uuid]',
          'type' => '',
        ),
        'image_display_url' => array(
          'value' => '[node:og-group-ref:Nth:field_image]',
          'type' => '',
        ),
        'title' => array(
          'value' => '[node:og-group-ref:Nth:title]',
          'type' => '',
        ),
        'name' => array(
          'value' => '[node:og-group-ref:Nth:url:path]',
          'type' => '',
        ),
      ),
      'extras' => array(
        'odsm_entity_reference' => array(
          'value' => 'field_additional_info',
          'type' => '',
        ),
        'id' => array(
          'value' => '',
          'type' => '',
        ),
        'key' => array(
          'value' => '[node:field-additional-info:Nth:first]',
          'type' => '',
        ),
        'value' => array(
          'value' => '[node:field-additional-info:Nth:second]',
          'type' => '',
        ),
        'state' => array(
          'value' => '',
          'type' => '',
        ),
        'revision_timestamp' => array(
          'value' => '',
          'type' => '',
        ),
      ),
    ),
    'outputformat' => 'json',
    'endpoint' => 'api/3/action/current_package_list_with_resources',
    'arguments' => array(
      1 => array(
        'field' => 'limit',
        'required' => 0,
        'value' => '',
      ),
      2 => array(
        'field' => 'offset',
        'required' => 0,
      ),
    ),
    'xml_root' => 'records',
    'xml_defaulttag' => 'record',
  );
  // Exported Open Data API Map: CKAN Group List.
  $open_data_schema_apis['ckan_group_list'] = array(
    'name' => 'CKAN Group List',
    'enabled' => 1,
    'machine_name' => 'ckan_group_list',
    'api_schema' => 'ckan_dataset',
    'description' => NULL,
    'type' => 'node',
    'bundle' => 'group',
    'mapping' => array(
      'id' => array(
        'value' => '',
        'type' => 'string',
      ),
      'revision_id' => array(
        'value' => '',
        'type' => 'string',
      ),
      'name' => array(
        'value' => '[node:url:args:last]',
        'type' => 'string',
      ),
      'title' => array(
        'value' => '',
        'type' => 'string',
      ),
      'author' => array(
        'value' => '',
        'type' => 'string',
      ),
      'author_email' => array(
        'value' => '',
        'type' => 'string',
      ),
      'maintainer' => array(
        'value' => '',
        'type' => 'string',
      ),
      'maintainer_email' => array(
        'value' => '',
        'type' => 'string',
      ),
      'license_title' => array(
        'value' => '',
        'type' => 'string',
      ),
      'license_id' => array(
        'value' => '',
        'type' => 'string',
      ),
      'notes' => array(
        'value' => '',
        'type' => 'string',
      ),
      'url' => array(
        'value' => '',
        'type' => 'string',
      ),
      'version' => array(
        'value' => '',
        'type' => 'string',
      ),
      'state' => array(
        'value' => '',
        'type' => 'string',
      ),
      'owner_org' => array(
        'value' => '',
        'type' => 'string',
      ),
      'log_message' => array(
        'value' => '',
        'type' => 'string',
      ),
      'private' => array(
        'value' => '',
        'type' => 'boolean',
      ),
      'relationships_as_object' => array(
        'value' => '',
        'type' => 'object',
      ),
      'revision_timestamp' => array(
        'value' => '',
        'type' => 'string',
      ),
      'metadata_created' => array(
        'value' => '',
        'type' => 'string',
      ),
      'metadata_modified' => array(
        'value' => '',
        'type' => 'string',
      ),
      'creator_user_id' => array(
        'value' => '',
        'type' => 'string',
      ),
      'type' => array(
        'value' => '',
        'type' => 'string',
      ),
      'resources' => array(
        'odsm_entity_reference' => array(
          'value' => '',
          'type' => '',
        ),
        'id' => array(
          'value' => '',
          'type' => '',
        ),
        'revision_id' => array(
          'value' => '',
          'type' => '',
        ),
        'url' => array(
          'value' => '',
          'type' => '',
        ),
        'description' => array(
          'value' => '',
          'type' => '',
        ),
        'format' => array(
          'value' => '',
          'type' => '',
        ),
        'hash' => array(
          'value' => '',
          'type' => '',
        ),
        'state' => array(
          'value' => '',
          'type' => '',
        ),
        'Position' => array(
          'value' => '',
          'type' => '',
        ),
        'revision_timestamp' => array(
          'value' => '',
          'type' => '',
        ),
        'name' => array(
          'value' => '',
          'type' => '',
        ),
        'resource_type' => array(
          'value' => '',
          'type' => '',
        ),
        'url_type' => array(
          'value' => '',
          'type' => '',
        ),
        'mimetype' => array(
          'value' => '',
          'type' => '',
        ),
        'webstore_url' => array(
          'value' => '',
          'type' => '',
        ),
        'size' => array(
          'value' => '',
          'type' => '',
        ),
        'created' => array(
          'value' => '',
          'type' => '',
        ),
        'resource_group_id' => array(
          'value' => '',
          'type' => '',
        ),
        'cache_last_updated' => array(
          'value' => '',
          'type' => '',
        ),
        'webstore_last_updated' => array(
          'value' => '',
          'type' => '',
        ),
        'datastore_active' => array(
          'value' => '',
          'type' => '',
        ),
        'last_modified' => array(
          'value' => '',
          'type' => '',
        ),
      ),
      'tags' => array(
        'odsm_entity_reference' => array(
          'value' => '',
          'type' => '',
        ),
        'id' => array(
          'value' => '',
          'type' => '',
        ),
        'vocabulary_id' => array(
          'value' => '',
          'type' => '',
        ),
        'name' => array(
          'value' => '',
          'type' => '',
        ),
        'revision_timestamp' => array(
          'value' => '',
          'type' => '',
        ),
        'state' => array(
          'value' => '',
          'type' => '',
        ),
      ),
      'groups' => array(
        'odsm_entity_reference' => array(
          'value' => '',
          'type' => '',
        ),
        'display_name' => array(
          'value' => '',
          'type' => '',
        ),
        'description' => array(
          'value' => '',
          'type' => '',
        ),
        'id' => array(
          'value' => '[node:uuid]',
          'type' => '',
        ),
        'image_display_url' => array(
          'value' => '',
          'type' => '',
        ),
        'title' => array(
          'value' => '',
          'type' => '',
        ),
        'name' => array(
          'value' => '[node:url]',
          'type' => '',
        ),
      ),
      'extras' => array(
        'odsm_entity_reference' => array(
          'value' => '',
          'type' => '',
        ),
        'id' => array(
          'value' => '',
          'type' => '',
        ),
        'key' => array(
          'value' => '',
          'type' => '',
        ),
        'value' => array(
          'value' => '',
          'type' => '',
        ),
        'state' => array(
          'value' => '',
          'type' => '',
        ),
        'revision_timestamp' => array(
          'value' => '',
          'type' => '',
        ),
      ),
    ),
    'outputformat' => 'json',
    'endpoint' => 'api/3/action/group_list',
    'arguments' => array(),
    'xml_root' => 'records',
    'xml_defaulttag' => 'record',
  );
  // Exported Open Data API Map: CKAN Group Package Show.
  $open_data_schema_apis['ckan_group_package_show'] = array(
    'name' => 'CKAN Group Package Show',
    'enabled' => 1,
    'machine_name' => 'ckan_group_package_show',
    'api_schema' => 'ckan_dataset',
    'description' => NULL,
    'type' => 'node',
    'bundle' => 'dataset',
    'mapping' => array(
      'id' => array(
        'value' => '[node:uuid]',
        'type' => 'string',
      ),
      'revision_id' => array(
        'value' => '[node:vuuid]',
        'type' => 'string',
      ),
      'name' => array(
        'value' => '[node:url:args:last]',
        'type' => 'string',
      ),
      'title' => array(
        'value' => '[node:title]',
        'type' => 'string',
      ),
      'author' => array(
        'value' => '[node:field_author]',
        'type' => 'string',
      ),
      'author_email' => array(
        'value' => '[node:field-contact-email]',
        'type' => 'string',
      ),
      'maintainer' => array(
        'value' => '[site:name]',
        'type' => 'string',
      ),
      'maintainer_email' => array(
        'value' => '[site:mail]',
        'type' => 'string',
      ),
      'license_title' => array(
        'value' => '[node:field_license]',
        'type' => 'string',
      ),
      'license_id' => array(
        'value' => '',
        'type' => 'string',
      ),
      'notes' => array(
        'value' => '[node:body]',
        'type' => 'string',
      ),
      'url' => array(
        'value' => '[node:url]',
        'type' => 'string',
      ),
      'version' => array(
        'value' => '',
        'type' => 'string',
      ),
      'state' => array(
        'value' => 'Active',
        'type' => 'string',
      ),
      'owner_org' => array(
        'value' => '',
        'type' => 'string',
      ),
      'log_message' => array(
        'value' => '[node:log]',
        'type' => 'string',
      ),
      'private' => array(
        'value' => '[node:status]',
        'type' => 'boolean',
      ),
      'relationships_as_object' => array(
        'value' => '',
        'type' => 'object',
      ),
      'revision_timestamp' => array(
        'value' => '[node:changed]',
        'type' => 'string',
      ),
      'metadata_created' => array(
        'value' => '[node:field_harvest_source_issued] || [node:created]',
        'type' => 'string',
      ),
      'metadata_modified' => array(
        'value' => '[node:field_harvest_source_modified] || [node:changed]',
        'type' => 'string',
      ),
      'creator_user_id' => array(
        'value' => '[node:author:uuid]',
        'type' => 'string',
      ),
      'type' => array(
        'value' => 'Dataset',
        'type' => 'string',
      ),
      'resources' => array(
        'odsm_entity_reference' => array(
          'value' => 'field_resources',
          'type' => '',
        ),
        'id' => array(
          'value' => '[node:field-resources:Nth:uuid]',
          'type' => '',
        ),
        'revision_id' => array(
          'value' => '[node:field-resources:Nth:vuuid]',
          'type' => '',
        ),
        'url' => array(
          'value' => '[node:field-resources:Nth:url]',
          'type' => '',
        ),
        'description' => array(
          'value' => '[node:field-resources:Nth:body]',
          'type' => '',
        ),
        'format' => array(
          'value' => '[node:field-resources:Nth:field_format]',
          'type' => '',
        ),
        'hash' => array(
          'value' => '',
          'type' => '',
        ),
        'state' => array(
          'value' => '[node:field-resources:Nth:state]',
          'type' => '',
        ),
        'Position' => array(
          'value' => '',
          'type' => '',
        ),
        'revision_timestamp' => array(
          'value' => '[node:field-resources:Nth:changed]',
          'type' => '',
        ),
        'name' => array(
          'value' => '[node:field-resources:Nth:title]',
          'type' => '',
        ),
        'resource_type' => array(
          'value' => '',
          'type' => '',
        ),
        'url_type' => array(
          'value' => '',
          'type' => '',
        ),
        'mimetype' => array(
          'value' => '[node:field-resources:Nth:field-upload:mime] || [node:field-resources:Nth:field_format]',
          'type' => '',
        ),
        'webstore_url' => array(
          'value' => '',
          'type' => '',
        ),
        'size' => array(
          'value' => '[node:field-resources:Nth:field-upload:size]',
          'type' => '',
        ),
        'created' => array(
          'value' => '[node:field-resources:Nth:created]',
          'type' => '',
        ),
        'resource_group_id' => array(
          'value' => '[node:field-resources:Nth:og-group-ref:0:uuid]',
          'type' => '',
        ),
        'cache_last_updated' => array(
          'value' => '',
          'type' => '',
        ),
        'webstore_last_updated' => array(
          'value' => '',
          'type' => '',
        ),
        'datastore_active' => array(
          'value' => '',
          'type' => '',
        ),
        'last_modified' => array(
          'value' => 'Date changed  [node:field-resources:Nth:changed]',
          'type' => '',
        ),
      ),
      'tags' => array(
        'odsm_entity_reference' => array(
          'value' => 'field_tags',
          'type' => '',
        ),
        'id' => array(
          'value' => '[node:field-tags:Nth:uuid]',
          'type' => '',
        ),
        'vocabulary_id' => array(
          'value' => '[node:field-tags:Nth:vocabulary:vid]',
          'type' => '',
        ),
        'name' => array(
          'value' => '[node:field-tags:Nth:name]',
          'type' => '',
        ),
        'revision_timestamp' => array(
          'value' => '',
          'type' => '',
        ),
        'state' => array(
          'value' => '',
          'type' => '',
        ),
      ),
      'groups' => array(
        'odsm_entity_reference' => array(
          'value' => 'og_group_ref',
          'type' => '',
        ),
        'display_name' => array(
          'value' => '[node:title]',
          'type' => '',
        ),
        'description' => array(
          'value' => '[node:og-group-ref:Nth:body]',
          'type' => '',
        ),
        'id' => array(
          'value' => '[node:og-group-ref:Nth:uuid]',
          'type' => '',
        ),
        'image_display_url' => array(
          'value' => '[node:og-group-ref:Nth:field_image]',
          'type' => '',
        ),
        'title' => array(
          'value' => '[node:og-group-ref:Nth:title]',
          'type' => '',
        ),
        'name' => array(
          'value' => '[node:url:args:last]',
          'type' => '',
        ),
      ),
      'extras' => array(
        'odsm_entity_reference' => array(
          'value' => 'field_additional_info',
          'type' => '',
        ),
        'id' => array(
          'value' => '',
          'type' => '',
        ),
        'key' => array(
          'value' => '[node:field-additional-info:Nth:first]',
          'type' => '',
        ),
        'value' => array(
          'value' => '[node:field-additional-info:Nth:second]',
          'type' => '',
        ),
        'state' => array(
          'value' => '',
          'type' => '',
        ),
        'revision_timestamp' => array(
          'value' => '',
          'type' => '',
        ),
      ),
    ),
    'outputformat' => 'json',
    'endpoint' => 'api/3/action/group_package_show',
    'arguments' => array(
      1 => array(
        'field' => 'id',
        'required' => 1,
      ),
    ),
    'xml_root' => 'records',
    'xml_defaulttag' => 'record',
  );
  // Exported Open Data API Map: CKAN Package Show.
  $open_data_schema_apis['ckan_package_show'] = array(
    'name' => 'CKAN Package Show',
    'enabled' => 1,
    'machine_name' => 'ckan_package_show',
    'api_schema' => 'ckan_dataset',
    'description' => NULL,
    'type' => 'node',
    'bundle' => 'dataset',
    'mapping' => array(
      'id' => array(
        'value' => '[node:uuid]',
        'type' => 'string',
      ),
      'revision_id' => array(
        'value' => '[node:vuuid]',
        'type' => 'string',
      ),
      'name' => array(
        'value' => '[node:url:args:last]',
        'type' => 'string',
      ),
      'title' => array(
        'value' => '[node:title]',
        'type' => 'string',
      ),
      'author' => array(
        'value' => '[node:field_author]',
        'type' => 'string',
      ),
      'author_email' => array(
        'value' => '[node:field-contact-email]',
        'type' => 'string',
      ),
      'maintainer' => array(
        'value' => '[site:name]',
        'type' => 'string',
      ),
      'maintainer_email' => array(
        'value' => '[site:mail]',
        'type' => 'string',
      ),
      'license_title' => array(
        'value' => '[node:field_license]',
        'type' => 'string',
      ),
      'license_id' => array(
        'value' => '',
        'type' => 'string',
      ),
      'notes' => array(
        'value' => '[node:body]',
        'type' => 'string',
      ),
      'url' => array(
        'value' => '[node:url]',
        'type' => 'string',
      ),
      'version' => array(
        'value' => '',
        'type' => 'string',
      ),
      'state' => array(
        'value' => 'Active',
        'type' => 'string',
      ),
      'owner_org' => array(
        'value' => '',
        'type' => 'string',
      ),
      'log_message' => array(
        'value' => '[node:log]',
        'type' => 'string',
      ),
      'private' => array(
        'value' => '[node:status]',
        'type' => 'boolean',
      ),
      'relationships_as_object' => array(
        'value' => '',
        'type' => 'object',
      ),
      'revision_timestamp' => array(
        'value' => '[node:changed]',
        'type' => 'string',
      ),
      'metadata_created' => array(
        'value' => '[node:field_harvest_source_issued] || [node:created]',
        'type' => 'string',
      ),
      'metadata_modified' => array(
        'value' => '[node:field_harvest_source_modified] || [node:changed]',
        'type' => 'string',
      ),
      'creator_user_id' => array(
        'value' => '[node:author:uuid]',
        'type' => 'string',
      ),
      'type' => array(
        'value' => 'Dataset',
        'type' => 'string',
      ),
      'resources' => array(
        'odsm_entity_reference' => array(
          'value' => 'field_resources',
          'type' => '',
        ),
        'id' => array(
          'value' => '[node:field-resources:Nth:uuid]',
          'type' => '',
        ),
        'revision_id' => array(
          'value' => '[node:field-resources:Nth:vuuid]',
          'type' => '',
        ),
        'url' => array(
          'value' => '[node:field-resources:Nth:field-link-api:url] || [node:field-resources:Nth:field_link_remote_file] || [node:field-resources:Nth:field-upload:url]',
          'type' => '',
        ),
        'description' => array(
          'value' => '[node:field-resources:Nth:body]',
          'type' => '',
        ),
        'format' => array(
          'value' => '[node:field-resources:Nth:field_format]',
          'type' => '',
        ),
        'hash' => array(
          'value' => '',
          'type' => '',
        ),
        'state' => array(
          'value' => 'Active',
          'type' => '',
        ),
        'Position' => array(
          'value' => '',
          'type' => '',
        ),
        'revision_timestamp' => array(
          'value' => '[node:field-resources:Nth:changed]',
          'type' => '',
        ),
        'name' => array(
          'value' => '[node:field-resources:Nth:title]',
          'type' => '',
        ),
        'resource_type' => array(
          'value' => '',
          'type' => '',
        ),
        'url_type' => array(
          'value' => '',
          'type' => '',
        ),
        'mimetype' => array(
          'value' => '[node:field-resources:Nth:field-upload:mime] || [node:field-resources:Nth:field_format]',
          'type' => '',
        ),
        'webstore_url' => array(
          'value' => '',
          'type' => '',
        ),
        'size' => array(
          'value' => '[node:field-resources:Nth:field-upload:size]',
          'type' => '',
        ),
        'created' => array(
          'value' => '[node:field-resources:Nth:created]',
          'type' => '',
        ),
        'resource_group_id' => array(
          'value' => '[node:field-resources:Nth:og-group-ref:0:uuid]',
          'type' => '',
        ),
        'cache_last_updated' => array(
          'value' => '',
          'type' => '',
        ),
        'webstore_last_updated' => array(
          'value' => '',
          'type' => '',
        ),
        'datastore_active' => array(
          'value' => '',
          'type' => '',
        ),
        'last_modified' => array(
          'value' => 'Date changed  [node:field-resources:Nth:changed]',
          'type' => '',
        ),
      ),
      'tags' => array(
        'odsm_entity_reference' => array(
          'value' => 'field_tags',
          'type' => '',
        ),
        'id' => array(
          'value' => '[node:field-tags:Nth:uuid]',
          'type' => '',
        ),
        'vocabulary_id' => array(
          'value' => '[node:field-tags:Nth:vocabulary:vid]',
          'type' => '',
        ),
        'name' => array(
          'value' => '[node:field-tags:Nth:name]',
          'type' => '',
        ),
        'revision_timestamp' => array(
          'value' => '',
          'type' => '',
        ),
        'state' => array(
          'value' => '',
          'type' => '',
        ),
      ),
      'groups' => array(
        'odsm_entity_reference' => array(
          'value' => 'og_group_ref',
          'type' => '',
        ),
        'display_name' => array(
          'value' => '',
          'type' => '',
        ),
        'description' => array(
          'value' => '[node:og-group-ref:Nth:body]',
          'type' => '',
        ),
        'id' => array(
          'value' => '[node:og-group-ref:Nth:uuid]',
          'type' => '',
        ),
        'image_display_url' => array(
          'value' => '[node:og-group-ref:Nth:field_image]',
          'type' => '',
        ),
        'title' => array(
          'value' => '[node:og-group-ref:Nth:title]',
          'type' => '',
        ),
        'name' => array(
          'value' => '[node:og-group-ref:Nth:url:path]',
          'type' => '',
        ),
      ),
      'extras' => array(
        'odsm_entity_reference' => array(
          'value' => 'field_additional_info',
          'type' => '',
        ),
        'id' => array(
          'value' => '',
          'type' => '',
        ),
        'key' => array(
          'value' => '[node:field-additional-info:Nth:first]',
          'type' => '',
        ),
        'value' => array(
          'value' => '[node:field-additional-info:Nth:second]',
          'type' => '',
        ),
        'state' => array(
          'value' => '',
          'type' => '',
        ),
        'revision_timestamp' => array(
          'value' => '',
          'type' => '',
        ),
      ),
    ),
    'outputformat' => 'json',
    'endpoint' => 'api/3/action/package_show',
    'arguments' => array(
      1 => array(
        'field' => 'name',
        'required' => 0,
      ),
      2 => array(
        'field' => 'id',
        'required' => 1,
      ),
    ),
    'xml_root' => 'records',
    'xml_defaulttag' => 'record',
  );
  // Exported Open Data API Map: CKAN Resource Show.
  $open_data_schema_apis['ckan_resource_show'] = array(
    'name' => 'CKAN Resource Show',
    'enabled' => 1,
    'machine_name' => 'ckan_resource_show',
    'api_schema' => 'ckan_resource',
    'description' => NULL,
    'type' => 'node',
    'bundle' => 'resource',
    'mapping' => array(
      'id' => array(
        'value' => '[node:uuid]',
        'type' => 'string',
      ),
      'revision_id' => array(
        'value' => '[node:vuuid]',
        'type' => 'string',
      ),
      'url' => array(
        'value' => '[node:field-link-api:url] || [node:field_link_remote_file] || [node:field-upload:url]',
        'type' => 'string',
      ),
      'description' => array(
        'value' => '[node:body]',
        'type' => 'string',
      ),
      'format' => array(
        'value' => '[node:field_format]',
        'type' => 'string',
      ),
      'hash' => array(
        'value' => '',
        'type' => 'string',
      ),
      'state' => array(
        'value' => '',
        'type' => 'string',
      ),
      'Position' => array(
        'value' => '',
        'type' => 'string',
      ),
      'revision_timestamp' => array(
        'value' => '[node:changed]',
        'type' => 'string',
      ),
      'name' => array(
        'value' => '[node:title]',
        'type' => 'string',
      ),
      'resource_type' => array(
        'value' => '',
        'type' => 'string',
      ),
      'url_type' => array(
        'value' => '',
        'type' => 'string',
      ),
      'mimetype' => array(
        'value' => '[node:field-upload:mime] || [node:field_format]',
        'type' => 'string',
      ),
      'webstore_url' => array(
        'value' => '',
        'type' => 'string',
      ),
      'size' => array(
        'value' => '[node:field-upload:size]',
        'type' => 'string',
      ),
      'created' => array(
        'value' => '[node:created]',
        'type' => 'string',
      ),
      'resource_group_id' => array(
        'value' => '[node:og-group-ref:0:uuid]',
        'type' => 'string',
      ),
      'cache_last_updated' => array(
        'value' => '',
        'type' => 'string',
      ),
      'webstore_last_updated' => array(
        'value' => '',
        'type' => 'string',
      ),
      'datastore_active' => array(
        'value' => '',
        'type' => 'string',
      ),
      'last_modified' => array(
        'value' => 'Date changed  [node:changed]',
        'type' => 'string',
      ),
    ),
    'outputformat' => 'json',
    'endpoint' => 'api/3/action/resource_show',
    'arguments' => array(
      1 => array(
        'field' => 'id',
        'required' => 1,
      ),
      2 => array(
        'field' => 'name',
        'required' => 0,
      ),
    ),
    'xml_root' => 'records',
    'xml_defaulttag' => 'record',
  );
  // Exported Open Data API Map: Data.json.
  $open_data_schema_apis['data_json'] = array(
    'name' => 'Data.json',
    'enabled' => 0,
    'machine_name' => 'data_json',
    'api_schema' => 'pod',
    'description' => NULL,
    'type' => 'node',
    'bundle' => 'dataset',
    'mapping' => array(
      'accessLevel' => array(
        'value' => '[node:field_public_access_level]',
        'type' => 'string',
      ),
      'accessLevelComment' => array(
        'value' => '',
        'type' => 'string',
      ),
      'accessURL' => array(
        'value' => '',
        'type' => 'string',
      ),
      'accrualPeriodicity' => array(
        'value' => '[node:field-frequency]',
        'type' => 'string',
      ),
      'bureauCode' => array(
        'value' => '',
        'type' => 'array',
      ),
      'contactPoint' => array(
        'value' => '',
        'type' => 'string',
      ),
      'dataDictionary' => array(
        'value' => '[node:field-data-dictionary]',
        'type' => 'string',
      ),
      'dataQuality' => array(
        'value' => '',
        'type' => 'boolean',
      ),
      'description' => array(
        'value' => '[node:body]',
        'type' => 'string',
      ),
      'distribution' => array(
        'odsm_entity_reference' => array(
          'value' => 'field_resources',
          'type' => '',
        ),
        'accessURL' => array(
          'value' => '[node:field-resources:Nth:field-link-api:url] || [node:field-resources:Nth:field_link_remote_file] || [node:field-resources:Nth:field-upload:url]',
          'type' => '',
        ),
        'format' => array(
          'value' => '[node:field-resources:Nth:field-format:name]',
          'type' => '',
        ),
      ),
      'format' => array(
        'value' => '',
        'type' => 'string',
      ),
      'identifier' => array(
        'value' => '[node:uuid]',
        'type' => 'string',
      ),
      'issued' => array(
        'value' => '[node:field-harvest-source-issued:custom:Y-m-d] || [node:created:custom:Y-m-d]',
        'type' => 'string',
      ),
      'keyword' => array(
        'value' => '[node:field-tags]',
        'type' => 'array',
      ),
      'landingPage' => array(
        'value' => '',
        'type' => 'string',
      ),
      'language' => array(
        'value' => '[node:field_language]',
        'type' => 'array',
      ),
      'license' => array(
        'value' => '[node:field_license]',
        'type' => 'string',
      ),
      'mbox' => array(
        'value' => '[node:field-contact-email]',
        'type' => 'string',
      ),
      'modified' => array(
        'value' => '[node:field-harvest-source-modified:custom:Y-m-d] || [node:changed:custom:Y-m-d]',
        'type' => 'string',
      ),
      'PrimaryITInvestmentUII' => array(
        'value' => '',
        'type' => 'string',
      ),
      'programCode' => array(
        'value' => '',
        'type' => 'array',
      ),
      'publisher' => array(
        'value' => '[node:og_group_ref]',
        'type' => 'string',
      ),
      'references' => array(
        'value' => '[node:field_related_content]',
        'type' => 'array',
      ),
      'spatial' => array(
        'value' => '[node:field_spatial]',
        'type' => 'string',
      ),
      'systemOfRecords' => array(
        'value' => '',
        'type' => 'string',
      ),
      'temporal' => array(
        'value' => '[node:field_temporal_coverage]',
        'type' => 'string',
      ),
      'theme' => array(
        'value' => '',
        'type' => 'array',
      ),
      'title' => array(
        'value' => '[node:title]',
        'type' => 'string',
      ),
      'webService' => array(
        'value' => '',
        'type' => 'string',
      ),
    ),
    'outputformat' => 'json',
    'endpoint' => 'data.json',
    'arguments' => array(),
    'xml_root' => 'records',
    'xml_defaulttag' => 'record',
  );
  // Exported Open Data API Map: Data Json 1.1.
  $open_data_schema_apis['data_json_1_1'] = array(
    'name' => 'Data Json 1.1',
    'enabled' => 1,
    'machine_name' => 'data_json_1_1',
    'api_schema' => 'pod_v1_1',
    'description' => NULL,
    'type' => 'node',
    'bundle' => 'dataset',
    'mapping' => array(
      '@type' => array(
        'value' => 'dcat:Dataset',
        'type' => 'string',
      ),
      'accessLevel' => array(
        'value' => '[node:field-public-access-level]',
        'type' => 'string',
      ),
      'rights' => array(
        'value' => '[node:field_rights]',
        'type' => 'string',
      ),
      'accrualPeriodicity' => array(
        'value' => '[node:field-frequency]',
        'type' => 'string',
      ),
      'bureauCode' => array(
        'value' => '[node:field-odfe-bureau-code]',
        'type' => 'array',
      ),
      'contactPoint' => array(
        '@type' => array(
          'value' => '',
          'type' => '',
        ),
        'fn' => array(
          'value' => '[node:field_contact_name] || [node:author:name]',
          'type' => '',
        ),
        'hasEmail' => array(
          'value' => 'mailto:[node:field-contact-email]',
          'type' => '',
        ),
      ),
      'describedBy' => array(
        'value' => '[node:field-data-dictionary]',
        'type' => 'string',
      ),
      'describedByType' => array(
        'value' => '[node:field_data_dictionary_type]',
        'type' => 'string',
      ),
      'conformsTo' => array(
        'value' => '[node:field-conforms-to:url]',
        'type' => 'string',
      ),
      'dataQuality' => array(
        'value' => '[node:field_odfe_data_quality]',
        'type' => 'boolean',
      ),
      'description' => array(
        'value' => '[node:body]',
        'type' => 'string',
      ),
      'distribution' => array(
        'odsm_entity_reference' => array(
          'value' => 'field_resources',
          'type' => '',
        ),
        '@type' => array(
          'value' => 'dcat:Distribution',
          'type' => '',
        ),
        'downloadURL' => array(
          'value' => '[node:field-resources:Nth:field_link_remote_file] || [node:field-resources:Nth:field-upload:url]',
          'type' => '',
        ),
        'mediaType' => array(
          'value' => '[node:field-resources:Nth:field-upload:mime]',
          'type' => '',
        ),
        'format' => array(
          'value' => '[node:field-resources:Nth:field-format:name]',
          'type' => '',
        ),
        'accessURL' => array(
          'value' => '[node:field-resources:Nth:field-link-api:url]',
          'type' => '',
        ),
        'description' => array(
          'value' => '[node:field-resources:Nth:body]',
          'type' => '',
        ),
        'title' => array(
          'value' => '[node:field-resources:Nth:title]',
          'type' => '',
        ),
      ),
      'identifier' => array(
        'value' => '[node:uuid]',
        'type' => 'string',
      ),
      'issued' => array(
        'value' => '[node:field-harvest-source-issued:custom:Y-m-d] || [node:created:custom:Y-m-d]',
        'type' => 'string',
      ),
      'keyword' => array(
        'value' => '[node:field-tags]',
        'type' => 'array',
      ),
      'landingPage' => array(
        'value' => '[node:field-landing-page:url] || [node:url]',
        'type' => 'string',
      ),
      'language' => array(
        'value' => '[node:field_language]',
        'type' => 'array',
      ),
      'license' => array(
        'value' => '[node:field_license]',
        'type' => 'string',
      ),
      'modified' => array(
        'value' => '[node:field-harvest-source-modified:custom:Y-m-d] || [node:changed:custom:Y-m-d]',
        'type' => 'string',
      ),
      'primaryITInvestmentUII' => array(
        'value' => '[node:field_odfe_investment_uii]',
        'type' => 'string',
      ),
      'programCode' => array(
        'value' => '[node:field-odfe-program-code]',
        'type' => 'array',
      ),
      'publisher' => array(
        '@type' => array(
          'value' => 'org:Organization',
          'type' => '',
        ),
        'name' => array(
          'value' => '[node:og_group_ref]',
          'type' => '',
        ),
        'subOrganizationOf' => array(
          'value' => '',
          'type' => '',
        ),
      ),
      'references' => array(
        'value' => '[node:field_related_content]',
        'type' => 'array',
      ),
      'spatial' => array(
        'value' => '[node:field_spatial]',
        'type' => 'string',
      ),
      'systemOfRecords' => array(
        'value' => '[node:field-odfe-system-of-records:url]',
        'type' => 'string',
      ),
      'temporal' => array(
        'value' => '[node:field-temporal-coverage:value:custom:c]/[node:field-temporal-coverage:value2:custom:c]',
        'type' => 'string',
      ),
      'isPartOf' => array(
        'value' => '[node:field_is_part_of]',
        'type' => 'string',
      ),
      'theme' => array(
        'value' => '[node:field_pod_theme] || [node:field_topic]',
        'type' => 'array',
      ),
      'title' => array(
        'value' => '[node:title]',
        'type' => 'string',
      ),
    ),
    'outputformat' => 'json',
    'endpoint' => 'data.json',
    'arguments' => array(
      1 => array(
        'field' => 'identifier',
        'required' => 0,
      ),
    ),
    'xml_root' => 'records',
    'xml_defaulttag' => 'record',
  );
  // Exported Open Data API Map: DCAT-AP v1.1 (Dataset)
  $open_data_schema_apis['dcat_ap_v1_1_dataset'] = array(
    'name' => 'DCAT-AP v1.1 (Dataset)',
    'enabled' => 1,
    'machine_name' => 'dcat_ap_v1_1_dataset',
    'api_schema' => 'dcat_dataset',
    'description' => NULL,
    'type' => 'node',
    'bundle' => 'dataset',
    'mapping' => array(
      '@rdf:about' => array(
        'value' => '[node:url]',
        'type' => 'string',
      ),
      'dct:title' => array(
        'value' => '[node:title]',
        'type' => 'string',
      ),
      'dct:description' => array(
        'value' => '[node:body]',
        'type' => 'string',
      ),
      'dcat:keyword' => array(
        'value' => '[node:field-tags]',
        'type' => 'array',
      ),
      'dcat:theme' => array(
        'value' => '[node:field_pod_theme] || [node:field_topic]',
        'type' => 'string',
      ),
      'dct:identifier' => array(
        'value' => '[node:uuid]',
        'type' => 'string',
      ),
      'dct:issued' => array(
        'value' => '[node:field-harvest-source-issued:custom:c] || [node:created:custom:c]',
        'type' => 'string',
      ),
      'dct:modified' => array(
        'value' => '[node:field-harvest-source-modified:custom:c] || [node:changed:custom:c]',
        'type' => 'string',
      ),
      'owl:versionInfo' => array(
        'value' => '',
        'type' => 'string',
      ),
      'adms:versionNotes' => array(
        'value' => '',
        'type' => 'string',
      ),
      'dct:language' => array(
        'value' => '[node:field_language]',
        'type' => 'array',
      ),
      'dcat:landingPage' => array(
        'value' => '[node:field-landing-page:url]',
        'type' => 'string',
      ),
      'dct:accrualPeriodicity' => array(
        'value' => '[node:field-frequency]',
        'type' => 'string',
      ),
      'dct:conformsTo' => array(
        'value' => '[node:field-conforms-to:url]',
        'type' => 'array',
      ),
      'dct:accessRights' => array(
        'value' => '[node:field_rights]',
        'type' => 'string',
      ),
      'dct:provenance' => array(
        'value' => '',
        'type' => 'string',
      ),
      'dct:type' => array(
        'value' => '',
        'type' => 'string',
      ),
      'dct:hasVersion' => array(
        'value' => '',
        'type' => 'array',
      ),
      'dct:isVersionOf' => array(
        'value' => '',
        'type' => 'array',
      ),
      'dct:source' => array(
        'value' => '',
        'type' => 'array',
      ),
      'adms:sample' => array(
        'value' => '',
        'type' => 'array',
      ),
      'dct:spatial' => array(
        'value' => '[node:field_spatial]',
        'type' => 'string',
      ),
      'dct:temporal' => array(
        'odsm_entity_reference' => array(
          'value' => '',
          'type' => '',
        ),
        'schema:startDate' => array(
          'value' => '[node:field-temporal-coverage:value:custom:c]',
          'type' => '',
        ),
        'schema:endDate' => array(
          'value' => '[node:field-temporal-coverage:value2:custom:c]',
          'type' => '',
        ),
      ),
      'dct:publisher' => array(
        'value' => '[node:og_group_ref]',
        'type' => 'string',
      ),
      'dcat:contactPoint' => array(
        'odsm_entity_reference' => array(
          'value' => '',
          'type' => '',
        ),
        'vcard:fn' => array(
          'value' => '[node:field-contact-name]',
          'type' => '',
        ),
        'vcard:hasTelephone' => array(
          'value' => '',
          'type' => '',
        ),
        'vcard:hasEmail' => array(
          'value' => '[node:field-contact-email]',
          'type' => '',
        ),
        'vcard:hasURL' => array(
          'value' => '',
          'type' => '',
        ),
      ),
      'dcat:Distribution' => array(
        'odsm_entity_reference' => array(
          'value' => 'field_resources',
          'type' => '',
        ),
        'dct:title' => array(
          'value' => '[node:field-resources:Nth:title]',
          'type' => '',
        ),
        'dct:description' => array(
          'value' => '[node:field-resources:Nth:body]',
          'type' => '',
        ),
        'dct:issued' => array(
          'value' => '[node:field-resources:Nth:created:custom:c]',
          'type' => '',
        ),
        'dct:modified' => array(
          'value' => '[node:field-resources:Nth:changed:custom:c]',
          'type' => '',
        ),
        'dct:license' => array(
          'value' => '',
          'type' => '',
        ),
        'dct:rights' => array(
          'value' => '',
          'type' => '',
        ),
        'dcat:accessURL' => array(
          'value' => '[node:field-resources:Nth:field-link-api:url]',
          'type' => '',
        ),
        'dcat:downloadURL' => array(
          'value' => '[node:field-resources:Nth:field_link_remote_file] || [node:field-resources:Nth:field-upload:url]',
          'type' => '',
        ),
        'dcat:mediaType' => array(
          'value' => '[node:field-resources:Nth:field-upload:mime]',
          'type' => '',
        ),
        'dct:format' => array(
          'value' => '[node:field-resources:Nth:field-format:name]',
          'type' => '',
        ),
        'dcat:byteSize' => array(
          'value' => '[node:field-resources:Nth:field-upload:size-raw]',
          'type' => '',
        ),
        'foaf:page' => array(
          'value' => '[node:field-resources:Nth:url]',
          'type' => '',
        ),
      ),
    ),
    'outputformat' => 'xml',
    'endpoint' => 'catalog/dataset.xml',
    'arguments' => array(
      1 => array(
        'field' => 'dct:identifier',
        'required' => 1,
      ),
    ),
    'xml_root' => 'rdf:RDF',
    'xml_defaulttag' => 'dcat:Dataset',
  );
  // Exported Open Data API Map: DCAT-AP v1.1.
  $open_data_schema_apis['dcat_v1_1'] = array(
    'name' => 'DCAT-AP v1.1',
    'enabled' => 1,
    'machine_name' => 'dcat_v1_1',
    'api_schema' => 'dcat',
    'description' => NULL,
    'type' => 'node',
    'bundle' => 'dataset',
    'mapping' => array(
      '@rdf:about' => array(
        'value' => '[node:url]',
        'type' => 'string',
      ),
      'dct:title' => array(
        'value' => '[node:title]',
        'type' => 'string',
      ),
      'dct:description' => array(
        'value' => '[node:body]',
        'type' => 'string',
      ),
      'dcat:keyword' => array(
        'value' => '[node:field-tags]',
        'type' => 'array',
      ),
      'dcat:theme' => array(
        'value' => '[node:field_pod_theme] || [node:field_topic]',
        'type' => 'string',
      ),
      'dct:identifier' => array(
        'value' => '[node:uuid]',
        'type' => 'string',
      ),
      'dct:issued' => array(
        'value' => '[node:field-harvest-source-issued:custom:c] || [node:created:custom:c]',
        'type' => 'string',
      ),
      'dct:modified' => array(
        'value' => '[node:field-harvest-source-modified:custom:c] || [node:changed:custom:c]',
        'type' => 'string',
      ),
      'owl:versionInfo' => array(
        'value' => '',
        'type' => 'string',
      ),
      'adms:versionNotes' => array(
        'value' => '',
        'type' => 'string',
      ),
      'dct:language' => array(
        'value' => '[node:field_language]',
        'type' => 'array',
      ),
      'dcat:landingPage' => array(
        'value' => '[node:field-landing-page:url]',
        'type' => 'string',
      ),
      'dct:accrualPeriodicity' => array(
        'value' => '[node:field-frequency]',
        'type' => 'string',
      ),
      'dct:conformsTo' => array(
        'value' => '[node:field-conforms-to:url]',
        'type' => 'array',
      ),
      'dct:accessRights' => array(
        'value' => '[node:field_rights]',
        'type' => 'string',
      ),
      'dct:provenance' => array(
        'value' => '',
        'type' => 'string',
      ),
      'dct:type' => array(
        'value' => '',
        'type' => 'string',
      ),
      'dct:hasVersion' => array(
        'value' => '',
        'type' => 'array',
      ),
      'dct:isVersionOf' => array(
        'value' => '',
        'type' => 'array',
      ),
      'dct:source' => array(
        'value' => '',
        'type' => 'array',
      ),
      'adms:sample' => array(
        'value' => '',
        'type' => 'array',
      ),
      'dct:spatial' => array(
        'value' => '[node:field_spatial]',
        'type' => 'string',
      ),
      'dct:temporal' => array(
        'odsm_entity_reference' => array(
          'value' => '',
          'type' => '',
        ),
        'schema:startDate' => array(
          'value' => '[node:field-temporal-coverage:value:custom:c]',
          'type' => '',
        ),
        'schema:endDate' => array(
          'value' => '[node:field-temporal-coverage:value2:custom:c]',
          'type' => '',
        ),
      ),
      'dct:publisher' => array(
        'value' => '[node:og_group_ref]',
        'type' => 'string',
      ),
      'dcat:contactPoint' => array(
        'vcard:fn' => array(
          'value' => '[node:field-contact-name]',
          'type' => '',
        ),
        'vcard:hasTelephone' => array(
          'value' => '',
          'type' => '',
        ),
        'vcard:hasEmail' => array(
          'value' => '[node:field-contact-email]',
          'type' => '',
        ),
        'vcard:hasURL' => array(
          'value' => '',
          'type' => '',
        ),
      ),
      'dcat:Distribution' => array(
        'odsm_entity_reference' => array(
          'value' => 'field_resources',
          'type' => '',
        ),
        'dct:title' => array(
          'value' => '[node:field-resources:Nth:title]',
          'type' => '',
        ),
        'dct:description' => array(
          'value' => '[node:field-resources:Nth:body]',
          'type' => '',
        ),
        'dct:issued' => array(
          'value' => '[node:field-resources:Nth:created:custom:c]',
          'type' => '',
        ),
        'dct:modified' => array(
          'value' => '[node:field-resources:Nth:changed:custom:c]',
          'type' => '',
        ),
        'dct:license' => array(
          'value' => '',
          'type' => '',
        ),
        'dct:rights' => array(
          'value' => '',
          'type' => '',
        ),
        'dcat:accessURL' => array(
          'value' => '[node:field-resources:Nth:url]',
          'type' => '',
        ),
        'dcat:downloadURL' => array(
          'value' => '[node:field-resources:Nth:field_link_remote_file] || [node:field-resources:Nth:field-upload:url]',
          'type' => '',
        ),
        'dcat:mediaType' => array(
          'value' => '[node:field-resources:Nth:field-upload:mime]',
          'type' => '',
        ),
        'dct:format' => array(
          'value' => '[node:field-resources:Nth:field-format:name]',
          'type' => '',
        ),
        'dcat:byteSize' => array(
          'value' => '[node:field-resources:Nth:field-upload:size-raw]',
          'type' => '',
        ),
        'foaf:page' => array(
          'value' => '[node:field-resources:Nth:url]',
          'type' => '',
        ),
      ),
    ),
    'outputformat' => 'xml',
    'endpoint' => 'catalog.xml',
    'arguments' => array(),
    'xml_root' => 'rdf:RDF',
    'xml_defaulttag' => 'dcat:Dataset',
  );
  // Exported Open Data API Map: DCAT-AP v1.1 (JSON)
  $open_data_schema_apis['dcat_v1_1_json'] = array(
    'name' => 'DCAT-AP v1.1 (JSON)',
    'enabled' => 1,
    'machine_name' => 'dcat_v1_1_json',
    'api_schema' => 'dcat',
    'description' => NULL,
    'type' => 'node',
    'bundle' => 'dataset',
    'mapping' => array(
      '@rdf:about' => array(
        'value' => '[node:url]',
        'type' => 'string',
      ),
      'dct:title' => array(
        'value' => '[node:title]',
        'type' => 'string',
      ),
      'dct:description' => array(
        'value' => '[node:body]',
        'type' => 'string',
      ),
      'dcat:keyword' => array(
        'value' => '[node:field-tags]',
        'type' => 'array',
      ),
      'dcat:theme' => array(
        'value' => '[node:field_pod_theme] || [node:field_topic]',
        'type' => 'string',
      ),
      'dct:identifier' => array(
        'value' => '[node:uuid]',
        'type' => 'string',
      ),
      'dct:issued' => array(
        'value' => '[node:field-harvest-source-issued:custom:c] || [node:created:custom:c]',
        'type' => 'string',
      ),
      'dct:modified' => array(
        'value' => '[node:field-harvest-source-modified:custom:c] || [node:changed:custom:c]',
        'type' => 'string',
      ),
      'owl:versionInfo' => array(
        'value' => '',
        'type' => 'string',
      ),
      'adms:versionNotes' => array(
        'value' => '',
        'type' => 'string',
      ),
      'dct:language' => array(
        'value' => '[node:field_language]',
        'type' => 'array',
      ),
      'dcat:landingPage' => array(
        'value' => '[node:field-landing-page:url]',
        'type' => 'string',
      ),
      'dct:accrualPeriodicity' => array(
        'value' => '[node:field-frequency]',
        'type' => 'string',
      ),
      'dct:conformsTo' => array(
        'value' => '[node:field-conforms-to:url]',
        'type' => 'array',
      ),
      'dct:accessRights' => array(
        'value' => '[node:field_rights]',
        'type' => 'string',
      ),
      'dct:provenance' => array(
        'value' => '',
        'type' => 'string',
      ),
      'dct:type' => array(
        'value' => '',
        'type' => 'string',
      ),
      'dct:hasVersion' => array(
        'value' => '',
        'type' => 'array',
      ),
      'dct:isVersionOf' => array(
        'value' => '',
        'type' => 'array',
      ),
      'dct:source' => array(
        'value' => '',
        'type' => 'array',
      ),
      'adms:sample' => array(
        'value' => '',
        'type' => 'array',
      ),
      'dct:spatial' => array(
        'value' => '[node:field_spatial]',
        'type' => 'string',
      ),
      'dct:temporal' => array(
        'odsm_entity_reference' => array(
          'value' => '',
          'type' => '',
        ),
        'schema:startDate' => array(
          'value' => '[node:field-temporal-coverage:value:custom:c]',
          'type' => '',
        ),
        'schema:endDate' => array(
          'value' => '[node:field-temporal-coverage:value2:custom:c]',
          'type' => '',
        ),
      ),
      'dct:publisher' => array(
        'value' => '[node:og_group_ref]',
        'type' => 'string',
      ),
      'dcat:contactPoint' => array(
        'vcard:fn' => array(
          'value' => '[node:field-contact-name]',
          'type' => '',
        ),
        'vcard:hasTelephone' => array(
          'value' => '',
          'type' => '',
        ),
        'vcard:hasEmail' => array(
          'value' => '[node:field-contact-email]',
          'type' => '',
        ),
        'vcard:hasURL' => array(
          'value' => '',
          'type' => '',
        ),
      ),
      'dcat:Distribution' => array(
        'odsm_entity_reference' => array(
          'value' => 'field_resources',
          'type' => '',
        ),
        'dct:title' => array(
          'value' => '[node:field-resources:Nth:title]',
          'type' => '',
        ),
        'dct:description' => array(
          'value' => '[node:field-resources:Nth:body]',
          'type' => '',
        ),
        'dct:issued' => array(
          'value' => '[node:field-resources:Nth:created:custom:c]',
          'type' => '',
        ),
        'dct:modified' => array(
          'value' => '[node:field-resources:Nth:changed:custom:c]',
          'type' => '',
        ),
        'dct:license' => array(
          'value' => '',
          'type' => '',
        ),
        'dct:rights' => array(
          'value' => '',
          'type' => '',
        ),
        'dcat:accessURL' => array(
          'value' => '[node:field-resources:Nth:url]',
          'type' => '',
        ),
        'dcat:downloadURL' => array(
          'value' => '[node:field-resources:Nth:field_link_remote_file] || [node:field-resources:Nth:field-upload:url]',
          'type' => '',
        ),
        'dcat:mediaType' => array(
          'value' => '[node:field-resources:Nth:field-upload:mime]',
          'type' => '',
        ),
        'dct:format' => array(
          'value' => '[node:field-resources:Nth:field-format:name]',
          'type' => '',
        ),
        'dcat:byteSize' => array(
          'value' => '[node:field-resources:Nth:field-upload:size-raw]',
          'type' => '',
        ),
        'foaf:page' => array(
          'value' => '[node:field-resources:Nth:url]',
          'type' => '',
        ),
      ),
    ),
    'outputformat' => 'json',
    'endpoint' => 'catalog.json',
    'arguments' => array(),
    'xml_root' => '',
    'xml_defaulttag' => '',
  );
  return $open_data_schema_apis;
}
