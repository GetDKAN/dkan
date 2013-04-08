<?php
/**
 * @file
 * Group membersihp type editing UI.
 */

/**
 * UI controller.
 */
class OgMembershipTypeUIController extends EntityDefaultUIController {

  /**
   * Overrides hook_menu() defaults.
   */
  public function hook_menu() {
    $items = parent::hook_menu();
    $items[$this->path]['description'] = 'Manage group memberships type, including fields.';
    return $items;
  }
}

/**
 * Generates the message type editing form.
 */
function og_membership_type_form($form, &$form_state, $og_membership_type, $op = 'edit') {
  if ($op == 'clone') {
    $og_membership_type->description .= ' (cloned)';
  }

  $form['description'] = array(
    '#title' => t('Description'),
    '#type' => 'textfield',
    '#default_value' => $og_membership_type->description,
    '#description' => t('The human-readable description of this group membership type.'),
    '#required' => TRUE,
  );
  // Machine-readable type name.
  $form['name'] = array(
    '#type' => 'machine_name',
    '#default_value' => isset($og_membership_type->name) ? $og_membership_type->name : '',
    '#disabled' => entity_has_status('og_membership_type', $og_membership_type, ENTITY_IN_CODE),
    '#machine_name' => array(
      'exists' => 'og_membership_type_load',
      'source' => array('description'),
    ),
    '#description' => t('A unique machine-readable name for this group membership type. It must only contain lowercase letters, numbers, and underscores.'),
  );

  field_attach_form('og_membership_type', $og_membership_type, $form, $form_state);

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save og membership type'),
    '#weight' => 40,
  );

  if (!entity_has_status('og_membership_type', $og_membership_type, ENTITY_IN_CODE) && $op != 'add') {
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete og_membership type'),
      '#weight' => 45,
      '#limit_validation_errors' => array(),
      '#submit' => array('og_membership_type_form_submit_delete')
    );
  }
  return $form;
}

/**
 * Validation callback.
 */
function og_membership_type_form_validate($form, &$form_state) {
  entity_form_field_validate('og_membership_type', $form, $form_state);
}

/**
 * Form API submit callback for the type form.
 */
function og_membership_type_form_submit(&$form, &$form_state) {
  $og_membership_type = entity_ui_form_submit_build_entity($form, $form_state);
  // Save and go back.
  $og_membership_type->save();
  $form_state['redirect'] = 'admin/config/group/group-membership';
}

/**
 * Form API submit callback for the delete button.
 */
function og_membership_type_form_submit_delete(&$form, &$form_state) {
  $form_state['redirect'] = 'admin/config/group/group-membership/manage/' . $form_state['og_membership_type']->name . '/delete';
}
