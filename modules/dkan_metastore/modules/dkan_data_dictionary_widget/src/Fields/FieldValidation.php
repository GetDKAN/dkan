<?php

namespace Drupal\data_dictionary_widget\Fields;

use Drupal\Core\Form\FormStateInterface;

/**
 * Validation of the data dictionary form fields.
 */
class FieldValidation {

  /**
   * Throws error when other format field is empty.
   */
  public static function validateFormatOther(FormStateInterface &$form_state, $index) {
    $input = $form_state->getUserInput()['field_json_metadata'][0]['dictionary_fields']['field_collection'] ?? NULL;

    if ($input) {
      $format_field = $input['group']['format'] ?? '';
      $other_format_value = $input['group']['format_other'] ?? '';
      // Set 'format_other' error.
      if ($format_field === 'other' && empty($other_format_value)) {
        $form_state->setErrorByName('field_json_metadata][0][dictionary_fields][field_collection][group][format_other', t('Other format is required when "Other" is selected as the format.'));
      }
    }
  }

  /**
   * Throws error when edit fields are empty.
   */
  public static function validateField(FormStateInterface &$form_state, $field_key, $field_label, $index) {
    $path = ['field_json_metadata', 0, 'dictionary_fields'];

    if ($form_state->hasValue(array_merge($path,
      ['data', $index, 'field_collection']))) {
      $field_value = $form_state->getValue(array_merge($path,
      ['data', $index, 'field_collection', $field_key]));
      $error_field = "field_json_metadata][0][dictionary_fields][edit_fields][$index][$field_key";
    }
    elseif ($form_state->hasValue(array_merge($path,
      ['field_collection', 'group', $field_key]))) {
      $field_value = $form_state->getValue(array_merge($path,
      ['field_collection', 'group', $field_key]));
      $error_field = "field_json_metadata[0][dictionary_fields][field_collection][group][format_other";
    }

    $field_properties = [
      'field_key' => $field_key,
      'field_label' => $field_label,
      'index' => $index,
    ];

    self::validateFormatOtherEdit($form_state, $field_properties, $error_field, $field_value);
  }

  /**
   * Throws error when format_other edit fields are empty.
   */
  private static function validateFormatOtherEdit($form_state, $field_properties, $error_field, $field_value) {
    $field_key = $field_properties['field_key'];
    $field_label = $field_properties['field_label'];
    $index = $field_properties['index'];

    $format = $form_state->getValue([
      'field_json_metadata',
      0,
      'dictionary_fields',
      'data',
      $index,
      'field_collection',
      'format',
    ]);

    if ($field_value === "" && $field_key !== "format_other") {
      $form_state->setErrorByName($error_field, t('@label is required.', ['@label' => $field_label]));
    }

    if ($field_key === "format_other" && $field_value === "" && $format === "other") {
      $form_state->setErrorByName($error_field, t('@label is required when "Other" is selected as the format.', ['@label' => $field_label]));
    }
  }

}
