<?php

/**
 * @file
 * Hooks provided by the multistep module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Provide a way to change the status of submitting a step.
 * 
 * If there are multiple implementations of this hook, they all have to return
 * 'submitted' for that step to be considered fully submitted.
 *
 * @param $form_state
 *   The form's form state.
 * @param $status
 *   The status of the step during form submission.
 * @param $step
 *   The current step being submitted.
 *
 * @return
 *   The status of the step after submission. The possible options are
 *   'submitted' or 'unsubmitted'.
 */
function hook_multistep_update_status($form_state, $status, $step) {
  // Get the node type
  $type = $form_state['node']['type'];
  // Check whether we are on the last step of the form
  if ($type == 'profile' && $step == variable_get('multistep_steps_' . $type, 0)) {
    // Check whether the user entered their Last Name in the proper field
    if (empty($form_state['values']['field_last_name']['value'][0])) {
      // Warn the user
      drupal_set_message(t('You have to enter your last name to complete this form'), 'warning');
      // Mark the step as 'unsubmitted'
      return 'unsubmitted';
    }
  }
}

/**
 * @} End of "addtogroup hooks".
 */
