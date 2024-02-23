<?php

namespace Drupal\data_dictionary_widget\Controller\Widget\DictionaryIndexes;

use Drupal\Core\Controller\ControllerBase;
use Drupal\data_dictionary_widget\Controller\Widget\FieldOperations;

/**
 * Various operations for the Data Dictionary Widget.
 */
class IndexFieldOperations extends ControllerBase {
  /**
   * Setting ajax elements.
   */
  public static function setIndexAjaxElements(array $dictionaryIndexFields) {
    foreach ($dictionaryIndexFields['data']['#rows'] as $row => $data) {
      $edit_button = $dictionaryIndexFields['edit_buttons'][$row] ?? NULL;
      $edit_fields = $dictionaryIndexFields['edit_fields'][$row] ?? NULL;
      // Setting the ajax fields if they exsist.
      if ($edit_button) {
        $dictionaryIndexFields['data']['#rows'][$row] = array_merge($data, $edit_button);
        unset($dictionaryIndexFields['edit_buttons'][$row]);
      }
      elseif ($edit_fields) {
        unset($dictionaryIndexFields['data']['#rows'][$row]);
        $dictionaryIndexFields['data']['#rows'][$row]['field_collection'] = $edit_fields;
        // Remove the buttons so they don't show up twice.
        unset($dictionaryIndexFields['edit_fields'][$row]);
        ksort($dictionaryIndexFields['data']['#rows']);
      }

    }

    return $dictionaryIndexFields;
  }

  /**
   * Cleaning the data up.
   */
  public static function processIndexDataResults($index_data_results, $current_index_fields, $index_field_values, $op) {
    if (isset($current_index_fields)) {
      $index_data_results = $current_index_fields;
    }

    if (isset($index_field_values["field_json_metadata"][0]["index_fields"]["field_collection"])) {
      $index_field_group = $index_field_values["field_json_metadata"][0]["index_fields"]["field_collection"]["group"];

      $data_pre = [
        [
          "name" => $index_field_group["name"],
          "length" => $index_field_group["length"],
        ],
      ];

    }

    if (isset($data_pre) && $op === "add") {
      $index_data_results = isset($current_index_fields) ? array_merge($current_index_fields, $data_pre) : $data_pre;
    }

    return $index_data_results;
  }

  /**
   * Set the elements associated with adding a new field.
   */
  public static function setAddIndexFormState($add_new_index_field, $element) {
    if ($add_new_index_field) {

      $element['index_fields']['field_collection'] = $add_new_index_field;
      $element['index_fields']['field_collection']['#access'] = TRUE;
      $element['index_fields']['add_row_button']['#access'] = FALSE;
      $element['identifier']['#required'] = FALSE;
      $element['title']['#required'] = FALSE;
    }
    return $element;
  }

  /**
   * Create edit and update fields where needed.
   */
  public static function createDictionaryIndexFieldOptions($op_index, $data_results, $index_fields_being_modified, $element) {
    $current_index_fields = $element['current_index_fields'];
    // Creating ajax buttons/fields to be placed in correct location later.
    foreach ($data_results as $key => $data) {
      if (self::checkEditingField($key, $op_index, $index_fields_being_modified)) {
        $element['edit_fields'][$key] = FieldEditCreation::editFields($key, $current_index_fields, $index_fields_being_modified);
      }
      else {
        $element['edit_buttons'][$key]['edit_button'] = IndexFieldButtons::editButtons($key);
      }
    }
    $element['add_row_button'] = IndexFieldButtons::addButton();

    return $element;
  }

  /**
   * Return true if field is being edited.
   */
  public static function checkEditingField($key, $op_index, $index_fields_being_modified) {
    $action_list = FieldOperations::editActions();
    if (isset($op_index[0]) && in_array($op_index[0], $action_list) && array_key_exists($key, $fields_being_modified)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
}