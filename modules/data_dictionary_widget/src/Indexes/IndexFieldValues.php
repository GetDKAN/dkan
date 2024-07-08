<?php

namespace Drupal\data_dictionary_widget\Indexes;

/**
 * Various operations for creating index fields.
 */
class IndexFieldValues {
  /**
   * Return updated index field values after edit.
   */
  public static function updateIndexFieldValues($field_index, $update_values, $current_index_fields) {
    $name = $update_values["field_json_metadata"][0]["indexes"]["fields"]["edit_index_fields"][$field_index]["name"];
    $length = $update_values["field_json_metadata"][0]["indexes"]["fields"]["edit_index_fields"][$field_index]["length"];
    
    return [
      'name' => $name,
      'length' => (int)$length,
    ];
  }
}
