<?php

/**
 * @file
 */

use Dkan\DataDictionary\Page\ResourceDataDictionaryValidationForm;

/**
 * Data_dictionary forms menu callback.
 */
function dkan_data_dictionary_resource_report_form($form, &$form_state, $node) {
  $form_state['node'] = $node;

  try {
    $validateForm = new ResourceDataDictionaryValidationForm($node);
    $form = $validateForm->buildForm($form, $form_state);
  }
  catch (\Exception $exception) {
    watchdog(
      'dkan_data_dictionary',
      'Failed to build form "@form_id".',
      array('@form_id' => $form['#id']),
      WATCHDOG_ERROR
    );
  }

  return $form;
}

/**
 * Submit handler.
 */
function dkan_data_dictionary_resource_report_form_submit($form, &$form_state) {
  $validateForm = new ResourceDataDictionaryValidationForm($form_state['node']);
  return $validateForm->submitForm($form, $form_state);
}
