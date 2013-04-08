<?php

/**
 * @file
 * Action to set the state of a user in a group.
 */

function og_set_state_action_info() {
  return array('og_set_state_action' => array(
    'type' => 'og_membership',
    'label' => t('Modify membership status'),
    'configurable' => TRUE,
  ));
}

function og_set_state_action_form($context) {
  $form['state'] = array(
    '#type' => 'select',
    '#title' => t('State'),
    '#description' => t('Choose the state to set for the selected users in the group.'),
    '#options' => og_group_content_states(),
    '#default_value' => OG_STATE_ACTIVE,
    '#required' => TRUE,
  );
  return $form;
}

function og_set_state_action_submit($form, $form_state) {
  return array('state' => $form_state['values']['state']);
}

function og_set_state_action($og_membership, $context) {
  $state = $context['state'];

  if ($og_membership->state == $state) {
    return;
  }

  // Don't process the group manager, if exists.
  $group = entity_load_single($og_membership->group_type, $og_membership->gid);
  if (!empty($group->uid) && $group->uid == $og_membership->etid && $og_membership->entity_type == 'user') {
    return;
  }

  $og_membership->state = $state;
  $og_membership->save();
}
