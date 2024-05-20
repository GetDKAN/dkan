<?php

namespace Drupal\data_dictionary_widget\Indexes;

/**
 * Various operations for creating Data Dictionary Widget add fields.
 */

class IndexEditCreation {
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
   * Create edit fields for Data Dictionary Widget.
   */
  public static function editIndexFields($indexFieldKey, $current_index_fields) {
    $indexFieldKeyExplode = explode("_", $indexFieldKey);
    $edit_index_fields['name'] = [
      '#name' => 'field_json_metadata[0][fields][data][' . $indexFieldKeyExplode[3] . '][field_collection][name]',
      '#type' => 'textfield',
      '#value' => $current_index_fields[$indexFieldKeyExplode[3]]['name'],
      '#title' => 'Name',
    ];
    $edit_index_fields['length'] = [
      '#name' => 'field_json_metadata[0][fields][data]['. $indexFieldKeyExplode[3] .'][field_collection][length]',
      '#type' => 'number',
      '#value' => $current_index_fields[$indexFieldKeyExplode[3]]['length'],
      '#title' => 'Length',
    ];


    $edit_index_fields['update_index_field']['actions'] = self::createIndexFieldActionFields($indexFieldKey);
    return $edit_index_fields;

  }

  /**
   * Create Index Action buttons.
   */
  private static function createIndexActionFields($indexKey) {
    return [
      '#type' => 'actions',
      'save_update_index' => IndexButtons::submitIndexButton('edit', $indexKey),
      'cancel_updates_index' => IndexButtons::cancelIndexButton('edit', $indexKey),
      'delete_index_index' => IndexButtons::deleteIndexButton($indexKey),
    ];
  }

  /**
   * Create Index Field Action buttons.
   */
  private static function createIndexFieldActionFields($indexFieldKey) {
    return [
      '#type' => 'actions',
      'save_update_index_field' => IndexButtons::submitIndexFieldButton('edit', $indexFieldKey),
      'cancel_update_index_field' => IndexButtons::cancelIndexFieldButton('edit', $indexFieldKey),
      'delete_index_field' => IndexButtons::deleteIndexFieldButton($indexFieldKey),
    ];
  }

}