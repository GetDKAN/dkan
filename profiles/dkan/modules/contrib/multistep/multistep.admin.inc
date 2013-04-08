<?php

/**
 * @file
 * Administration settings for multistep.
 */

/**
 * Form used for administering multistep workflow settings.
 */
function multistep_admin_settings_workflow_form($form, &$form_state) {
  $form = array();
  $form['multistep_status_revisions'] = array(
    '#type' => 'radios',
    '#title' => t('Revisions'),
    '#description' => t('Sets how multistep will interact with node revisioning, <em>if revisioning is enabled for the content type</em>.
      <ul><li><strong>Any step</strong> will follow the standard revision settings
      (i.e. new revisions will be created after each successful submission, regardless of the step).</li>
      <li><strong>Any step, after node completion</strong> will create new revisions after each successful submission, regardless of the step, only after all steps have been submitted successfully at least once
      (i.e. after the node is considered <em>complete</em>).</li>
      <li><strong>Last step</strong> will create new revisions whenever you submit the last step of the form, regardless of whether the node is considered <em>complete</em> or not.
      (i.e. it will create a new revision after the last step even if some steps were skipped).</li>
      <li><strong>Last step, after node completion</strong> will create a new revision only when subitting the last step of the form, and only if all the steps have been successfully submitted at least once
      (i.e. after the node is considered <em>complete</em>).</li></ul>'
    ),
    '#default_value' => variable_get('multistep_status_revisions', 'default'),
    '#options' => array(
      'default' => t('Any step (Default)'),
      'complete' => t('Any step, after node completion'),
      'last' => t('Last step'),
      'last-complete' => t('Last step, after node completion'),
    ),
    '#weight' => -10,
  );
  $form['multistep_status_publishing'] = array(
    '#type' => 'radios',
    '#title' => t('Publishing'),
    '#description' => t('Sets how multistep will interact with the node published status, <em>if publishing is enabled for the content type</em>.
      <ul><li><strong>At node creation</strong> will follow the standard workflow settings
      (i.e. the node will be published when created).</li>
      <li><strong>At node completion</strong> will hold off publishing the node until all steps have been successfully submitted
      (i.e. when the node is considered <em>complete</em>).</li></ul>'
    ),
    '#default_value' => variable_get('multistep_status_publishing', 'default'),
    '#options' => array(
      'default' => t('At node creation (Default)'),
      'complete' => t('At node completion'),
    ),
    '#weight' => -9,
  );
  $form['multistep_workflow_mode'] = array(
    '#type' => 'radios',
    '#title' => t('Workflow mode'),
    '#description' => t('Disables navigation block links for steps that have not been submitted') . '<br /><strong>' . t('Note: This does not remove access from those steps, it merely displays the links as static text instead.') . '</strong>',
    '#options' => array(
      'free' => t('Steps can be submitted in any order (Default)'),
      'direct' => t('Steps can be submitted only in one direction (Wizard)'),
    ),
    '#default_value' => variable_get('multistep_workflow_mode', 'free'),
    '#weight' => -8,
  );
  $form['multistep_show_step_numbers'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show step numbers'),
    '#description' => t('Show step numbers in navigation block.'),
    '#default_value' => variable_get('multistep_show_step_numbers', FALSE),
    '#weight' => -6,
  );
  return system_settings_form($form);
}  
  
/**
 * Form used for changing multistep form labels.
 */
function multistep_admin_settings_design_form($form, &$form_state) {
  drupal_set_message(t('Saving these strings will disable their translation via Drupal\'s language system. Use the reset button to return them to the original state.'), 'warning');
  $form = array();
  $form['multistep_button_prev'] = array(
    '#type' => 'textfield',
    '#title' => t('Previous button'),
    '#description' => t('This button will bring you back one step. To omit this button, remove any text and leave this field blank.'),
    '#default_value' => variable_get('multistep_button_prev', t('< Previous')),
    '#weight' => -10,
  );
  $form['multistep_button_next'] = array(
    '#type' => 'textfield',
    '#title' => t('Next button'),
    '#description' => t('This button will move you forward one step.'),
    '#default_value' => variable_get('multistep_button_next', t('Next >')),
    '#required' => TRUE,
    '#weight' => -9,
  );
  $form['multistep_button_save'] = array(
    '#type' => 'textfield',
    '#title' => t('Save button'),
    '#description' => t('This button will save the form while remaining in the current step. To omit this button, remove any text and leave this field blank.'),
    '#default_value' => variable_get('multistep_button_save', t('Save')),
    '#weight' => -8,
  );
  $form['multistep_button_done'] = array(
    '#type' => 'textfield',
    '#title' => t('Done button'),
    '#description' => t('This button will save the form and redirect you to the node view.'),
    '#default_value' => variable_get('multistep_button_done', t('Done')),
    '#required' => TRUE,
    '#weight' => -7,
  );
  return system_settings_form($form);
}

/**
 * Form used for changing multistep form labels.
 */
function multistep_admin_settings_reset_form($form, &$form_state) {
  $form = array();
  $form['#description'] = t('This form will reset all the step data for the selected content type. Use this if you just enabled multistep for a content type that already contained nodes.') . '<br /><strong>' . t('Warning! Resetting the tables for a content type that already had multistep enabled could result in the loss of the step data!') . '</strong>';
  $options = array('' => '- Select -');
  foreach (node_type_get_types() as $type) {
    if (is_multistep($type)) {
      $options[$type->type] = $type->name;
    }
  }
  $form['multistep_type'] = array(
    '#type' => 'select',
    '#title' => t('Content type'),
    '#options' => $options,
    '#required' => TRUE,
    '#weight' => -1,
  );
  // Show reset button if multistep is enabled for this content type.
  $form['multistep_reset'] = array(
    '#type' => 'submit',
    '#value' => t('Reset data'),
    '#weight' => 1,
    '#access' => user_access('administer multistep'),
  );
  return $form;
}

/**
 * Resets the multistep table data for the selected content type.
 */
function multistep_admin_settings_reset_form_submit($form, &$form_state) {
  _multistep_reset_data($form_state['values']['multistep_type']);
  drupal_set_message(t('Data was reset successfully.'));
}
