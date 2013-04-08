<?php
/**
 * @file
 * Theme functions for OG Extras.
 */

/**
 * Create variables for the group statistics block.
 */
function og_extras_preprocess_og_extras_group_info(&$variables) {
  $group = og_context();

  $variables['group_node_links'] = array();
  $variables['gid'] = NULL;
  $variables['group_type'] = NULL;
  $variables['group_node'] = NULL;
  $variables['created'] = NULL;
  $variables['subscribe_link'] = NULL;
  $variables['subscriber_count'] = NULL;
  $variables['subscriber_link'] = NULL;
  $variables['manager_uids'] = array();
  $variables['managers'] = array();

  if (empty($group)) {
    return NULL;
  }

  $variables['group_node_links'] = og_extras_node_links();
  $variables['gid'] = $group['gid'];
  $variables['group_type'] = $group['group_type'];
  $variables['group_node'] = node_load($group['gid']);

  $variables['subscribe_link'] = og_extras_subscribe('node', $variables['group_node']);

  $variables['created'] = format_date($variables['group_node']->created, 'custom', 'M d Y');

  $variables['subscriber_count'] = og_extras_subscriber_count($variables['gid']);
  $variables['subscriber_link'] = l(format_plural($variables['subscriber_count'], '@count member', '@count members'), 'node/' . $variables['gid'] . '/members');

  // @TODO Add a configuration form to select the 'manager' roles for the group.
  $roles = og_extras_get_roles($variables['gid']);
  $role_rids = array();
  foreach ($roles as $role) {
    $role_rids[] = $role->rid;
  }

  $variables['manager_uids'] = array($variables['group_node']->uid);
  foreach (og_extras_get_users_by_roles($variables['gid'], $role_rids) as $item) {
    $variables['manager_uids'][] = $item->uid;
  }
  $variables['manager_uids'] = array_unique($variables['manager_uids']);
  $first = TRUE;
  foreach ($variables['manager_uids'] as $i => $manager_id) {
    $variables['managers'][$i] = '';
    $account = user_load($manager_id);
    $variables['managers'][$i] .= theme('username', array('account' => $account));
    if ($first) {
      $variables['managers'][$i] .= theme('user_picture', array('account' => $account));
      $first = FALSE;
    }
  }
}

/**
 * Create variables for the group links block.
 */
function og_extras_preprocess_og_extras_group_links(&$variables) {
  $group = og_context();

  $variables['group_node_links'] = array();
  $variables['gid'] = NULL;
  $variables['group_type'] = NULL;
  $variables['group_node'] = NULL;

  if (empty($group)) {
    return NULL;
  }
  $variables['group_node_links'] = og_extras_node_links();
  $variables['gid'] = $group['gid'];
  $variables['group_type'] = $group['group_type'];
  $variables['group_node'] = node_load($group['gid']);
}