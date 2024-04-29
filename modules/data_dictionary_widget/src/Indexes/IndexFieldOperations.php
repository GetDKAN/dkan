<?php

namespace Drupal\data_dictionary_widget\Indexes;

/**
 * Various operations for the Data Dictionary Widget.
 */
class IndexFieldOperations {
  /**
   * Setting ajax elements.
   */
  public static function setIndexFieldsAjaxElements(array $dictionaryIndexFields) {
    foreach ($dictionaryIndexFields['data']['#rows'] as $row => $data) {
      $edit_index_button = $dictionaryIndexFields['edit_index_buttons']['index_field_key_' . $row] ?? NULL;
      $edit_index_fields = $dictionaryIndexFields['edit_index_fields']['index_field_key_' . $row] ?? NULL;
      // Setting the ajax fields if they exsist.
      if ($edit_index_button) {
        $dictionaryIndexFields['data']['#rows'][$row] = array_merge($data, $edit_index_button);
        unset($dictionaryIndexFields['edit_index_buttons']['index_field_key_' . $row]);
      }
      elseif ($edit_index_fields) {
        unset($dictionaryIndexFields['data']['#rows']['index_field_key_' . $row]);
        $dictionaryIndexFields['data']['#rows'][$row]['field_collection'] = $edit_index_fields;
        // Remove the buttons so they don't show up twice.
        unset($dictionaryIndexFields['edit_index_fields']['index_field_key_' . $row]);
        ksort($dictionaryIndexFields['data']['#rows']);
      }

    }

    return $dictionaryIndexFields;
  }

  /**
   * Setting ajax elements.
   */
  public static function setIndexAjaxElements(array $dictionaryIndexes) {
    foreach ($dictionaryIndexes['data']['#rows'] as $row => $data) {
      $edit_index_button = $dictionaryIndexFields['edit_index_buttons']['index_field_key_' . $row] ?? NULL;
      $edit_index_fields = $dictionaryIndexFields['edit_index_fields']['index_field_key_' . $row] ?? NULL;
      // Setting the ajax fields if they exsist.
      if ($edit_index_button) {
        $dictionaryIndexFields['data']['#rows'][$row] = array_merge($data, $edit_index_button);
        unset($dictionaryIndexFields['edit_index_buttons']['index_field_key_' . $row]);
      }
      elseif ($edit_index_fields) {
        unset($dictionaryIndexFields['data']['#rows']['index_field_key_' . $row]);
        $dictionaryIndexFields['data']['#rows'][$row]['field_collection'] = $edit_index_fields;
        // Remove the buttons so they don't show up twice.
        unset($dictionaryIndexFields['edit_index_fields']['index_field_key_' . $row]);
        ksort($dictionaryIndexFields['data']['#rows']);
      }

    }

    return $dictionaryIndexes;
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

      $data_index_fields_pre = [
        [
          "name" => $index_field_group["name"],
          "length" => (int)$index_field_group["length"],
        ],
      ];
    }

    if (isset($data_index_fields_pre) && $op === "add_index_field") {
      $index_data_results = isset($current_index_fields) ? array_merge($current_index_fields, $data_index_fields_pre) : $data_index_fields_pre;
    }

    return $index_data_results;
  }

  /**
   * Return acceptable edit actions.
   */
  public static function editIndexActions() {
    return [
      'format',
      'edit',
      'update',
      'abort',
      'delete',
    ];
  }

  /**
   * Set the elements associated with adding a new field.
   */
  public static function setAddIndexFieldFormState($add_new_index_field, $element) {
    if ($add_new_index_field) {

      $element['indexes']['index_fields']['field_collection'] = $add_new_index_field;
      $element['indexes']['index_fields']['field_collection']['#access'] = TRUE;
      $element['indexes']['index_fields']['add_row_button']['#access'] = FALSE;
      $element['identifier']['#required'] = FALSE;
      $element['title']['#required'] = FALSE;
    }
    return $element;
  }

  /**
   * Set the elements associated with adding a new field.
   */
  public static function setAddIndexFormState($add_new_index, $element) {
    if ($add_new_index) {

      $element['indexes']['field_collection'] = $add_new_index;
      $element['indexes']['field_collection']['#access'] = TRUE;
      $element['indexes']['add_row_button']['#access'] = FALSE;
      $element['identifier']['#required'] = FALSE;
      $element['title']['#required'] = FALSE;
    }
    return $element;
  }

  /**
   * Create edit and update fields where needed.
   */
  public static function createDictionaryIndexFieldOptions($op_index, $index_data_results, $index_fields_being_modified, $element) {
    $current_index_fields = $element['current_index_fields'] ?? NULL;
    // Creating ajax buttons/fields to be placed in correct location later.
    foreach ($index_data_results as $indexKey => $data) {
      if (self::checkIndexEditingField('index_field_key_' . $indexKey, $op_index, $index_fields_being_modified)) {
        $element['edit_index_fields']['index_field_key_' . $indexKey] = IndexFieldEditCreation::editIndexFields('index_field_key_' . $indexKey, $current_index_fields, $index_fields_being_modified);
      }
      else {
        $element['edit_index_buttons']['index_field_key_' . $indexKey]['edit_index_button'] = IndexFieldButtons::editIndexButtons('index_field_key_' . $indexKey);
      }
    }
    $element['add_row_button'] = IndexFieldButtons::addIndexFieldButton();

    return $element;
  }

  /**
   * Create edit and update fields where needed.
   */
  public static function createDictionaryIndexOptions($op_index, $index_data_results, $index_fields_being_modified, $element) {
    $current_indexes = $element['current_index'];
    // Creating ajax buttons/fields to be placed in correct location later.
    foreach ($index_data_results as $indexKey => $data) {
      if (self::checkIndexEditingField('index_field_key_' . $indexKey, $op_index, $index_fields_being_modified)) {
        $element['edit_index_fields']['index_field_key_' . $indexKey] = IndexFieldEditCreation::editIndexFields('index_field_key_' . $indexKey, $current_indexes, $index_fields_being_modified);
      }
      else {
        $element['edit_index_buttons']['index_field_key_' . $indexKey]['edit_index_button'] = IndexFieldButtons::editIndexButtons('index_field_key_' . $indexKey);
      }
    }
    $element['add_row_button'] = IndexFieldButtons::addIndexButton();

    return $element;
  }

  /**
   * Return true if field is being edited.
   */
  public static function checkIndexEditingField($indexKey, $op_index, $index_fields_being_modified) {
    $action_list = IndexFieldOperations::editIndexActions();
    $indexKeyExplode = explode("_", $indexKey); 
    if (isset($op_index[0]) && in_array($op_index[0], $action_list) && array_key_exists($indexKeyExplode[3], $index_fields_being_modified)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
}