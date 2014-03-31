<?php

/**
 * @file
 * Triggers for dkan_dataset.
 */

/**
 * Implements hook_node_update().
 */
function dkan_dataset_node_update($node) {
  global $user;
  switch ($node->type) {
    case 'resource':
      $datasets = $node->field_dataset_ref[LANGUAGE_NONE];
      if (count($datasets)) {
        $queue = DrupalQueue::get('dataset_changelog');
        foreach ($datasets as $dataset) {
          $item = array(
            'dataset' => $dataset['target_id'],
            'message' => 'Update to resource \'' . $node->title . '\'',
            'user' => $user->uid,
          );
          $queue->createItem($item);
        }
      }
      break;

    default:
      break;

  }
}

/**
 * Implements hook_queue_info().
 */
function dkan_dataset_cron_queue_info() {
  $queues = array();
  $queues['dataset_changelog'] = array(
    'worker callback' => 'dkan_dataset_dataset_changelog_run',
    'time' => 20,
  );
  return $queues;
}

/**
 * Callback for dkan_dataset_dataset_changelog cron worker.
 * @param  array $item
 *   An associative array containing the dataset nid and a log message.
 */
function dkan_dataset_dataset_changelog_run($item) {
  global $user;
  $original_user = $user;
  drupal_save_session(FALSE);
  // Increment Revision
  if (isset($item['user']) && isset($item['dataset']) && isset($item['message'])) {
    $user = user_load($item['user']);
    ctools_include('Dataset', 'dkan_dataset');
    $dataset = Dataset::instance($item['dataset']);
    $dataset->incrementRevision($item['message']);
    $dataset->save();
  }
  // Restore Anonymous user.
  $user = $original_user;
  drupal_save_session(TRUE);
}
