<?php

namespace Drupal\data_dictionary_widget\Indexes;

/**
 * Various operations for creating Data Dictionary Widget fields.
 */
class IndexValues {
  /**
   * Return updated index field values after edit.
   */
  public static function updateIndexFieldValues($field_index, $update_values, $current_index_fields) {
    $name = $update_values['field_json_metadata'][0]['fields']['data'][$field_index]['field_collection']['name'];
    $length = $update_values['field_json_metadata'][0]['fields']['data'][$field_index]['field_collection']['length'];
    
    return [
      'name' => $name,
      'length' => $length,
    ];
  }
}