<?php

namespace Drupal\data_dictionary_widget\Indexes;

/**
 * Various operations for returning index values when editing the field.
 */
class IndexFieldValues {

  /**
   * Return updated index field values after edit.
   */
  public static function updateIndexFieldValues($field_index, $update_values, $current_index_fields) {
    $name = $update_values["field_json_metadata"][0]["dictionary_fields"]["data"][$field_index]["field_collection"]["name"];
    $length = $update_values["field_json_metadata"][0]["dictionary_fields"]["data"][$field_index]["field_collection"]["length"];

    return [
      'name' => $name,
      'length' => (int) $length,
    ];
  }

  /**
   * Return updated index values after edit.
   */
  public static function updateIndexValues($field_index, $update_values, $current_index) {
    $description = $update_values["field_json_metadata"][0]["indexes"]["edit_index"][$field_index]["description"];
    $type = $update_values["field_json_metadata"][0]["indexes"]["edit_index"][$field_index]["type"];
    $fields = $current_index[$field_index]["fields"];

    return [
      'description' => $description,
      'type' => $type,
      'fields' => $fields,
    ];
  }

}
