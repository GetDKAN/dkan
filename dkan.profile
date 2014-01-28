<?php
/**
 * @file
 * Additional setup tasks for DKAN.
 */

/**
 * Implements hook_install_tasks().
 */
function dkan_install_tasks() {
  $tasks = array();
  $tasks['dkan_additional_setup'] = array(
    'display_name' => 'Cleanup',
  );
  return $tasks;
}

/**
 * Implements hook_install_tasks().
 */
function dkan_additional_setup() {
  // Change block titles for selected blocks.
  db_query("UPDATE {block} SET title ='<none>' WHERE delta = 'main-menu' OR delta = 'login'");
  variable_set('node_access_needs_rebuild', FALSE);
  variable_set('gravatar_size', 190);

  $tags = array(
    'country-afghanistan',
    'election',
    'politics',
    'transparency',
    'municipal',
  );
  foreach ($tags as $tag) {
    $term = new stdClass();
    // 'Tags' vid.
    $term->vid = 2;
    $term->name = $tag;
    taxonomy_term_save($term);
  }
  $formats = array(
    'csv',
    'html',
  );
  foreach ($formats as $format) {
    $term = new stdClass();
    // 'Formats' vid.
    $term->vid = 1;
    $term->name = $format;
    taxonomy_term_save($term);
  }

  // Make sure markdown editor installs correctly.
  module_load_include('install', 'markdowneditor', 'markdowneditor');
  _markdowneditor_insert_latest();
  $data = array(
    'pages' => "node/*\ncomment/*\nsystem/ajax",
    'eid' => 5,
  );
  drupal_write_record('bueditor_editors', $data, array('eid'));

  dkan_default_content_base_install();

  // Add feed source for resources loaded by dkan_default_content.
  $record = array(
    'id' => 'dkan_file',
    'feed_nid' => '10',
    'source' => 'public://Polling_Places_Madison_4.csv',
    'state' => '0',
    'config' => array('FeedsCSVParser'=>array('delimiter' => ",", 'no_headers' => 0, 'encoding' => 'UTF-8'), 'FeedsFileFieldFetcher' => array('fid'=> '36', 'source' => 'public://Polling_Places_Madison_4.csv'), 'FeedsFlatstoreProcessor' => array()),
    'fetcher_result' => '0',
    'imported' => '0',
  );
  drupal_write_record('feeds_source', $record);
  $record = array(
    'id' => 'dkan_file',
    'feed_nid' => '5',
    'source' => 'public://district_centerpoints_4.csv',
    'state' => '0',
    'config' => array('FeedsCSVParser'=>array('delimiter' => ",", 'no_headers' => 0, 'encoding' => 'UTF-8'), 'FeedsFileFieldFetcher' => array('fid'=> '30', 'source' => 'public://district_centerpoints_4.csv'), 'FeedsFlatstoreProcessor' => array()),
    'fetcher_result' => '0',
    'imported' => '0',
  );
  drupal_write_record('feeds_source', $record);
  $record = array(
    'id' => 'dkan_file',
    'feed_nid' => '8',
    'source' => 'public://us_foreclosures_jan_2012_by_state_4.csv',
    'state' => '0',
    'config' => array('FeedsCSVParser'=>array('delimiter' => ",", 'no_headers' => 0, 'encoding' => 'UTF-8'), 'FeedsFileFieldFetcher' => array('fid'=> '30', 'source' => 'public://district_centerpoints_4.csv'), 'FeedsFlatstoreProcessor' => array()),
    'fetcher_result' => '0',
    'imported' => '0',
  );
  drupal_write_record('feeds_source', $record);
  $record = array(
    'id' => 'dkan_file',
    'feed_nid' => '6',
    'source' => 'public://data_4.csv',
    'state' => '0',
    'config' => array('FeedsCSVParser'=>array('delimiter' => ",", 'no_headers' => 0, 'encoding' => 'UTF-8'), 'FeedsFileFieldFetcher' => array('fid'=> '30', 'source' => 'public://district_centerpoints_4.csv'), 'FeedsFlatstoreProcessor' => array()),
    'fetcher_result' => '0',
    'imported' => '0',
  );
  drupal_write_record('feeds_source', $record);
  features_revert(array('dkan_sitewide_search_db' => array('search_api_index')));
  features_revert(array('dkan_dataset_groups' => array('search_api_index')));
  unset($_SESSION['messages']['warning']);
}
