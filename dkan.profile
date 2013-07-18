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
  $data = array(
    'pages' => "node/*\ncomment/*\nsystem/ajax",
    'eid' => 5,
  );
  drupal_write_record('bueditor_editors', $data, array('eid'));
}
