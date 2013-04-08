<?php
/**
 * @file
 * Admin functions for the Delta blocks module.
 */

function delta_blocks_admin_settings($form, &$form_state) {
  $form['delta_blocks'] = array(
    '#type' => 'fieldset',
    '#title' => t('Delta blocks configuration'),
  );
  
  $form['delta_blocks']['delta_blocks_toggle'] = array(
    '#type' => 'checkboxes',
    '#title' => t('Toggle Delta blocks'),
    '#options' => delta_blocks_options(),
    '#default_value' => variable_get('delta_blocks_toggle', array()),
    '#description' => t('The selected items will be available as blocks.'),
  );
  
  return system_settings_form($form);
}