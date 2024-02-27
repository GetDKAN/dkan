<?php

namespace Drupal\data_dictionary_widget\Controller\Widget\DictionaryIndexes;

use Drupal\Core\Controller\ControllerBase;

/**
 * Various operations for creating Data Dictionary Widget add fields.
 */

class IndexFieldEditCreation extends ControllerBase {
  /**
   * Create edit fields for Data Dictionary Widget.
   */
  public static function editIndexFields($key, $current_index_fields) {

    $edit_index_fields['name'] = [
      '#name' => 'field_json_metadata[0][index_fields][data][' . $key . '][field_collection][name]',
      '#type' => 'textfield',
      '#value' => $current_index_fields[$key]['name'],
      '#title' => 'Name',
    ];
    $edit_index_fields['length'] = [
      '#name' => 'field_json_metadata[0][index_fields][data][' . $key . '][field_collection][length]',
      '#type' => 'number',
      '#value' => $current_index_fields[$key]['length'],
      '#title' => 'Length',
    ];


    $edit_index_fields['update_index_field']['actions'] = self::createIndexActionFields($key);
    return $edit_index_fields;

  }

  /**
   * Create Action buttons.
   */
  private static function createIndexActionFields($key) {
    return [
      '#type' => 'actions',
      'save_update' => IndexFieldButtons::submitIndexFieldButton('edit', $key),
      'cancel_updates' => IndexFieldButtons::cancelIndexFieldButton('edit', $key),
      'delete_field' => IndexFieldButtons::deleteIndexButton($key),
    ];
  }

}