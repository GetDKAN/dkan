<?php

function og_user_roles_action_info() {
  return array('og_user_roles_action' => array(
    'type' => 'og_membership',
    'label' => t('Modify OG user roles'),
    'configurable' => TRUE,
  ));
}

function og_user_roles_action_form($context) {
  $gid = '';
  $group_type = '';

  if (module_exists('og_context') && $og_context = og_context()) {
    // Get the group type and group ID from OG context.
    $gid = $og_context['gid'];
    $group_type = $og_context['group_type'];
  }
  elseif (empty($gid) || empty($group_type)) {
    // Get the group type and group ID from the Views arguments if they exist.
    list($group_type, $gid) = $context['view']->args;
    if (empty($group_type) || empty($gid)) {
      return;
    }
  }

  if (!entity_get_info($group_type)) {
    return;
  }

  $group = entity_load_single($group_type, $gid);
  if (!$group || !og_is_group($group_type, $group)) {
    return;
  }
  list(,, $bundle) = entity_extract_ids($group_type, $group);
  $og_roles = og_roles($group_type, $bundle, $gid, FALSE, FALSE);

  $form['add_roles'] = array(
    '#type' => 'select',
    '#multiple' => TRUE,
    '#title' => t('Add roles'),
    '#description' => t('Choose one or more roles you would like to assign to the selected users.'),
    '#options' => $og_roles,
    '#size' => 5
  );
  $form['remove_roles'] = array(
    '#type' => 'select',
    '#multiple' => TRUE,
    '#title' => t('Remove roles'),
    '#description' => t('Choose one or more roles you would like to remove from the selected users.'),
    '#options' => $og_roles,
    '#size' => 5
  );
  return $form;
}

function og_user_roles_action_validate($form, $form_state) {
  if (!$form_state['values']['add_roles'] && !$form_state['values']['remove_roles']) {
    form_set_error('add_roles', t('You have not chosen any role to add or remove. Please select something to do.'));
  }
}

function og_user_roles_action_submit($form, $form_state) {
  return array(
    'add_roles' => array_filter($form_state['values']['add_roles']),
    'remove_roles' => array_filter($form_state['values']['remove_roles']),
  );
}

function og_user_roles_action($og_membership, $context) {
  if ($og_membership->entity_type != 'user') {
    return;
  }

  $group_type = $og_membership->group_type;
  $gid = $og_membership->gid;

  foreach ($context['add_roles'] as $rid) {
    og_role_grant($group_type, $gid, $og_membership->etid, $rid);
  }

  foreach ($context['remove_roles'] as $rid) {
    og_role_revoke($group_type, $gid, $og_membership->etid, $rid);
  }
}
