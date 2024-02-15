<?php

namespace Drupal\data_dictionary_widget\Controller\Widget\DictionaryIndexes;

use Drupal\Core\Controller\ControllerBase;

/**
 * Various operations for creating Data Dictionary Widget add fields.
 */
class IndexFieldAddCreation extends ControllerBase {

  /**
   * Create add fields for Data Dictionary Widget.
   */
  public static function addIndexFields() {
    $add_index_fields['#access'] = FALSE;
    $add_index_fields['group'] = [
      '#type' => 'fieldset',
      '#title' => t('Add new field'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];

    $add_index_fields['group']['name'] = [
      '#name' => 'field_json_metadata[0][index_fields][field_collection][group][name]',
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => 'Name',
    ];
    $add_index_fields['group']['description'] = self::createDescriptionField();
    
    return $add_index_fields;
  }

  /**
   * Create Description field.
   */
  private static function createDescriptionField() {
    return [
      '#name' => 'field_json_metadata[0][index_fields][field_collection][group][description]',
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => 'Description',
    ];
  }
}