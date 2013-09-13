<?php

/**
 * Implements hook_install_tasks()
 */
function dkan_install_tasks() {
  $tasks = array();
  // TODO: Move these to .profile
  $tasks['dkan_additional_setup'] = array(
    'display_name' => 'Cleanup',
  );
  return $tasks;
}

/**
 * Implements hook_install_tasks()
 */
function dkan_additional_setup() {
  // Change block titles for selected blocks.
  db_query("UPDATE {block} SET title ='<none>' WHERE delta = 'main-menu' OR delta = 'login'");
  // Making connections between entity references.
  $values = array(
    array(
      'entity_type' => 'node',
      'bundle' => 'resource',
      'deleted' => 0,
      'entity_id' => 5,
      'revision_id' => 5,
      'language' => 'und',
      'delta' => 0,
      'field_dataset_ref_target_id' => 4,
    ),
    array(
      'entity_type' => 'node',
      'bundle' => 'resource',
      'deleted' => 0,
      'entity_id' => 6,
      'revision_id' => 6,
      'language' => 'und',
      'delta' => 0,
      'field_dataset_ref_target_id' => 7,
    ),
    array(
      'entity_type' => 'node',
      'bundle' => 'resource',
      'deleted' => 0,
      'entity_id' => 8,
      'revision_id' => 8,
      'language' => 'und',
      'delta' => 0,
      'field_dataset_ref_target_id' => 9,
    ),
    array(
      'entity_type' => 'node',
      'bundle' => 'resource',
      'deleted' => 0,
      'entity_id' => 10,
      'revision_id' => 10,
      'language' => 'und',
      'delta' => 0,
      'field_dataset_ref_target_id' => 11,
    ),
  );
  $query = db_insert('field_data_field_dataset_ref')->fields(array('entity_type', 'bundle', 'deleted', 'entity_id', 'revision_id', 'language', 'delta', 'field_dataset_ref_target_id'));
  foreach ($values as $record) {
    $query->values($record);
  }
  $query->execute();

  variable_set('node_access_needs_rebuild', FALSE);
  variable_set('gravatar_size', 190);

  $tags = array(
    'country-afghanistan',
    'election',
    'politics',
    'transparency',
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

  // Add feed source for resources loaded by dkan_default_content.
  $record = array(
    'id' => 'dkan_file',
    'feed_nid' => '10',
    'source' => 'public://Polling_Places_Madison.csv',
    'state' => '0',
    'config' => array('FeedsCSVParser'=>array('delimiter' => ",", 'no_headers' => 0), 'FeedsFileFieldFetcher' => array('fid'=> '36', 'source' => 'public://Polling_Places_Madison.csv'), 'FeedsFlatstoreProcessor' => array()),
    'fetcher_result' => '0',
    'imported' => '0',
  );
  drupal_write_record('feeds_source', $record);
  $record = array(
    'id' => 'dkan_link',
    'feed_nid' => '10',
    'source' => '',
    'state' => '0',
    'config' => array('FeedsCSVParser'=>array('delimiter' => ",", 'no_headers' => 0), 'FeedsFileFieldFetcher' => array('fid'=>'', 'source' =>''), 'FeedsFlatstoreProcessor' => array()),
    'fetcher_result' => '0',
    'imported' => '0',
  );
  drupal_write_record('feeds_source', $record);
  $record = array(
    'id' => 'dkan_file',
    'feed_nid' => '5',
    'source' => 'public://district_centerpoints.csv',
    'state' => '0',
    'config' => array('FeedsCSVParser'=>array('delimiter' => ",", 'no_headers' => 0), 'FeedsFileFieldFetcher' => array('fid'=> '30', 'source' => 'public://public://district_centerpoints.csv'), 'FeedsFlatstoreProcessor' => array()),
    'fetcher_result' => '0',
    'imported' => '0',
  );
  drupal_write_record('feeds_source', $record);
  $record = array(
    'id' => 'dkan_link',
    'feed_nid' => '5',
    'source' => '',
    'state' => '0',
    'config' => array('FeedsCSVParser'=>array('delimiter' => ",", 'no_headers' => 0), 'FeedsFileFieldFetcher' => array('fid'=>'', 'source' =>''), 'FeedsFlatstoreProcessor' => array()),
    'fetcher_result' => '0',
    'imported' => '0',
  );
  drupal_write_record('feeds_source', $record);
  $record = array(
    'id' => 'dkan_file',
    'feed_nid' => '8',
    'source' => 'public://us_foreclosures_jan_2012_by_state.csv',
    'state' => '0',
    'config' => array('FeedsCSVParser'=>array('delimiter' => ",", 'no_headers' => 0), 'FeedsFileFieldFetcher' => array('fid'=> '30', 'source' => 'public://public://district_centerpoints.csv'), 'FeedsFlatstoreProcessor' => array()),
    'fetcher_result' => '0',
    'imported' => '0',
  );
  drupal_write_record('feeds_source', $record);
  $record = array(
    'id' => 'dkan_link',
    'feed_nid' => '8',
    'source' => '',
    'state' => '0',
    'config' => array('FeedsCSVParser'=>array('delimiter' => ",", 'no_headers' => 0), 'FeedsFileFieldFetcher' => array('fid'=>'', 'source' =>''), 'FeedsFlatstoreProcessor' => array()),
    'fetcher_result' => '0',
    'imported' => '0',
  );
  drupal_write_record('feeds_source', $record);
  $record = array(
    'id' => 'dkan_file',
    'feed_nid' => '6',
    'source' => 'public://data.csv',
    'state' => '0',
    'config' => array('FeedsCSVParser'=>array('delimiter' => ",", 'no_headers' => 0), 'FeedsFileFieldFetcher' => array('fid'=> '30', 'source' => 'public://public://district_centerpoints.csv'), 'FeedsFlatstoreProcessor' => array()),
    'fetcher_result' => '0',
    'imported' => '0',
  );
  drupal_write_record('feeds_source', $record);
  $record = array(
    'id' => 'dkan_link',
    'feed_nid' => '6',
    'source' => '',
    'state' => '0',
    'config' => array('FeedsCSVParser'=>array('delimiter' => ",", 'no_headers' => 0), 'FeedsFileFieldFetcher' => array('fid'=>'', 'source' =>''), 'FeedsFlatstoreProcessor' => array()),
    'fetcher_result' => '0',
    'imported' => '0',
  );
  drupal_write_record('feeds_source', $record);

}
