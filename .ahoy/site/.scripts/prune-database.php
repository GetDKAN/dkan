<?php
/**
 * The purpose of this script is to delete all but N datasets and their 
 * resources in order to prune the size of a DKAN site database for development 
 * purposes.
 */

db_query("DELETE FROM search_api_index where server = 'dkan_acquia_solr'");
db_query("DELETE FROM search_api_index where server = 'local_solr_server'");
db_query("DELETE FROM search_api_server where machine_name = 'dkan_acquia_solr';");
db_query("DELETE FROM search_api_server where machine_name = 'local_solr_server';");
db_query("DELETE FROM search_api_index where server IS NULL");

module_load_include('inc', 'search_api', 'search_api.drush');
drush_search_api_disable();

prune_nodes();
prune_terms();

/**
 * Prunes taxonomy terms saving a default of 5 from each vocabulary.
 */
function prune_terms($number = 5) {
  $terms_to_save = array();
  // Ensures we save terms that are being used.
  $topics = db_query("SELECT DISTINCT field_topic_tid as tid FROM {field_data_field_topic} LIMIT $number")->fetchAll();
  foreach ($topics as $topic) {
    $terms_to_save[] = $topic->tid;
  }
  $tags = db_query("SELECT DISTINCT field_tags_tid as tid FROM {field_data_field_tags} LIMIT $number")->fetchAll();
  foreach ($tags as $tag) {
    $terms_to_save[] = $tag->tid;
  }
  $all_terms = array();
  // Leave all formats.
  $format_vid = 1;
  $terms = db_query("SELECT DISTINCT tid FROM {taxonomy_term_data} WHERE vid != $format_vid")->fetchAll();
  foreach ($terms as $term) {
    $all_terms[] = $term->tid;
  }
  foreach ($all_terms as $tid) {
    if (!in_array($tid,$terms_to_save)) {
      taxonomy_term_delete($tid);
    }
  }
}

/**
 * Prunes nodes.
 */
function prune_nodes($number = 25) {
  $query = db_select('node', 'n');
  $query->range(0, $number);
  $records = $query
    ->fields('n', array('nid'))
    ->condition('type', 'dataset')
    ->condition('status', 1)
    ->orderBy('created', 'DESC')
    ->execute();

  $keep_nodes = [];
  foreach($records as $record) {
    $keep_nodes[] = $record->nid;
  }

  $query = db_select('node', 'n');
  $nodes = $query
    ->fields('n', array('nid'))
    ->condition('type', 'dataset')
    ->condition('n.nid', $keep_nodes, 'NOT IN')
    ->execute();

  $delete_nodes = [];

  foreach($nodes as $node) {
    $dataset = node_load($node->nid);
    $resources = $dataset->field_resources['und'];
    $resources = is_array($resources) ? $resources : array();
    try {
      node_delete($node->nid);
    }
    catch(Exception $e) {
      print "Skipping dataset $node->nid do to error\n";
    }
    foreach($resources as $resource) {
      try {
        node_delete($resource['target_id']);
      }
      catch(Exception $e) {
        print "Skipping resource $resource[target_id] do to error\n";
      }
    }
  }
}

