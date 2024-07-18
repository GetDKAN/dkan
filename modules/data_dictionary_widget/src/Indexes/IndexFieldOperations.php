<?php

namespace Drupal\data_dictionary_widget\Indexes;

/**
 * Various operations for the Data Dictionary Widget.
 */
class IndexFieldOperations {
  // /**
  //  * Setting ajax elements.
  //  */
  // public static function setIndexFieldsAjaxElements(array $dictionaryIndexFields) {
  //   if ($dictionaryIndexFields["index_fields"]) {
  //     foreach ($dictionaryIndexFields['index_fields']['#rows'] as $row => $data) {
  //       $edit_index_button = $dictionaryIndexFields['edit_index_buttons']['index_field_key_' . $row] ?? NULL;
  //       $edit_index_fields = $dictionaryIndexFields['edit_index_fields']['index_field_key_' . $row] ?? NULL;
  //       // Setting the ajax fields if they exsist.
  //       if ($edit_index_button) {
  //         $dictionaryIndexFields['index_fields']['#rows'][$row] = array_merge($data, $edit_index_button);
  //         unset($dictionaryIndexFields['edit_index_buttons']['index_field_key_' . $row]);
  //       }
  //       elseif ($edit_index_fields) {
  //         unset($dictionaryIndexFields['index_fields']['#rows']['index_field_key_' . $row]);
  //         $dictionaryIndexFields['index_fields']['#rows'][$row]['field_collection'] = $edit_index_fields;
  //         // Remove the buttons so they don't show up twice.
  //         unset($dictionaryIndexFields['edit_index_fields']['index_field_key_' . $row]);
  //         ksort($dictionaryIndexFields['index_fields']['#rows']);
  //       }
  //     }
  //   }

  //   return $dictionaryIndexFields;
  // }

