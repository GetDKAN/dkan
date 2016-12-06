<?php

namespace Drupal\DKANExtension\Context;

/**
 * 
 */
trait ModeratorTrait {

  public function moderate($wrapper, $fields) {

    if (isset($fields['moderation'])) {
      // Make the node author as the revision author.
      // This is needed for workbench views filtering.
      $wrapper->value()->log = $wrapper->value()->uid;
      $wrapper->value()->revision_uid = $wrapper->value()->uid;

      db_update('node_revision')
        ->fields(array(
          'uid' => $wrapper->value()->uid,
        ))
        ->condition('nid', $wrapper->getIdentifier(), '=')
        ->execute();

      // Manage moderation state.
      workbench_moderation_moderate($wrapper->value(), $fields["moderation"]);
      // Hack the moderation date
      if (isset($fields['moderation_date'])) {
        $datetime = new \DateTime($fields['moderation_date']);
        db_update('workbench_moderation_node_history')
          ->fields(array(
            'stamp' => $datetime->getTimestamp(),
          ))
          ->condition('nid', $wrapper->getIdentifier(), '=')
          ->condition('vid', $wrapper->value()->vid, '=')
          ->execute();
      }
    }

  }

  public function preSaveModerate($wrapper, &$fields) {
    if (isset($fields['moderation'])) {
      // status = 0 causing values to not be accessible by default.
      if ($fields['moderation'] == 'published') {
        $fields['published'] = 1;
      }
    }
  }

  public function isNodeInModerationState($node, $state) {
    $query = db_select('workbench_moderation_node_history', 'h')
    ->fields('h')
    ->condition('nid', $node->nid, '=')
    ->condition('is_current', "1", '=')
    ->range(0,1);

    $result = $query->execute();

    $actual_state = "";
    while($record = $result->fetchAssoc()) {
      $actual_state = $record["state"];
    }

    if ($actual_state <> $state) {
      throw new \Exception(sprintf("The node '%s' is not in '%s' moderation state.", $node->title, $state));
    }
  }

}
