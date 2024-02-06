<?php

namespace Drupal\data_dictionary_widget\Controller\Widget;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Various operations for the Data Dictionary Widget callbacks.
 */
class FieldCallbacks extends ControllerBase {

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
    $trigger = $form_state->getTriggeringElement();
    $current_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"];
    $op = $trigger['#op'];
    $op_index = explode("_", $trigger['#op']);
    $currently_modifying = $form_state->get('fields_being_modified') != NULL ? $form_state->get('fields_being_modified') : [];

    if (str_contains($op, 'abort')) {
      unset($currently_modifying[$op_index[1]]);
    }

    if (str_contains($op, 'delete')) {
      unset($currently_modifying[$op_index[1]]);
      unset($current_fields[$op_index[1]]);
    }

    if (str_contains($op, 'update')) {
      $update_values = $form_state->getUserInput();
      unset($currently_modifying[$op_index[1]]);
      unset($current_fields[$op_index[1]]);
      $current_fields[$op_index[1]] = FieldValues::updateValues($op_index[1], $update_values, $current_fields);
      ksort($current_fields);
    }

    if (str_contains($op, 'edit')) {
      $currently_modifying[$op_index[1]] = $current_fields[$op_index[1]];
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
   * Submit callback for the bulk changes button.
   */
  public static function bulkChangeSubformCallback(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $current_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"];
    $op = $trigger['#op'];
    $currently_modifying = $form_state->get('fields_being_modified') != NULL ? $form_state->get('fields_being_modified') : [];

    if ($op == 'save_all' && count($currently_modifying) > 0) {
      $update_values = $form_state->getUserInput();
      $current_fields = self::saveAllCallback($currently_modifying, $current_fields, $update_values);
      $form_state->set('fields_being_modified', []);
    }

    if ($op == 'edit_all' && count($currently_modifying) == 0) {
      $form_state->set('fields_being_modified', $current_fields);
    }

    if ($op == 'cancel_all' && count($currently_modifying) > 0) {
      $form_state->set('fields_being_modified', []);
    }

    $form_state->set('current_fields', $current_fields);
    $form_state->setRebuild();
  }

  /**
   * Submit callback for the save all button.
   */
  public static function saveAllCallback($currently_modifying, $current_fields, $update_values) {
    $current_fields = [];
    foreach ($currently_modifying as $key => $value) {
      unset($currently_modifying[$key]);
      unset($current_fields[$key]);
      $current_fields[$key] = FieldValues::updateValues($key, $update_values);
    }

    ksort($current_fields);
    return $current_fields;
  }

  /**
   * Ajax callback.
   */
  public static function subformAjax(array &$form, FormStateInterface $form_state) {
    return $form["field_json_metadata"]["widget"][0]["dictionary_fields"];
  }

  /**
   * Widget validation callback.
   */
  public static function customValidationCallback($element, &$form_state) {
    $format_field = $form_state->getUserInput()['field_json_metadata'][0]['dictionary_fields']['field_collection']['group']['format'];
    $other_format_value = $element['#value'];

    // Check if the 'format' field is 'other' and 'format_other' field is empty.
    if ($format_field == 'other' && empty($other_format_value)) {
      // Add a validation error.
      $form_state->setError($element, t('Other format is required when "Other" is selected as the format.'));
    }
  }

}
