<?php
/**
 * @file
 *
 * Is an alter plugin for defaultcontent
 *
 * Handles the exporting and importing of node queue membership
 * requires http://drupal.org/files/issues/1023606-qid-to-name-6.patch
 * from http://drupal.org/node/1023606
 */

$plugin = array();
// TODO add weight

/**
 * Handles the adding of node queue membership to the export
 */
function nodequeue_export_alter(&$node, &$export) {
  if (isset($node->nid)) {
    $query = new EntityFieldQuery();
    $queue_data = db_select('nodequeue_nodes', 'q')
      ->condition('nid', $node->nid)
      ->fields('q', array('qid', 'sqid'))
      ->execute();

    foreach ($queue_data as $datum) {
      $export->default_nodequeue = array();
      $export->default_nodequeue[] = array(
        'queue' => $datum->qid,
      );
    }
  }
}

/**
 * After import handles the adding of of the nodes to nodequeues
 */
function nodequeue_post_import($node) {
  if (isset($node->default_nodequeue)) {
    foreach ($node->default_nodequeue as $datum) {
      if (isset($node->nid) && ($queue = nodequeue_load($datum['queue']))) {
        $subqueues = nodequeue_load_subqueues_by_queue(array($datum['queue']));
        nodequeue_subqueue_add($queue, array_pop($subqueues), $node->nid);
      }
    }
  }
}

function nodequeue_enabled() {
  return module_exists('nodequeue');
}
