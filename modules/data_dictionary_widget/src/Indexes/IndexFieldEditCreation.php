<?php

namespace Drupal\data_dictionary_widget\Indexes;

/**
 * Various operations for creating Data Dictionary Widget add fields.
 */

class IndexFieldEditCreation {
  /**
   * Create edit fields for Data Dictionary Widget.
   */
  public static function editIndexFields($indexKey, $current_index_fields) {
    $indexKeyExplode = explode("_", $indexKey);
    $edit_index_fields['name'] = [
      '#name' => 'field_json_metadata[0][fields][data][' . $indexKeyExplode[3] . '][field_collection][name]',
      '#type' => 'textfield',
      '#value' => $current_index_fields[$indexKeyExplode[3]]['name'],
      '#title' => 'Name',
    ];
    $edit_index_fields['length'] = [
      '#name' => 'field_json_metadata[0][fields][data]['. $indexKeyExplode[3] .'][field_collection][length]',
      '#type' => 'number',
      '#value' => $current_index_fields[$indexKeyExplode[3]]['length'],
      '#title' => 'Length',
    ];


    $edit_index_fields['update_index_field']['actions'] = self::createIndexActionFields($indexKey);
    return $edit_index_fields;

  }

    /**
   * Create edit fields for Data Dictionary Widget.
   */
  public static function editIndex($indexKey, $current_index) {
    $indexKeyExplode = explode("_", $indexKey);
    $edit_index['name'] = [
      '#name' => 'field_json_metadata[0][index][data][' . $indexKeyExplode[3] . '][field_collection][name]',
      '#type' => 'textfield',
      '#value' => $current_index[$indexKeyExplode[3]]['name'],
      '#title' => 'Name',
    ];
    $edit_index['length'] = [
      '#name' => 'field_json_metadata[0][index][data]['. $indexKeyExplode[3] .'][field_collection][length]',
      '#type' => 'number',
      '#value' => $current_index[$indexKeyExplode[3]]['length'],
      '#title' => 'Length',
    ];


    $edit_index['update_index_field']['actions'] = self::createIndexActionFields($indexKey);
    return $edit_index;

  }

  /**
   * Create Action buttons.
   */
  private static function createIndexActionFields($indexKey) {
    return [
      '#type' => 'actions',
      'save_update' => IndexFieldButtons::submitIndexFieldButton('edit', $indexKey),
      'cancel_updates' => IndexFieldButtons::cancelIndexFieldButton('edit', $indexKey),
      'delete_field' => IndexFieldButtons::deleteIndexButton($indexKey),
    ];
  }

}