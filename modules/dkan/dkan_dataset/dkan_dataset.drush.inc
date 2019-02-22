<?php

/**
 * @file
 * Dkan_dataset.drush.inc.
 */

/**
 * Implements hook_drush_command().
 */
function dkan_dataset_drush_command() {
  $items['dkan-dataset-prune-revisions'] = array(
    'aliases' => array('ddpr'),
    'description' => 'Prune duplicate revisions from resource changes.',
    'options' => array(
      'revision-max' => 'Max revisions to remove in one run of the command.',
      'node-max' => 'Max number of nodes to prune.',
    ),
    'drupal dependencies' => array('dkan_dataset'),
  );

  return $items;
}

/**
 * Callback for revision pruning.
 */
function drush_dkan_dataset_prune_revisions() {
  // Get NIDs for nodes with more than 10 revisions.
  $sql = 'SELECT count(nr.vid) as c, nr.nid, n.title, n.type
    FROM {node_revision} nr
    LEFT JOIN {node} n ON n.nid=nr.nid
    GROUP BY nid HAVING c > 10
    ORDER BY c DESC';
  if ($limit = drush_get_option('node-max')) {
    $sql .= " LIMIT $limit";
  }
  $result = db_query($sql)->fetchAll();
  $total = 0;
  $revision_max = drush_get_option('revision-max');
  foreach ($result as $record) {
    $node_count = 0;
    // Get revision id for revisions for each node that have more than one
    // duplicate timestamp.
    $t_query = db_query(
      'SELECT count(*) as c, timestamp 
      FROM {node_revision} 
      WHERE nid = :nid 
      GROUP BY timestamp HAVING c > 2',
      array(':nid' => $record->nid)
    );
    $t_result = $t_query->fetchAll();
    foreach ($t_result as $t_record) {
      $vtime = date("r", $t_record->timestamp);
      drush_print("Processing $record->type \"$record->title\" (NID: $record->nid)");
      // Get all vids for duplicate timestamps.
      $r_query = db_query(
        'SELECT vid FROM {node_revision} WHERE nid = :nid && timestamp = :timestamp', 
        array(':timestamp' => $t_record->timestamp, 'nid' => $record->nid)
      );
      $r_result = $r_query->fetchAll();
      $vids = [];
      foreach ($r_result as $r_record) {
        $vids[] = $r_record->vid;
      }
      array_pop($vids);
      $vid_count = count($vids);
      drush_print("Removing $vid_count revisions for timestamp: $t_record->timestamp ($vtime)");
      foreach ($vids as $vid) {
        node_revision_delete($vid);
        $node_count++;
        $total++;
        if ($revision_max && $total >= $revision_max) {
          break;
        }
      }
      if ($revision_max && $total >= $revision_max) {
        break;
      }
    }
    $messages[] = "Removed $node_count revisions for $record->type $record->title ($record->nid)";
    if ($revision_max && $total >= $revision_max) {
      break;
    }
  }
  drush_print('----------------------');
  foreach($messages as $message) {
    drush_print($message);
  }
  drush_print("Removed $total total revisions.");
}
