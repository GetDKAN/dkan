<?php

namespace Drupal\data_dictionary_widget\Indexes;

/**
 * Various operations for creating Data Dictionary Widget add fields.
 */
class IndexFieldAddCreation {

 /**
  * Create add fields for Data Dictionary Widget.
  */
 public static function addIndex() {
  $add_index['#access'] = FALSE;
  $add_index['group'] = [
    '#type' => 'fieldset',
    '#title' => t('Index'),
    '#open' => TRUE,
    '#prefix' => '<div id = field-json-metadata-dictionary-index>',
    '#suffix' => '</div>',
  ];

  //$add_index['group']['indexes']['add_row_button'] = IndexFieldButtons::addIndexFieldButton();
  $add_index['group']['indexes']['description'] = [
    '#name' => 'field_json_metadata[0][index][field_collection][group][description]',
    '#description' => t('Description of index purpose or functionality.'),
    '#type' => 'textfield',
    //'#required' => TRUE,
    '#title' => 'Name',
  ];

  $add_index['group']['indexes']['type'] = [
    '#name' => 'field_json_metadata[0][index][field_collection][group][type]',
    '#type' => 'select',
    '#description' => t('Index type.'),
    '#title' => 'Index Type',
    '#default_value' => 'index',
    '#op' => 'index_type',
    '#required' => TRUE,
    '#options' => [
      'index' => t('index'),
      'fulltext' => t('fulltext'),
    ],
  ];

  $add_index['group']['indexes']['fields'] = [
    '#type' => 'fieldset',
    '#title' => t('Fields'),
    '#required' => TRUE,
    '#prefix' => '<div id = field-json-metadata-dictionary-index-fields-new>',
    '#suffix' => '</div>',
    '#markup' => t('<div class="claro-details__description">Test One or more fields included in index. Must be keys from the fields object.</div>'),
  ];
  
  $add_index['group']['indexes']['fields']['add_row_button'] = IndexFieldButtons::addIndexFieldButton();

  //$add_index['group']['indexes']['add_row_button'] = self::createIndexActionFields();

  $add_index['group']['indexes']['save_index'] = IndexFieldButtons::submitIndexButton('add_index', NULL);
  $add_index['group']['indexes']['cancel_index'] = IndexFieldButtons::cancelIndexButton('cancel_index', NULL);
   
   return $add_index;
 }

  /**
   * Create add fields for Data Dictionary Widget.
   */
  public static function addIndexFields() {
    $add_index_fields['#access'] = FALSE;
    $add_index_fields['group'] = [
      '#type' => 'fieldset',
      '#title' => t('Add new field'),
      '#prefix' => '<div id = field-json-metadata-dictionary-index-fields-new>',
      '#suffix' => '</div>',
    ];

    // $add_index_fields['group']['indexes']['index_fields'] = [
    //   '#prefix' => '<div id = field-json-metadata-dictionary-index-fields>',
    //   '#suffix' => '</div>',
    // ];

    $add_index_fields['group']['indexes']['fields']['name'] = [
      '#name' => 'field_json_metadata[0][fields][field_collection][group][name]',
      '#type' => 'textfield',
      '#title' => 'Name',
    ];
    $add_index_fields['group']['indexes']['fields']['length'] = self::createIndexFieldLengthField();
    $add_index_fields['group']['indexes']['fields']['actions'] = self::createIndexActionFields();
    
    return $add_index_fields;
  }

  /**
   * Create Description field.
   */
  private static function createIndexFieldLengthField() {
    return [
      '#name' => 'field_json_metadata[0][fields][field_collection][group][length]',
      '#type' => 'number',
      '#title' => 'Length',
    ];
  }

  /**
   * Create Action buttons.
   */
  private static function createIndexActionFields() {
    return [
      '#type' => 'actions',
      'save_index_settings' => IndexFieldButtons::submitIndexFieldButton('add', NULL),
      'cancel_index_settings' => IndexFieldButtons::cancelIndexFieldButton('cancel', NULL),
    ];
  }
}