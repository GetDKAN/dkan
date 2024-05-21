<?php

namespace Drupal\data_dictionary_widget\Fields;

/**
 * Various operations for creating Data Dictionary Widget add fields.
 */
class FieldAddCreation {

  /**
   * Create add fields for Data Dictionary Widget.
   */
  public static function addFields() {
    $add_fields['#access'] = FALSE;
    $add_fields['group'] = [
      '#type' => 'fieldset',
      '#title' => t('Add new field'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    $add_fields['group']['name'] = [
      '#name' => 'field_json_metadata[0][dictionary_fields][field_collection][group][name]',
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => 'Name',
      '#description' => t('Machine name of the field/column in the data table.'),
    ];
    $add_fields['group']['title'] = self::createTitle();
    $add_fields['group']['type'] = self::createType();
    $add_fields['group']['format'] = self::createFormat();
    $add_fields['group']['format_other'] = self::createFormatOther();
    $add_fields['group']['description'] = self::createDescriptionField();
    $add_fields['group']['actions'] = self::createActionFields();

    return $add_fields;
  }

  /**
   * Create Type field.
   */
  private static function createTitle() {
    return [
      '#name' => 'field_json_metadata[0][dictionary_fields][field_collection][group][title]',
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => 'Title',
      '#description' => t('A human-readable title.'),
    ];
  }

  /**
   * Create Type field.
   */
  private static function createType() {
    return [
      '#name' => 'field_json_metadata[0][dictionary_fields][field_collection][group][type]',
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => 'Data type',
      '#default_value' => 'string',
      '#op' => 'type',
      '#options' => [
        'string' => t('String'),
        'date' => t('Date'),
        'datetime' => t('Datetime'),
        'integer' => t('Integer'),
        'number' => t('Number'),
        'year' => t('Year'),
        'boolean' => t('Boolean'),
      ],
      '#ajax' => [
        'callback' => '\Drupal\data_dictionary_widget\Fields\FieldCallbacks::updateFormatOptions',
        'method' => 'replace',
        'wrapper' => 'field-json-metadata-format',
      ],
    ];
  }

  /**
   * Create Format field.
   */
  private static function createFormat() {
    return [
      '#name' => 'field_json_metadata[0][dictionary_fields][field_collection][group][format]',
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => 'Format',
      '#description' => FieldOperations::generateFormats("string", "description"),
      '#default_value' => 'default',
      '#prefix' => '<div id = field-json-metadata-format>',
      '#suffix' => '</div>',
      '#validated' => TRUE,
      '#options' => [
        'default' => t('default'),
        'email' => t('email'),
        'uri' => t('uri'),
        'binary' => t('binary'),
        'uuid' => t('uuid'),
      ],
    ];
  }

  /**
   * Create Format Other field.
   */
  private static function createFormatOther() {
    return [
      '#name' => 'field_json_metadata[0][dictionary_fields][field_collection][group][format_other]',
      '#type' => 'textfield',
      '#title' => t('Other format'),
      '#description' => t('A supported format'),
      '#states' => [
        'visible' => [
          ':input[name="field_json_metadata[0][dictionary_fields][field_collection][group][format]"]' => ['value' => 'other'],
        ],
        'required' => [
          ':input[name="field_json_metadata[0][dictionary_fields][field_collection][group][format]"]' => ['value' => 'other'],
        ],
      ],
    ];
  }

  /**
   * Create Action buttons.
   */
  private static function createActionFields() {
    return [
      '#type' => 'actions',
      'save_settings' => FieldButtons::submitButton('add', NULL),
      'cancel_settings' => FieldButtons::cancelButton('cancel', NULL),
    ];
  }

  /**
   * Create Description field.
   */
  private static function createDescriptionField() {
    return [
      '#name' => 'field_json_metadata[0][dictionary_fields][field_collection][group][description]',
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => 'Description',
      '#description' => t('Information about the field data.'),
    ];
  }

}
