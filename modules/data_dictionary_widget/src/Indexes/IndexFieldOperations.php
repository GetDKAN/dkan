<?php

namespace Drupal\data_dictionary_widget\Indexes;

/**
 * Various operations for structuring indexes and fields before rendering.
 */
class IndexFieldOperations {

  /**
   * Setting ajax elements when editing newly added index fields.
   */
  public static function setIndexFieldsAjaxElementsOnAdd(array $indexFields) {
    if ($indexFields["data"]) {
      foreach ($indexFields['data']['#rows'] as $row => $data) {
        $edit_index_button = $indexFields['edit_index_buttons']['index_field_key_' . $row] ?? NULL;
        $edit_index_fields = $indexFields['edit_index_fields']['index_field_key_' . $row] ?? NULL;
        // Setting the ajax fields if they exist.
        if ($edit_index_button) {
          $indexFields['data']['#rows'][$row] = array_merge($data, $edit_index_button);
          unset($indexFields['edit_index_buttons']['index_field_key_' . $row]);
        }
        elseif ($edit_index_fields) {
          unset($indexFields['data']['#rows']['index_field_key_' . $row]);
          $indexFields['data']['#rows'][$row]['field_collection'] = $edit_index_fields;
          // Remove the buttons so they don't show up twice.
          unset($indexFields['edit_index_fields']['index_field_key_' . $row]);
          ksort($indexFields['data']['#rows']);
        }
      }
    }

    return $indexFields;
  }

  /**
   * Setting ajax elements when editing existing index fields.
   */
  public static function setIndexFieldsAjaxElements(array $indexFields) {
    if ($indexFields["data"]) {
      foreach ($indexFields['data']['#rows'] as $row => $data) {
        $edit_index_fields_button = $indexFields['fields']['edit_index_fields_buttons']['index_field_key_' . $row] ?? NULL;
        $edit_index_fields = $indexFields['fields']['edit_index_fields']['index_field_key_' . $row] ?? NULL;
        // Setting the ajax fields if they exist.
        if ($edit_index_fields_button) {
          $indexFields["data"]["#rows"][$row] = array_merge($data, $edit_index_fields_button);
          unset($indexFields["fields"]["edit_index_fields_buttons"]['index_field_key_' . $row]);
        }
        elseif ($edit_index_fields) {
          unset($indexFields['data']['#rows']['index_field_key_' . $row]);
          $indexFields['data']['#rows'][$row]['field_collection'] = $edit_index_fields;
          // Remove the buttons so they don't show up twice.
          unset($indexFields['edit_index_fields']['index_field_key_' . $row]);
          ksort($indexFields['data']['#rows']);
        }
      }
    }

    return $indexFields;
  }

  /**
   * Setting index ajax elements.
   */
  public static function setIndexAjaxElements(array $indexes) {
    foreach ($indexes['data']['#rows'] as $row => $data) {
      $edit_index_button = $indexes['edit_index_buttons']['index_key_' . $row] ?? NULL;
      $edit_index = $indexes['edit_index']['index_key_' . $row] ?? NULL;
      // Setting the ajax fields if they exist.
      if ($edit_index_button) {
        $indexes['data']['#rows'][$row] = array_merge($data, $edit_index_button);
        unset($indexes['edit_index_buttons']['index_key_' . $row]);
      }
      elseif ($edit_index) {
        unset($indexes['data']['#rows']['index_key_' . $row]);
        $indexes['data']['#rows'][$row]['field_collection'] = $edit_index;
        // Remove the buttons so they don't show up twice.
        unset($indexes['edit_index']['index_key_' . $row]);
        ksort($indexes['data']['#rows']);
      }

    }

    return $indexes;
  }

  /**
   * Prepare index field data results.
   */
  public static function processIndexFieldsDataResults($index_data_results, $current_index_fields, $index_field_values, $op) {
    if (isset($current_index_fields)) {
      $index_data_results = $current_index_fields;
    }

    if (isset($index_field_values["field_json_metadata"][0]["indexes"]["fields"]["field_collection"])) {
      $index_field_group = $index_field_values["field_json_metadata"][0]["indexes"]["fields"]["field_collection"]["group"];

      $data_index_fields_pre = [
        [
          "name" => $index_field_group['index']['fields']["name"],
          "length" => (int) $index_field_group['index']['fields']["length"],
        ],
      ];
    }

    if (isset($data_index_fields_pre) && $op === "add_index_field") {
      $index_data_results = isset($current_index_fields) ? array_merge($current_index_fields, $data_index_fields_pre) : $data_index_fields_pre;
    }

    return $index_data_results;
  }

