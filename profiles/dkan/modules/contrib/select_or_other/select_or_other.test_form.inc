<?php

/**
 * @file
 * The Select (or other) test form.
 */

/**
 * Implements hook_menu().
 */
function select_or_other_menu() {
  $items = array();
  $items['select-or-other-test-form'] = array(
    'title' => 'select_or_other test',
    'page callback' => 'drupal_get_form',
    'page arguments' => array('select_or_other_test_form'),
    'access arguments' => array('access administration pages'),
    'type' => MENU_CALLBACK,
  );
  return $items;
}

/**
 * Test function.
 * to view, visit http://example.com/?q=select-or-other-test-form
 * You must have the permission 'access administration pages'.
 */
function select_or_other_test_form($form, $form_state) {
  $v = &$form_state['values'];
  $form['my_field_1'] = array(
    '#type' => 'select_or_other',
    '#title' => t('My example Field'),
    '#default_value' => $v['my_field_1'] ? $v['my_field_1'] : array('Another value', 'extra value'),
    '#options' => array(
      'option1' => t('Option 1'),
      'option2' => t('Option 2'),
      'option3' => t('Option 3'),
    ),
    '#other' => t('Other (please type with your fingers)'),
    '#required' => TRUE,
    '#multiple' => FALSE,
    '#other_delimiter' => ', ', // if this is FALSE only the last value will be used
    '#other_unknown_defaults' => 'other', // possible values 'append', 'ignore', 'other'  (if other specified you can also override #other_delimiter).
    '#description' => t("The description of this element."),
  );
  $form['fieldset'] = array(
    '#type' => 'fieldset',
    '#title' => t('Fieldset'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['fieldset']['my_field_2'] = array(
    '#type' => 'select_or_other',
    '#select_type' => 'checkboxes',
    '#title' => t('My checkboxes example'),
    '#default_value' => $v['my_field_2'] ? $v['my_field_2'] : array('Another value'),
    '#options' => array(
      'option1' => t('Option 1'),
      'option2' => t('Option 2'),
      'option3' => t('Option 3'),
    ),
    '#other' => t('Other (please type with your fingers)'),
    '#required' => TRUE,
    '#multiple' => TRUE, // this should be ignored for checkboxes
    '#other_delimiter' => ', ', // if this is FALSE only the last value will be used
    '#other_unknown_defaults' => 'append', // possible values 'append', 'ignore', 'other'  (if other specified you can also override #other_delimiter).
    '#other_title' => t('Other'),
    '#other_description' => t('Description of other field.'),
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
  );
  return $form;
}

/**
 * Submit function for test form.
 */
function select_or_other_test_form_submit($form, &$form_state) {
  $form_state['storage'] = $form_state['values'];
}

