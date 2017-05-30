<?php

/**
 * @file
 * dkan_topics.entity_path.inc
 */

/**
 * Implements hook_entity_path_config_default().
 */
function dkan_topics_entity_path_config_default() {
  $export = array();

  $entity_path_config = new stdClass();
  $entity_path_config->disabled = FALSE; /* Edit this to true to make a default entity_path_config disabled initially */
  $entity_path_config->api_version = 1;
  $entity_path_config->cid = '1';
  $entity_path_config->instance = 'taxonomy_term:dkan_topics';
  $entity_path_config->path_pattern = '/search/field_topic/[term:name]-[term:tid]';
  $entity_path_config->query_pattern = '';
  $entity_path_config->fragment_pattern = '';
  $entity_path_config->config = array(
    'pathauto_cleanstring' => 1,
    'space_separator' => 1,
    'space_separator_char' => '-',
    'path_case' => 'lowercase',
  );
  $export['taxonomy_term:dkan_topics'] = $entity_path_config;

  $entity_path_config = new stdClass();
  $entity_path_config->disabled = FALSE; /* Edit this to true to make a default entity_path_config disabled initially */
  $entity_path_config->api_version = 1;
  $entity_path_config->cid = '2';
  $entity_path_config->instance = 'taxonomy_term:tags';
  $entity_path_config->path_pattern = '/search/field_tags/[term:name]-[term:tid]';
  $entity_path_config->query_pattern = '';
  $entity_path_config->fragment_pattern = '';
  $entity_path_config->config = array(
    'pathauto_cleanstring' => 0,
    'space_separator' => 1,
    'space_separator_char' => '-',
    'path_case' => 'lowercase',
  );
  $export['taxonomy_term:tags'] = $entity_path_config;

  return $export;
}
