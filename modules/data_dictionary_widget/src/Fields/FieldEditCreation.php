<?php

namespace Drupal\data_dictionary_widget\Fields;

/**
 * Various operations for creating Data Dictionary Widget add fields.
 */
class FieldEditCreation {

  /**
   * Create edit fields for Data Dictionary Widget.
   */
  public static function editFields($key, $current_dictionary_fields) {

    $edit_fields['name'] = [
      '#name' => 'field_json_metadata[0][dictionary_fields][data][' . $key . '][field_collection][name]',
      '#type' => 'textfield',
      '#value' => $current_dictionary_fields[$key]['name'],
      '#required' => TRUE,
      '#title' => 'Name',
      '#description' => t('Machine name of the field/column in the data table.'),
    ];
    $edit_fields['title'] = [
      '#name' => 'field_json_metadata[0][dictionary_fields][data][' . $key . '][field_collection][title]',
      '#type' => 'textfield',
      '#value' => $current_dictionary_fields[$key]['title'],
      '#required' => TRUE,
      '#title' => 'Title',
      '#description' => t('A human-readable title.'),
    ];
    $edit_fields['type'] = self::createType($key, $current_dictionary_fields);
    $edit_fields['format'] = self::createFormat($key, $current_dictionary_fields);
    $edit_fields['format_other'] = self::createFormatOther($key, $current_dictionary_fields);
    $edit_fields['description'] = self::createDescriptionField($key, $current_dictionary_fields);

    $edit_fields['update_field']['actions'] = self::createActionFields($key);
    return $edit_fields;

  }

  /**
   * Create Type field.
   */
  private static function createType($key, $current_dictionary_fields) {
    return [
      '#name' => 'field_json_metadata[0][dictionary_fields][data][' . $key . '][field_collection][type]',
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => 'Data type',
      '#default_value' => 'string',
      '#value' => $current_dictionary_fields[$key]['type'],
      '#op' => 'format_' . $key,
      '#options' => FieldOperations::setTypeOptions(),
      '#ajax' => [
        'callback' => '\Drupal\data_dictionary_widget\Fields\FieldCallbacks::updateFormatOptions',
        'method' => 'replace',
        'wrapper' => 'field-json-metadata-' . $key . '-format',
      ],
    ];
  }

  /**
   * Create Format field.
   */
  private static function createFormat($key, $current_dictionary_fields) {
    $format_options = FieldOperations::setFormatOptions($current_dictionary_fields[$key]['type']);
    $value = in_array($current_dictionary_fields[$key]['format'], $format_options) ? $current_dictionary_fields[$key]['format'] : 'other';
    return [
      '#name' => 'field_json_metadata[0][dictionary_fields][data][' . $key . '][field_collection][format]',
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => 'Format',
      '#default_value' => 'default',
      '#description' => FieldOperations::generateFormatDescription($current_dictionary_fields[$key]['type']),
      '#value' => $value,
      '#prefix' => '<div id = field-json-metadata-' . $key . '-format>',
      '#suffix' => '</div>',
      '#validated' => TRUE,
      '#options' => $format_options,
    ];
  }

  /**
   * Create Format Other field.
   */
  private static function createFormatOther($key, $current_dictionary_fields) {
    $format_options = FieldOperations::setFormatOptions($current_dictionary_fields[$key]['type']);
    $value = !in_array($current_dictionary_fields[$key]['format'], $format_options) ? $current_dictionary_fields[$key]['format'] : NULL;

    return [
      '#name' => 'field_json_metadata[0][dictionary_fields][data][' . $key . '][field_collection][format_other]',
      '#type' => 'textfield',
      '#title' => t('Other format'),
      '#value' => $value,
      '#description' => t('A supported format'),
      '#states' => [
        'required' => [
          ':input[name="field_json_metadata[0][dictionary_fields][data][' . $key . '][field_collection][format]"]' => ['value' => 'other'],
        ],
        'visible' => [
          ':input[name="field_json_metadata[0][dictionary_fields][data][' . $key . '][field_collection][format]"]' => ['value' => 'other'],
        ],
      ],
    ];
  }

  /**
   * Create Action buttons.
   */
  private static function createActionFields($key) {
    return [
      '#type' => 'actions',
      'save_update' => FieldButtons::submitButton('edit', $key),
      'cancel_updates' => FieldButtons::cancelButton('edit', $key),
      'delete_field' => FieldButtons::deleteButton($key),
    ];
  }

  /**
   * Create Description field.
   */
  private static function createDescriptionField($key, $current_dictionary_fields) {
    return [
      '#name' => 'field_json_metadata[0][dictionary_fields][data][' . $key . '][field_collection][description]',
      '#type' => 'textarea',
      '#value' => $current_dictionary_fields[$key]['description'],
      '#required' => TRUE,
      '#title' => 'Description',
      '#description' => t('Information about the field data.'),
    ];
  }

}