  /**
   * Setting ajax elements.
   */
  public static function setIndexFieldsAjaxElements(array $dictionaryIndexFields) {
    if ($dictionaryIndexFields["data"]) {
      foreach ($dictionaryIndexFields['data']['#rows'] as $row => $data) {
        $edit_index_button = $dictionaryIndexFields["fields"]["edit_index_buttons"]['index_field_key_' . $row] ?? NULL;
        //$edit_index_button = $dictionaryIndexFields['edit_index_buttons']['index_field_key_' . $row] ?? NULL;
        $edit_index_fields = $dictionaryIndexFields['edit_index_fields']['index_field_key_' . $row] ?? NULL;
        // Setting the ajax fields if they exsist.
        if ($edit_index_button) {
          $dictionaryIndexFields["data"]["#rows"][0]["fields"][0]["name"] = "blah";
          $dictionaryIndexFields["fields"]["data"]["#rows"][0]["name"] = "blah";
          $dictionaryIndexFields["current_index"][0]["fields"][0]["name"]["#value"] = "blah";
          $dictionaryIndexFields["field_collection"]["group"]["index"]["fields"]["data"]["#rows"][0]["name"] = "blah";
          //$dictionaryIndexFields['data']['#rows'][$row] = array_merge($data, $edit_index_button);
          $dictionaryIndexFields["data"]["#rows"][0]["fields"][0] = array_merge($data["fields"][0], $edit_index_button);
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
    }

    return $dictionaryIndexFields;
  }

    /**
   * Setting ajax elements.
   */
  public static function setIndexFieldsEditAjaxElements(array $dictionaryIndexFields) {
    if ($dictionaryIndexFields) {
      //$dictionaryIndexFields["#rows"][0]["fields"][0]
      foreach ($dictionaryIndexFields['data']['#rows'] as $row => $data) {
        $edit_index_button = $dictionaryIndexFields["edit_index"]["index_key_0"]["fields"]["edit_index_field_buttons"]["index_field_key_0"]["edit_index_field_button"] ?? NULL;
        $edit_index_fields = $dictionaryIndexFields["#rows"][0]["fields"][0] ?? NULL;
        // Setting the ajax fields if they exsist.
        if ($edit_index_button) {
          $dictionaryIndexFields["data"]["#rows"][0]["fields"][0] = array_merge($data["fields"][0], $edit_index_button);
          unset($dictionaryIndexFields['edit_index_field_buttons']['index_field_key_' . $row]);
        }
        if ($edit_index_fields) {
          unset($dictionaryIndexFields['data']['#rows']['index_field_key_' . $row]);
          $dictionaryIndexFields['#rows'][$row]['fields'] = $edit_index_fields;
          $dictionaryIndexFields["#rows"][0]["field_collection"]["fields"]["index_fields"]["#rows"][0];
          // Remove the buttons so they don't show up twice.
          unset($dictionaryIndexFields['edit_index_fields']['index_field_key_' . $row]);
          //ksort($dictionaryIndexFields['data']['#rows']);
        }
      }
    }

    return $dictionaryIndexFields;
  }

  

  /**
   * Setting ajax elements.
   */
  public static function setIndexAjaxElements(array $dictionaryIndexes) {
    foreach ($dictionaryIndexes['data']['#rows'] as $row => $data) {
      $edit_index_button = $dictionaryIndexes['edit_index_buttons']['index_key_' . $row] ?? NULL;
      $edit_index = $dictionaryIndexes['edit_index']['index_key_' . $row] ?? NULL;
      // Setting the ajax fields if they exsist.
      if ($edit_index_button) {
        $dictionaryIndexes['data']['#rows'][$row] = array_merge($data, $edit_index_button);
        unset($dictionaryIndexes['edit_index_buttons']['index_key_' . $row]);
      }
      elseif ($edit_index) {
        unset($dictionaryIndexes['data']['#rows']['index_key_' . $row]);
        $dictionaryIndexes['data']['#rows'][$row]['field_collection'] = $edit_index;
        // Remove the buttons so they don't show up twice.
        unset($dictionaryIndexes['edit_index']['index_key_' . $row]);
        ksort($dictionaryIndexes['data']['#rows']);
      }

    }

    return $dictionaryIndexes;
  }

  /**
   * Cleaning the data up.
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
          "length" => (int)$index_field_group['index']['fields']["length"],
        ],
      ];
    }

    if (isset($data_index_fields_pre) && $op === "add_index_field") {
      $index_data_results = isset($current_index_fields) ? array_merge($current_index_fields, $data_index_fields_pre) : $data_index_fields_pre;
    }

    if (isset($index_data_results) && $op === "add_new_index_field") {
      //$index_data_results = isset($index_data_results) ? array_merge($index_data_results, $data_index_fields_pre) : $data_index_fields_pre;
      $index_data_results = isset($index_data_results) ? $index_data_results : $data_index_fields_pre;

    }

    return $index_data_results;
  }

  /**
   * Cleaning the data up.
   */
  public static function processIndexDataResults($index_results, $current_indexes, $index_values, $index_fields_data_results, $op) {
    // if($op === 'edit_0') {
    //   $op = 'add_index';
    // };
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
   * Set the elements associated with adding a new field.
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

  // /**
  //  * Create edit and update fields where needed.
  //  */
  // public static function createDictionaryIndexFieldOptions($op_index, $index_fields_data_results, $index_fields_being_modified, $element) {
  //   $current_index_fields = $index_fields_data_results ?? NULL;
  //   // Creating ajax buttons/fields to be placed in correct location later.
  //   foreach ($index_fields_data_results as $indexKey => $data) {
  //     if (self::checkIndexEditingField('index_field_key_' . $indexKey, $op_index, $index_fields_being_modified)) {
  //       $element['edit_index_fields']['index_field_key_' . $indexKey] = IndexFieldEditCreation::editIndexFields('index_field_key_' . $indexKey, $current_index_fields, $index_fields_being_modified);
  //     }
  //     else {
  //       $element['edit_index_buttons']['index_field_key_' . $indexKey]['edit_index_button'] = IndexFieldButtons::editIndexFieldButtons('index_field_key_' . $indexKey);
  //     }
  //   }
  //   $element['add_row_button'] = IndexFieldButtons::addIndexFieldButton();

  //   return $element;
  // }

  /**
   * Create edit and update fields where needed.
   */
  public static function createDictionaryIndexFieldOptions($op_index, $index_data_results, $index_fields_being_modified, $element) {
    $current_index_fields = $index_data_results ?? NULL;
    // Creating ajax buttons/fields to be placed in correct location later.
    foreach ($index_data_results as $indexKey => $data) {
      //if (self::checkIndexEditingField('index_field_key_' . $indexKey, $op_index, $index_fields_being_modified)) {

      if ($index_data_results) {
        $element['edit_index_fields']['index_field_key_' . $indexKey] = IndexFieldEditCreation::editIndexFields('index_field_key_' . $indexKey, $current_index_fields, $index_fields_being_modified);
      }
      else {
        $element['edit_index_buttons']['index_field_key_' . $indexKey]['edit_index_field_button'] = IndexFieldButtons::editIndexButtons('index_field_key_' . $indexKey);
      }
    }
    $element['add_row_button'] = IndexFieldButtons::addIndexFieldButton();

    return $element;
  }

    /**
   * Create edit and update fields where needed.
   */
  public static function createDictionaryIndexFieldEditOptions($op_index, $index_fields_data_results, $index_fields_being_modified, $element) {
    $current_index_fields = $index_fields_data_results ?? NULL;
    // Creating ajax buttons/fields to be placed in correct location later.
    foreach ($index_fields_data_results as $indexKey => $data) {
      if (self::checkIndexEditingField('index_field_key_' . $indexKey, $op_index, $index_fields_being_modified)) {
        $element['edit_index_fields']['index_field_key_' . $indexKey] = IndexFieldEditCreation::editIndexFields('index_field_key_' . $indexKey, $current_index_fields, $index_fields_being_modified);
      }
      else {
        $element['edit_index_field_buttons']['index_field_key_' . $indexKey]['edit_index_field_button'] = IndexFieldButtons::editIndexFieldButtons('index_field_key_' . $indexKey);
      }
    }
    $element['add_row_button'] = IndexFieldButtons::addIndexFieldButton();

    return $element;
  }

  /**
   * Create edit and update fields where needed.
   */
  public static function createDictionaryIndexOptions($op_index, $index_data_results, $index_being_modified, $element) {
    $current_indexes = $index_data_results ?? NULL;
    // Creating ajax buttons/fields to be placed in correct location later.
    foreach ($index_data_results as $indexKey => $data) {
      if ($index_being_modified) {
        if (self::checkIndexEditing('index_key_' . $indexKey, $op_index, $index_being_modified)) {
          $element['edit_index']['index_key_' . $indexKey] = IndexFieldEditCreation::editIndex('index_key_' . $indexKey, $current_indexes, $index_being_modified, $element);
        }
      }
      else {
        $element['edit_index_buttons']['index_key_' . $indexKey]['edit_index_button'] = IndexFieldButtons::editIndexButtons('index_key_' . $indexKey);
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

  /**
   * Return true if field is being edited.
   */
  public static function checkIndexEditing($indexKey, $op_index, $index_being_modified) {
    $action_list = IndexFieldOperations::editIndexActions();
    $indexKeyExplode = explode("_", $indexKey); 
    if (isset($op_index[0]) && in_array($op_index[0], $action_list) && array_key_exists($indexKeyExplode[2], $index_being_modified)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }
}