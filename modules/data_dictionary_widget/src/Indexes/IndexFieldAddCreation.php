<?php

namespace Drupal\data_dictionary_widget\Indexes;

/**
 * Various operations for creating Index fields.
 */
class IndexFieldAddCreation {

 /**
  * Create fields for adding an index.
  */
 public static function addIndex() {
  $add_index['#access'] = FALSE;
  $add_index['group'] = [
    '#type' => 'fieldset',
    '#title' => t('Index'),
    '#open' => TRUE,
    '#prefix' => '<div id = field-json-metadata-index>',
    '#suffix' => '</div>',
    '#element_validate' => [
      ['\Drupal\data_dictionary_widget\Indexes\IndexValidation', 'indexFieldsValidation']
    ],
  ];

  $add_index['group']['index']['description'] = [
    '#name' => 'field_json_metadata[0][indexes][field_collection][group][index][description]',    
    '#description' => t('Description of index purpose or functionality.'),
    '#type' => 'textfield',
    '#title' => 'Name',
    '#required' => TRUE,
  ];

  $add_index['group']['index']['type'] = [
    '#name' => 'field_json_metadata[0][indexes][field_collection][group][index][type]',
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

  $add_index['group']['index']['fields'] = [
    '#type' => 'fieldset',
    '#title' => t('Fields'),
    '#prefix' => '<div id = field-json-metadata-index-fields>',
    '#suffix' => '</div>',
    '#markup' => t('<div class="claro-details__description">One or more fields included in index. Must be keys from the fields object.</div>'),
    '#attributes' => [
      'class' => ['index-fields-form'],
    ],
  ];
  
  $add_index['group']['index']['fields']['add_row_button'] = IndexFieldButtons::addIndexFieldButton();
  $add_index['group']['index']['save_index'] = IndexFieldButtons::submitIndexButton('add_index', NULL);
  $add_index['group']['index']['cancel_index'] = IndexFieldButtons::cancelIndexButton('cancel_index', NULL);
   
   return $add_index;
 }

  /**
   * Create fields for adding an index field.
   */
  public static function addIndexFields($current_index_fields) {
    $id = "field-json-metadata-index-fields-new";
    $add_index_fields['#access'] = FALSE;
    $add_index_fields['group'] = [
      '#type' => 'fieldset',
      '#title' => t('Add new field'),
      '#prefix' => "<div id = $id>",
      '#suffix' => '</div>',
      '#markup' => t('<div class="claro-details__description">Add a single index field. Must be keys from the fields object.</div>'),
    ];

    $add_index_fields['group']['index']['fields']['name'] = [
      '#name' => 'field_json_metadata[0][indexes][fields][field_collection][group][index][fields][name]',
      '#type' => 'textfield',
      '#title' => 'Name',
      '#required' => TRUE,
    ];

    $add_index_fields['group']['index']['fields']['length'] = self::createIndexFieldLengthField();
    $add_index_fields['group']['index']['fields']['actions'] = self::createIndexActionFields($id);
    
    return $add_index_fields;
  }

  /**
   * Create Description field.
   */
  private static function createIndexFieldLengthField() {
    return [
      '#name' => 'field_json_metadata[0][indexes][fields][field_collection][group][index][fields][length]',
      '#type' => 'number',
      '#title' => 'Length',
      '#required' => TRUE,
    ];
  }

  /**
   * Create Action buttons.
   */
  private static function createIndexActionFields($id) {
    return [
      '#type' => 'actions',
      'save_index_settings' => IndexFieldButtons::submitIndexFieldButton('add', NULL),
      'cancel_index_settings' => IndexFieldButtons::cancelIndexFieldButton('cancel', NULL, $id),
    ];
  }
}