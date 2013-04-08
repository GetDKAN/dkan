<?php

/**
 * @file
 * Action to set the state of a user in a group.
 */

function og_membership_delete_action_info() {
  return array('og_membership_delete_action' => array(
    'type' => 'og_membership',
    'label' => t('Remove from group'),
    'configurable' => FALSE,
  ));
}

function og_membership_delete_action($og_membership, $context) {
  $group_type = $og_membership->group_type;
  $gid = $og_membership->gid;

  // Don't process the group manager, if exists.
  $group = entity_load_single($group_type, $gid);
  if (!empty($group->uid) && $group->uid == $og_membership->etid && $og_membership->entity_type == 'user') {
    return;
  }

  $og_membership->delete();

}
