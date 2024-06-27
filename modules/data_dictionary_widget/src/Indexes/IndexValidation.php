<?php

namespace Drupal\data_dictionary_widget\Indexes;

use Drupal\Core\Form\FormStateInterface;

/**
 * Validation of the index fields form.
 */
class IndexValidation {

  /**
   * Validation callback for a index fields form.
   *
   * If index field name and length is empty, validation will trigger.
   *
   * @param array $element
   *   The form element being validated.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $form
   *   The complete form structure.
   */
  public static function indexFieldsValidation(array $element, FormStateInterface $form_state, array &$form) {
    $index_fields_fieldset = $form["field_json_metadata"]["widget"][0]["indexes"]["field_collection"]["group"]["index"]["fields"] ?? NULL;
    $fields = $form["field_json_metadata"]["widget"][0]["indexes"]["fields"]["data"]["#rows"] ?? NULL;

    if ($index_fields_fieldset && !$fields) {
        $form_state->setError($index_fields_fieldset, t('At least one index field is required.'));
    }
  }

  /**
   * Validation callback for a index fields edit form.
   *
   * If index field name and length is empty on edit, validation will trigger.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $field_key
   *   The key value for the field being edited.
   * @param array $field_label
   *   The label for the field being edited.
   */
  public static function indexFieldVal(FormStateInterface $form_state, $field_key, $field_label) {
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'];
    $op_index = explode("_", $op);

    // Perform validation for update operation.
    if (str_contains($op, 'update')) {
      $update_values = $form_state->getUserInput();
      $value = $update_values["field_json_metadata"][0]["indexes"]["fields"]["edit_index_fields"][0][$field_key];
      if ($value === "") {
        $field = "field_json_metadata][0][indexes][fields][edit_index_fields][$op_index[4]][$field_key";
        $form_state->setErrorByName($field, t($field_label . ' field is required.'));
      }
    }
  }

}
