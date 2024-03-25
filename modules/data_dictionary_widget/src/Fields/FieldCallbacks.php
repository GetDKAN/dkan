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
    $format_field = $form["field_json_metadata"]["widget"][0]['dictionary_fields']["field_collection"]["group"]["format"];
    $data_type = $field[0]['dictionary_fields']["field_collection"]["group"]["type"] ?? 'string';
    $field_index = $op_index[1];

    // The update format field is located in a diferent location.
    if (str_contains($op, 'format')) {
      $format_field = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["edit_fields"][$field_index]["format"];
      $data_type = $field[0]["dictionary_fields"]["data"][$field_index]["field_collection"]["type"] ?? 'string';
    }

    $format_field['#description'] = FieldOperations::generateFormatDescription($data_type);
    $options = FieldOperations::setFormatOptions($data_type);

    $format_field["#options"] = $options;
    return $format_field;

  }

  /**
   * Submit callback for the Edit button.
   */
  public static function editSubformCallback(array &$form, FormStateInterface $form_state) {
    $current_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"];
    $op_index = explode("_", $form_state->getTriggeringElement()['#op']);
    $currently_modifying = $form_state->get('fields_being_modified') != NULL ? $form_state->get('fields_being_modified') : [];

    if (str_contains($form_state->getTriggeringElement()['#op'], 'abort')) {
      unset($currently_modifying[$op_index[1]]);
    }

    if (str_contains($form_state->getTriggeringElement()['#op'], 'delete')) {
      unset($currently_modifying[$op_index[1]]);
      unset($current_fields[$op_index[1]]);
    }

    if (str_contains($form_state->getTriggeringElement()['#op'], 'update')) {
      unset($currently_modifying[$op_index[1]]);
      unset($current_fields[$op_index[1]]);
      $current_fields[$op_index[1]] = FieldValues::updateValues($op_index[1], $form_state->getUserInput(), $current_fields);
      ksort($current_fields);
    }

    if (str_contains($form_state->getTriggeringElement()['#op'], 'edit')) {
      $currently_modifying[$op_index[1]] = $current_fields[$op_index[1]];
    }

    // Reindex the current_fields array.
    if ($current_fields) {
      $current_fields = array_values($current_fields);
    }

    $form_state->set('fields_being_modified', $currently_modifying);
    $form_state->set('current_fields', $current_fields);
    $form_state->setRebuild();
  }

  /**
   * Submit callback for the Add button.
   */
  public static function addSubformCallback(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'];
    $form_state->set('add_new_field', '');
    // $fields_being_added = $form_state->set('fields_being_added', '');
    $current_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"];
    if ($current_fields) {
      $form_state->set('current_fields', $current_fields);
    }

    if ($op === 'cancel') {
      $form_state->set('cancel', TRUE);
    }

    if ($op === 'add_new_field') {
      $add_fields = FieldAddCreation::addFields();
      $form_state->set('add_new_field', $add_fields);
    }

    if ($op === 'add') {
      $form_state->set('new_fields', $form_state->getUserInput());
      $form_state->set('add', TRUE);
      $form_state->set('cancel', FALSE);
    }

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
      'format_other' => "Other Format"
    ];

    $edit_fields_array = $form_state->getValues()["field_json_metadata"][0]["dictionary_fields"]["edit_fields"] ?? [];
    $index = $edit_fields_array ? key($edit_fields_array) : null;

    foreach ($fields_to_validate as $field_key => $field_label) {
      $format = $form_state->getValue(['field_json_metadata', 0, 'dictionary_fields', 'data', $index, 'field_collection', 'format']);

      if ($form_state->hasValue(['field_json_metadata', 0, 'dictionary_fields', 'data', $index, 'field_collection'])) {
        $field_value = $form_state->getValue(['field_json_metadata', 0, 'dictionary_fields', 'data', $index, 'field_collection', $field_key]);
        $error_field = "field_json_metadata][0][dictionary_fields][edit_fields][$index][$field_key";
      } elseif ($form_state->hasValue(['field_json_metadata', 0, 'dictionary_fields', 'field_collection', 'group', $field_key])) {
        $field_value = $form_state->getValue(['field_json_metadata', 0, 'dictionary_fields', 'field_collection', 'group', $field_key]);
        $error_field = "field_json_metadata[0][dictionary_fields][field_collection][group][format_other";
      }

      if ($field_value === "" && $field_key !== "format_other") {
        $form_state->setErrorByName($error_field, t('@label is required.', ['@label' => $field_label]));      
      }

      if ($field_key === "format_other" && $field_value === "" && $format === "other") {
        $form_state->setErrorByName($error_field, t('@label is required when "Other" is selected as the format.', ['@label' => $field_label]));      
      }
    }

    $format_field = $form_state->getUserInput()['field_json_metadata'][0]['dictionary_fields']['field_collection']['group']['format'];
    $other_format_value = $form_state->getUserInput()['field_json_metadata'][0]['dictionary_fields']['field_collection']['group']['format_other'];

    if ($format_field === 'other' && empty($other_format_value)) {
      $form_state->setErrorByName('field_json_metadata][0][dictionary_fields][field_collection][group][format_other', t('Other format is required when "Other" is selected as the format.'));
    }
  }

}
