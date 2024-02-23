<?php

namespace Drupal\data_dictionary_widget\Controller\Widget\DictionaryIndexes;

use Drupal\Core\Controller\ControllerBase;

/**
 * Various operations for creating Data Dictionary Widget fields.
 */
class IndexFieldValues extends ControllerBase {
  /**
   * Return updated index field values after edit.
   */
  public static function updateIndexFieldValues($field_index, $update_values, $current_index_fields) {
    $format = $update_values['field_json_metadata'][0]['index_fields']['data'][$field_index]['field_collection']['format'];
    $format_other = $update_values['field_json_metadata'][0]['index_fields']['data'][$field_index]['field_collection']['format_other'];
    $name = $update_values['field_json_metadata'][0]['index_fields']['data'][$field_index]['field_collection']['name'];

    return [
      'name' => $name,
      'length' => $update_values['field_json_metadata'][0]['index_fields']['data'][$field_index]['field_collection']['length'],
    ];
  }
}