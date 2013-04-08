<?php

function views_bulk_operations_user_roles_action_info() {
  return array('views_bulk_operations_user_roles_action' => array(
    'type' => 'user',
    'label' => t('Modify user roles'),
    'configurable' => TRUE,
    'triggers' => array('any'),
  ));
}

function views_bulk_operations_user_roles_action_form($context) {
  $roles = user_roles(TRUE);
  unset($roles[DRUPAL_AUTHENTICATED_RID]);  // Can't edit authenticated role.

  $form['add_roles'] = array(
    '#type' => 'select',
    '#multiple' => TRUE,
    '#title' => t('Add roles'),
    '#description' => t('Choose one or more roles you would like to assign to the selected users.'),
    '#options' => $roles,
    '#size' => 5
  );
  $form['remove_roles'] = array(
    '#type' => 'select',
    '#multiple' => TRUE,
    '#title' => t('Remove roles'),
    '#description' => t('Choose one or more roles you would like to remove from the selected users.'),
    '#options' => $roles,
    '#size' => 5
  );
  return $form;
}

function views_bulk_operations_user_roles_action_validate($form, $form_state) {
  if (!$form_state['values']['add_roles'] && !$form_state['values']['remove_roles']) {
    form_set_error('add_roles', t('You have not chosen any role to add or remove. Please select something to do.'));
  }
}

function views_bulk_operations_user_roles_action_submit($form, $form_state) {
  return array(
    'add_roles' => array_filter($form_state['values']['add_roles']),
    'remove_roles' => array_filter($form_state['values']['remove_roles']),
  );
}

function views_bulk_operations_user_roles_action(&$user, $context) {
  $roles = $user->roles;
  $selected = (is_array($context['add_roles']) ? $context['add_roles'] : array()) +
              (is_array($context['remove_roles']) ? $context['remove_roles'] : array());
  $result = db_query("SELECT rid, name FROM {role} WHERE rid IN (:selected)", array(':selected' => array_keys($selected)));
  foreach ($result as $role) {
    if (isset($context['add_roles'][$role->rid])) {
      $add_roles[$role->rid] = $role->name;
    }
    if (isset($context['remove_roles'][$role->rid])) {
      $remove_roles[$role->rid] = $role->name;
    }
  }
  if (!empty($add_roles)) {
    $roles += $add_roles;
  }
  if (!empty($remove_roles)) {
    $roles = array_diff($roles, $remove_roles);
  }
  user_save($user, array('roles' => $roles));
}
