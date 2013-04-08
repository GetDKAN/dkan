<?php


/**
 * @file
 * Plugin to provide a relationship handler for OG membership from group and
 * user.
 */

/**
 * Plugin definition.
 */
$plugin = array(
  'title' => t('OG membership from a group node and user'),
  'keyword' => 'og_membership',
  'description' => t('Get the OG membership context from an a node that is a group and user.'),
  'required context' => array(
    new ctools_context_required(t('Node'), 'node'),
    new ctools_context_required(t('User'), 'user'),
  ),
  'context' => 'og_membership_from_group_and_user_context',
);

/**
 * Return a new context based on an existing context.
 */
function og_membership_from_group_and_user_context($context, $conf) {
  list($node_context, $user_context) = $context;

  // If unset it wants a generic, unfilled context, which is just NULL.
  if (empty($node_context->data)) {
    return ctools_context_create_empty('entity:og_membership', NULL);
  }

  if (empty($user_context) || empty($user_context->data)) {
    return;
  }

  $node = $node_context->data;
  $account = clone $user_context->data;

  if ($og_membership = og_get_membership('node', $node->nid, 'user', $account->uid)) {
    // Send it to ctools.
    return ctools_context_create('entity:og_membership', $og_membership);
  }
}
