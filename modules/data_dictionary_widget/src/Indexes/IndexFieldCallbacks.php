<?php

namespace Drupal\data_dictionary_widget\Indexes;

use Drupal\Core\Form\FormStateInterface;

/**
 * Various operations for the Index callbacks.
 */
class IndexFieldCallbacks {
  /**
   * Submit callback for the Index Add button.
   */
  public static function indexAddSubformCallback(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'];
    $current_dictionary_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"] ?? [];
    $current_index = $form["field_json_metadata"]["widget"][0]['indexes']["data"]["#rows"] ?? [];
    $current_index_fields = $form["field_json_metadata"]["widget"][0]['indexes']["fields"]["data"]["#rows"] ?? [];

    if ($current_index_fields) {
      $form_state->set('current_index_fields', $current_index_fields);
    }

    if ($op === 'cancel_index_field') {
      $form_state->set('cancel_index_field', TRUE);
      $form_state->set('add_new_index_field', '');
    }

    if ($op === 'add_new_index_field') {
      $form_state->set('add_index_field', '');
      $add_index_fields = IndexFieldAddCreation::addIndexFields($current_index_fields);
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
    $form_state->set('adding_new_index_fields', FALSE);
    $current_index = $form["field_json_metadata"]["widget"][0]["indexes"]["data"]["#rows"];
    $current_index_fields = $form["field_json_metadata"]["widget"][0]['indexes']["fields"]["data"]["#rows"] ?? NULL;

    if ($current_index) {
      $form_state->set('current_index', $current_index);
    }

    if ($op === 'cancel_index') {
      $form_state->set('cancel_index', TRUE);
    }

    if ($op === 'add_new_index') {
      $add_new_index = IndexFieldAddCreation::addIndex();
      $form_state->set('new_index', '');
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
  public static function indexEditSubformCallback(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $current_index_fields = $form["field_json_metadata"]["widget"][0]["indexes"]["fields"]["data"]["#rows"];
    $current_dictionary_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"];
    $op = $trigger['#op'];
    $op_index = explode("_", $trigger['#op']);
    $currently_modifying_index_fields = $form_state->get('index_fields_being_modified') != NULL ? $form_state->get('index_fields_being_modified') : [];
    $currently_modifying = $form_state->get('dictionary_fields_being_modified') != NULL ? $form_state->get('dictionary_fields_being_modified') : [];

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
      $current_index_fields[$op_index[4]] = IndexFieldValues::updateIndexFieldValues($op_index[4], $update_values, $current_index_fields);
      ksort($current_index_fields);

    }

    if (str_contains($op, 'edit_index_key')) {
      $currently_modifying_index_fields[$op_index[4]] = $current_index_fields[$op_index[4]];
    }

    if (str_contains($op, 'edit_index_field')) {
      $currently_modifying_index_fields[$op_index[4]] = $current_index_fields[$op_index[4]];
    }

    $form_state->set('dictionary_fields_being_modified', $currently_modifying);
    $form_state->set('index_fields_being_modified', $currently_modifying_index_fields);
    $form_state->set('current_index_fields', $current_index_fields);
    $form_state->set('current_dictionary_fields', $current_dictionary_fields);
    $form_state->setRebuild();
  }

  /**
   * Submit callback for the Index Edit button.
   */
  public static function indexEditCallback(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $current_index_fields = $form["field_json_metadata"]["widget"][0]["indexes"]["fields"]["data"]["#rows"] ?? [];
    $current_index = $form["field_json_metadata"]["widget"][0]["indexes"]["data"]["#rows"] ?? [];
    $current_dictionary_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"] ?? [];
    $op = $trigger['#op'];
    $op_index = explode("_", $trigger['#op']);
    $currently_modifying_index_fields = $form_state->get('index_fields_being_modified') != NULL ? $form_state->get('index_fields_being_modified') : [];
    $currently_modifying_index = $form_state->get('index_being_modified') != NULL ? $form_state->get('index_being_modified') : [];
    $currently_modifying_dictionary_fields = $form_state->get('dictionary_fields_being_modified') != NULL ? $form_state->get('dictionary_fields_being_modified') : [];

    if (str_contains($op, 'abort_index_key')) {
      unset($currently_modifying_index[$op_index[3]]);
    }

    if (str_contains($op, 'abort_index_field_key')) {
      unset($currently_modifying_index_fields[$op_index[4]]);
    }

    if (str_contains($op, 'delete_index_key')) {
      unset($currently_modifying_index[$op_index[3]]);
      unset($current_index[$op_index[3]]);
    }

    if (str_contains($op, 'delete_index_field_key')) {
      unset($currently_modifying_index_fields[$op_index[4]]);
      unset($current_index_fields[$op_index[4]]);
    }

    if (str_contains($op, 'update')) {
      $update_values = $form_state->getUserInput();
      $current_index[$op_index[3]] = IndexFieldValues::updateIndexValues($op_index[3], $update_values, $current_index);
      unset($currently_modifying_index[$op_index[3]]);
      ksort($current_index);
    }

    if (str_contains($op, 'edit')) {
      $currently_modifying_index[$op_index[3]] = $current_index[$op_index[3]];
    }

    $form_state->set('dictionary_fields_being_modified', $currently_modifying_dictionary_fields);
    $form_state->set('index_fields_being_modified', $currently_modifying_index_fields);
    $form_state->set('index_being_modified', $currently_modifying_index);
    $form_state->set('current_index_fields', $current_index_fields);
    $form_state->set('current_index', $current_index);
    $form_state->set('current_dictionary_fields', $current_dictionary_fields);
    $form_state->setRebuild();
  }

  /**
   * Ajax callback to return index fields.
   */
  public static function subIndexEditFormAjax(array &$form, FormStateInterface $form_state) {
    return $form["field_json_metadata"]["widget"][0]["indexes"]["edit_index"]["index_key_0"]["group"]["fields"]["fields"];
  }

  /**
   * Ajax callback to return index fields.
   */
  public static function subIndexFormAjax(array &$form, FormStateInterface $form_state) {
    return $form["field_json_metadata"]["widget"][0]["indexes"]["fields"];
  }

  /**
   * Ajax callback to return indexes.
   */
  public static function indexFormAjax(array &$form, FormStateInterface $form_state) {
    $index_fields = $form["field_json_metadata"]["widget"][0]["indexes"]["fields"];

    // Validation errors skip submit callbacks, this will set the index fields
    // in the correct location.
    if ($index_fields["data"]) {
      $form["field_json_metadata"]["widget"][0]["indexes"]["field_collection"]["group"]["index"]["fields"] = $index_fields;
      $form["field_json_metadata"]["widget"][0]["indexes"]["fields"]['#access'] = FALSE;
    }

    return $form["field_json_metadata"]["widget"][0]["indexes"];
  }

  /**
   * Ajax callback to return index fields fieldset with Add Field button.
   */
  public static function subIndexFormFieldAjax(array &$form, FormStateInterface $form_state) {
    return $form["field_json_metadata"]["widget"][0]["indexes"]["field_collection"]["group"]["index"]["fields"];
  }

  /**
   * Ajax callback to return index fields fieldset with existing fields and Add
   * Field button.
   */
  public static function subIndexFormExistingFieldAjax(array &$form, FormStateInterface $form_state) {
    $form["field_json_metadata"]["widget"][0]["indexes"]["field_collection"]["group"]["index"]["fields"]["add_row_button"]['#access'] = TRUE;
    return $form["field_json_metadata"]["widget"][0]["indexes"]["field_collection"]["group"]["index"]["fields"]["add_row_button"];
  }

  /**
   * Widget validation callback.
   */
  public static function indexFieldVal($element, FormStateInterface &$form_state, array &$form) {
    $fields_to_validate = [
      'name' => 'Name',
      'length' => 'Length',
    ];

    foreach ($fields_to_validate as $field_key => $field_label) {
      IndexValidation::indexFieldVal($form_state, $field_key, $field_label);
    }
  }

}
