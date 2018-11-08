<?php

/**
 * @file
 */

use Dkan\DataDictionary\Page\ResourceValidateForm;

/**
 * Data_dictionary forms menu callback.
 */
function dkan_data_dictionary_resource_report_form($form, &$form_state, $node) {
  $form_state['node'] = $node;

  try {
    $validateForm = new ResourceValidateForm($node);
    $form = $validateForm->buildForm($form, $form_state);
  } catch (\Exception $exception){
    // TODO add watchdog entry.
  }

  return $form;
}

/**
 * Submit handler.
 */
function dkan_data_dictionary_resource_report_form_submit($form, &$form_state) {
  $validateForm = new ResourceValidateForm($form_state['node']);
  return $validateForm->submitForm($form, $form_state);
}
