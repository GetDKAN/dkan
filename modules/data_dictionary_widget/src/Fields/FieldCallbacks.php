<?php

namespace Drupal\data_dictionary_widget\Fields;

use Drupal\Core\Form\FormStateInterface;

/**
 * Various operations for the Data Dictionary Widget callbacks.
 */
class FieldCallbacks {

  /**
   * AJAX callback to update the options of the "Format" field.
   */
  public static function updateFormatOptions(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'];
    $op_index = explode("_", $trigger['#op']);
    $field = $form_state->getValue(["field_json_metadata"]);
    $format_field = $form["field_json_metadata"]["widget"][0]['dictionary_fields']["field_collection"]["group"]["format"] ?? NULL;
    $data_type = $field[0]['dictionary_fields']["field_collection"]["group"]["type"] ?? 'string';

    // Update format field and data type when in edit mode.
    if (str_contains($op, 'format')) {
      $field_index = $op_index[1];
      $format_field = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["edit_fields"][$field_index]["format"];
      $data_type = $field[0]["dictionary_fields"]["data"][$field_index]["field_collection"]["type"] ?? 'string';
    }

    $format_field['#description'] = FieldOperations::generateFormats($data_type, "description");
    $options = FieldOperations::generateFormats($data_type, "options");

    $format_field["#options"] = $options;

    return $format_field;
  }

  /**
   * Submit callback for the Edit button.
   */
  public static function editSubformCallback(array &$form, FormStateInterface $form_state) {
    // Get the current fields data
    $current_dictionary_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"] ?? [];
    $current_index_fields = $form["field_json_metadata"]["widget"][0]["indexes"]["fields"]["data"]["#rows"] ?? [];
    // Get the field index from the triggering op attribute
    // so we can use it to store the respective field later
    $op_index = explode("_", $form_state->getTriggeringElement()['#op']);
    // Get the fields we're currently modifying
    $dictionary_fields_being_modified = $form_state->get('dictionary_fields_being_modified') != NULL ? $form_state->get('dictionary_fields_being_modified') : [];
    $index_fields_being_modified = $form_state->get('index_fields_being_modified') != NULL ? $form_state->get('index_fields_being_modified') : [];
    // If the op (trigger) containes abort,
    // we're canceling the field we're currently modifying so unset it.
    if (str_contains($form_state->getTriggeringElement()['#op'], 'abort')) {
      unset($dictionary_fields_being_modified[$op_index[1]]);
    }
    // If the op (trigger) contains delete,
    // we're deleting the field we're editing so...
    if (str_contains($form_state->getTriggeringElement()['#op'], 'delete')) {
      // Unset it from being currently modified.
      unset($dictionary_fields_being_modified[$op_index[1]]);
      // Remove the respective field/data from the form.
      unset($current_dictionary_fields[$op_index[1]]);
    }
    // If the op (trigger) contains update,
    // We're saving the field we're editing so...
    if (str_contains($form_state->getTriggeringElement()['#op'], 'update')) {
      // Unset the respective currently modifying field.
      unset($dictionary_fields_being_modified[$op_index[1]]);
      // Unset the respective field/data from the form.
      unset($current_dictionary_fields[$op_index[1]]);
      // Update the respective current field data with our new input data.
      $current_dictionary_fields[$op_index[1]] = FieldValues::updateValues($op_index[1], $form_state->getUserInput(), $current_dictionary_fields);
      // Sort the current fields data.
      ksort($current_dictionary_fields);
    }
    // If the op (trigger) contains edit
    // We're editing a specific field so...
    if (str_contains($form_state->getTriggeringElement()['#op'], 'edit')) {
      // Set the field we're modifying to that field.
      $dictionary_fields_being_modified[$op_index[1]] = $current_dictionary_fields[$op_index[1]];
    }
    // Reindex the current_dictionary_fields array.
    if ($current_dictionary_fields) {
      $current_dictionary_fields = array_values($current_dictionary_fields);
    }
    // Let's retain the fields that are being modified.
    $form_state->set('index_fields_being_modified', $index_fields_being_modified);
    $form_state->set('dictionary_fields_being_modified', $dictionary_fields_being_modified);
    // Let's retain the fields that are already stored on the form, 
    // but aren't currently being modified.
    $form_state->set('current_dictionary_fields', $current_dictionary_fields);
    $form_state->set('current_index_fields', $current_index_fields );
    // Let's rebuild the form.
    $form_state->setRebuild();
  }

  /**
   * Submit callback for the Add button.
   */
  public static function addSubformCallback(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'];
    $form_state->set('add_new_field', '');
    $current_dictionary_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"];
    $current_index = $form["field_json_metadata"]["widget"][0]['indexes']["data"]["#rows"];
    $current_index_fields = $form["field_json_metadata"]["widget"][0]['indexes']["fields"]["data"]["#rows"] ?? [];

    if ($current_dictionary_fields) {
      $form_state->set('current_dictionary_fields', $current_dictionary_fields);
    }

    if ($op === 'cancel') {
      $form_state->set('cancel', TRUE);
    }

    if ($op === 'add_new_field') {
      $add_fields = FieldAddCreation::addFields();
      $form_state->set('add_new_field', $add_fields);
    }

    if ($op === 'add') {
      $form_state->set('new_dictionary_fields', $form_state->getUserInput());
      $form_state->set('add', TRUE);
      $form_state->set('cancel', FALSE);
    }

    $form_state->set('current_index_fields', $current_index_fields);
    $form_state->set('current_index', $current_index);
    $form_state->setRebuild();
  }

  /**
   * Ajax callback.
   */
  public static function subformAjax(array &$form, FormStateInterface $form_state) {
    $data_dictionary = FieldOperations::restoreDictionaryFieldsOnRebuild($form, $form_state);

    return $data_dictionary;
  }

  /**
   * Widget validation callback.
   */
  public static function customValidationCallback($element, FormStateInterface &$form_state) {
    $fields_to_validate = [
      'name' => 'Name',
      'title' => 'Title',
      'description' => 'Description',
      'format_other' => 'Other Format',
    ];

    $edit_fields_array = $form_state->getValues()["field_json_metadata"][0]["dictionary_fields"]["edit_fields"] ?? [];
    $index = $edit_fields_array ? key($edit_fields_array) : NULL;

    FieldValidation::validateFormatOther($form_state, $index);

    foreach ($fields_to_validate as $field_key => $field_label) {
      FieldValidation::validateField($form_state, $field_key, $field_label, $index);
    }
  }

}
