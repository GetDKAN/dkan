<?php

namespace Drupal\data_dictionary_widget\Indexes;

use Drupal\Core\Form\FormStateInterface;

/**
 * Various operations for the Data Dictionary Widget callbacks.
 */
class IndexCallbacks {
  /**
   * Submit callback for the Index Field Add button.
   */
  public static function indexFieldAddSubformCallback(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'];
    $current_dictionary_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"];
    //$form_state->set('add_index_field', '');
    //$form_state->set('add_new_index_field', '');
    //$form_state->set('new_index_fields', '');
    // $fields_being_added = $form_state->set('fields_being_added', '');
    $current_index = $form["field_json_metadata"]["widget"][0]['indexes']["data"]["#rows"];
    $current_index_fields = $form["field_json_metadata"]["widget"][0]['indexes']["fields"]["data"]["#rows"] ?? NULL;
    //$existing_index_fields = $current_index[0]["fields"];

    if ($current_index_fields) {
      $form_state->set('current_index_fields', $current_index_fields);
    }

    if ($op === 'cancel_index_field') {
      $form_state->set('cancel_index_field', TRUE);
    }

    if ($op === 'add_new_index_field') {
      $form_state->set('add_index_field', '');
      $add_index_fields = IndexAddCreation::addIndexFields();
      $form_state->set('add_new_index_field', $add_index_fields);
      $form_state->set('index_added', FALSE);
      $form_state->set('adding_new_index_fields', TRUE);
    }

    if ($op === 'add_index_field') {
      $form_state->set('add_new_index_field', '');
      $form_state->set('new_index_fields', $form_state->getUserInput());
      $form_state->set('add', TRUE);
      $form_state->set('cancel_index_field', FALSE);
      $form_state->set('adding_new_index_fields', FALSE);
    }

    $form_state->set('current_dictionary_fields', $current_dictionary_fields);
    $form_state->set('current_index', $current_index);
    $form_state->set('current_index_fields', $current_index_fields);
    $form_state->setRebuild();
  }

  /**
   * Submit callback for the Index Add button.
   */
  public static function indexAddCallback(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'];
    $current_dictionary_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"];
    $form_state->set('add_new_index_field', '');
    $form_state->set('new_index_fields', '');
    $form_state->set('add_new_index', '');
    //$form_state->set('new_index', '');
    //$form_state->set('current_index_fields', '');
    // $fields_being_added = $form_state->set('fields_being_added', '');
    $form_state->set('adding_new_index_fields', FALSE);
    $current_index = $form["field_json_metadata"]["widget"][0]["indexes"]["data"]["#rows"];
    $current_index_fields = $form["field_json_metadata"]["widget"][0]['indexes']["fields"]["data"]["#rows"] ?? NULL;

    if ($current_index) {
      $form_state->set('current_index', $current_index);
      //$form_state->set('current_index_fields', '');
    }

    if ($op === 'cancel_index') {
      $form_state->set('cancel_index', TRUE);
    }

    if ($op === 'add_new_index') {
      $add_new_index = IndexAddCreation::addIndex();
      $form_state->set('new_index', '');
      //$form_state->set('current_index_fields', $current_index_fields);
      $form_state->set('add_new_index', $add_new_index);
    }

    if ($op === 'add_index') {
      $form_state->set('add_new_index', '');
      $form_state->set('new_index', $form_state->getUserInput());
      $form_state->set('add', TRUE);
      $form_state->set('index_added', TRUE);
      $form_state->set('cancel_index', FALSE);
    }

    $form_state->set('current_dictionary_fields', $current_dictionary_fields);
    $form_state->set('current_index', $current_index);
    $form_state->set('current_index_fields', $current_index_fields);
    $form_state->setRebuild();
  }