  /**
   * Prepare index data results.
   */
  public static function processIndexDataResults($index_results, $current_indexes, $index_values, $index_fields_data_results, $op) {
    if (isset($current_indexes)) {
      $index_results = $current_indexes;
    }

    if (isset($index_values["field_json_metadata"][0]["indexes"]["field_collection"])) {
      $index_group = $index_values["field_json_metadata"][0]["indexes"]["field_collection"]["group"];

      $data_index_pre = [
        [
          "description" => $index_group['index']["description"],
          "type" => $index_group['index']["type"],
          "fields" => $index_fields_data_results,
        ],
      ];
    }

    if (isset($data_index_pre) && $op === "add_index") {
      $index_results = isset($current_indexes) ? array_merge($current_indexes, $data_index_pre) : $data_index_pre;
    }

    return $index_results;
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
   * Set the elements associated with editing an index.
   */
  public static function editIndexFormState($edit_index, $element) {
    if ($edit_index) {
      unset($element["indexes"]["edit_index_buttons"]);
    }

    return $element;
  }

  /**
   * Set the elements associated with adding a new index field.
   */
  public static function setAddIndexFieldFormState($add_new_index_field, $element) {
    if ($add_new_index_field) {
      $element['indexes']['fields']['#access'] = FALSE;
      $element['indexes']['fields']['field_collection'] = $add_new_index_field;
      $element['indexes']['fields']['field_collection']['#access'] = TRUE;
      $element['indexes']['fields']['add_row_button']['#access'] = FALSE;
      $element['identifier']['#required'] = FALSE;
      $element['title']['#required'] = FALSE;
    }

    return $element;
  }

  /**
   * Set the elements associated with adding a new index.
   */
  public static function setAddIndexFormState($add_new_index, $element) {
    if ($add_new_index) {
      unset($element["indexes"]["edit_index_buttons"]);
      $element['indexes']['field_collection'] = $add_new_index;
      $element['indexes']['field_collection']['#access'] = TRUE;
      $element['indexes']['add_row_button']['#access'] = FALSE;
      $element['identifier']['#required'] = FALSE;
      $element['title']['#required'] = FALSE;
    }

    return $element;
  }

  /**
   * Create edit and update fields for index fields.
   */
  public static function createIndexFieldOptions($op_index, $index_data_results, $index_fields_being_modified, $element) {
    $current_index_fields = $index_data_results ?? NULL;
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
   * Create edit and update fields for indexes.
   */
  public static function createIndexOptions($op_index, $index_data_results, $index_being_modified, $index_field_being_modified, $element, $form_state) {
    $current_indexes = $element['current_index'];

    // Creating ajax buttons/fields to be placed in correct location later.
    foreach ($index_data_results as $indexKey => $data) {
      if (self::checkIndexEditing('index_key_' . $indexKey, $op_index, $index_being_modified)) {
        $element['edit_index']['index_key_' . $indexKey] = IndexFieldEditCreation::editIndex('index_key_' . $indexKey, $current_indexes, $index_being_modified, $form_state);
      }
      else {
        $element['edit_index_buttons']['index_key_' . $indexKey]['edit_index_button'] = IndexFieldButtons::editIndexButtons('index_key_' . $indexKey);
      }
    }
    $element['add_row_button'] = IndexFieldButtons::addIndexButton();

    return $element;
  }

  /**
   * Return true if index field is being edited.
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

  /**
   * Return true if index is being edited.
   */
  public static function checkIndexEditing($indexKey, $op_index, $index_being_modified) {
    if (isset($op_index)) {
      $op_index_string = implode('_', $op_index);
      if (str_contains($op_index_string, 'edit_index_key')) {
        $action_list = IndexFieldOperations::editIndexActions();
        $indexKeyExplode = explode("_", $indexKey);
        if (isset($op_index[0]) && in_array($op_index[0], $action_list) && array_key_exists($indexKeyExplode[2], $index_being_modified)) {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

}