  /**
   * Submit callback for the Index Field Edit button.
   */
  public static function indexFieldEditSubformCallback(array &$form, FormStateInterface $form_state) {
    // What button was clicked?
    $trigger = $form_state->getTriggeringElement();
    // Set the 'operation' of the actioned button to a var
    $op = $trigger['#op'];
    // Get the portion of the operation string that's relevent
    // so we can action on it
    $op_index = explode("_", $trigger['#op']);
    // Get our current (soft saved) data on the form for each main element
    $current_index_fields = $form["field_json_metadata"]["widget"][0]["indexes"]["fields"]["data"]["#rows"];
    $current_dictionary_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"];
    // Get the data that we're currently modifying (temporary)
    // This differs from the current fields in that
    // this is the data we are immediately editing
    $currently_modifying = $form_state->get('dictionary_fields_being_modified') != NULL ? $form_state->get('dictionary_fields_being_modified') : [];
    $currently_modifying_index_fields = $form_state->get('index_fields_being_modified') != NULL ? $form_state->get('index_fields_being_modified') : [];
    

    if (str_contains($op, 'abort')) {
      unset($currently_modifying_index_fields[$op_index[4]]);
    }

    if (str_contains($op, 'delete')) {
      unset($currently_modifying_index_fields[$op_index[4]]);
      unset($current_index_fields[$op_index[4]]);
    }

    if (str_contains($op, 'update')) {
      $update_values = $form_state->getUserInput();
      unset($currently_modifying_index_fields[$op_index[4]]);
      unset($current_index_fields[$op_index[4]]);
      $current_index_fields[$op_index[4]] = IndexValues::updateIndexFieldValues($op_index[4], $update_values, $current_index_fields );
      ksort($current_index_fields );
    }

    if (str_contains($op, 'edit')) {
      $currently_modifying_index_fields[$op_index[4]] = $current_index_fields[$op_index[4]];
    }

    $form_state->set('dictionary_fields_being_modified', $currently_modifying);
    $form_state->set('index_fields_being_modified', $currently_modifying_index_fields);
    $form_state->set('current_index_fields', $current_index_fields );
    $form_state->set('current_dictionary_fields', $current_dictionary_fields );
    $form_state->setRebuild();
  }

  /**
   * Submit callback for the Index Edit button.
   */
  public static function indexEditSubformCallback(array &$form, FormStateInterface $form_state) {
    // What button was clicked?
    $trigger = $form_state->getTriggeringElement();
    // Set the 'operation' of the actioned button to a var
    $op = $trigger['#op'];
    // Get the portion of the operation string that's relevent
    // so we can action on it
    $op_index = explode("_", $trigger['#op']);
    // Get our current (soft saved) data on the form for each main element
    $current_index = $form["field_json_metadata"]["widget"][0]["indexes"]["data"]["#rows"];
    $current_index_fields = $form["field_json_metadata"]["widget"][0]["indexes"]["fields"]["data"]["#rows"];
    $current_dictionary_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"];
    // Get the data that we're currently modifying (temporary)
    // This differs from the current fields in that
    // this is the data we are immediately editing
    $currently_modifying = $form_state->get('dictionary_fields_being_modified') != NULL ? $form_state->get('dictionary_fields_being_modified') : [];
    $currently_modifying_index = $form_state->get('index_being_modified') != NULL ? $form_state->get('index_being_modified') : [];
    $currently_modifying_index_fields = $form_state->get('index_fields_being_modified') != NULL ? $form_state->get('index_fields_being_modified') : [];
    

    if (str_contains($op, 'abort')) {
      unset($currently_modifying_index_fields[$op_index[4]]);
    }

    if (str_contains($op, 'delete')) {
      unset($currently_modifying_index_fields[$op_index[4]]);
      unset($current_index_fields[$op_index[4]]);
    }

    if (str_contains($op, 'update')) {
      $update_values = $form_state->getUserInput();
      unset($currently_modifying_index_fields[$op_index[4]]);
      unset($current_index_fields[$op_index[4]]);
      $current_index_fields[$op_index[4]] = IndexValues::updateIndexFieldValues($op_index[4], $update_values, $current_index_fields );
      ksort($current_index_fields );
    }

    if (str_contains($op, 'edit')) {
      $currently_modifying_index_fields[$op_index[4]] = $current_index_fields[$op_index[4]];
    }

    $form_state->set('dictionary_fields_being_modified', $currently_modifying);
    $form_state->set('index_fields_being_modified', $currently_modifying_index_fields);
    $form_state->set('current_index_fields', $current_index_fields );
    $form_state->set('current_dictionary_fields', $current_dictionary_fields );
    $form_state->setRebuild();
  }

  /**
   * Ajax callback.
   */
  public static function subIndexformAjax(array &$form, FormStateInterface $form_state) {
    return $form["field_json_metadata"]["widget"][0]["indexes"]["fields"];
  }

  /**
   * Ajax callback.
   */
  public static function indexformAjax(array &$form, FormStateInterface $form_state) {
    return $form["field_json_metadata"]["widget"][0]["indexes"];
  }
}