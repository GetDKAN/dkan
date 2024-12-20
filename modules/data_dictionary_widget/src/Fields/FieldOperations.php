<?php

namespace Drupal\data_dictionary_widget\Fields;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Various operations for the Data Dictionary Widget.
 */
class FieldOperations {

  /**
   * Get a list of data dictionaries.
   */
  public static function getDataDictionaries() {
    $existing_identifiers = [];
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $node_storage
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'data')
      ->condition('field_data_type', 'data-dictionary', '=');
    $nodes_ids = $query->execute();
    $nodes = $node_storage->loadMultiple($nodes_ids);
    foreach ($nodes as $node) {
      $existing_identifiers[] = [
        'nid' => $node->id(),
        'identifier' => $node->uuid(),
      ];
    }

    return $existing_identifiers;
  }

  /**
   * Setting ajax elements.
   */
  public static function setAjaxElements(array $dictionaryFields) {
    foreach ($dictionaryFields['data']['#rows'] as $row => $data) {
      $edit_button = $dictionaryFields['edit_buttons'][$row] ?? NULL;
      $edit_fields = $dictionaryFields['edit_fields'][$row] ?? NULL;
      // Setting the ajax fields if they exsist.
      if ($edit_button) {
        $dictionaryFields['data']['#rows'][$row] = array_merge($data, $edit_button);
        unset($dictionaryFields['edit_buttons'][$row]);
      }
      elseif ($edit_fields) {
        unset($dictionaryFields['data']['#rows'][$row]);
        $dictionaryFields['data']['#rows'][$row]['field_collection'] = $edit_fields;
        // Remove the buttons so they don't show up twice.
        unset($dictionaryFields['edit_fields'][$row]);
        ksort($dictionaryFields['data']['#rows']);
      }

    }

    return $dictionaryFields;
  }

  /**
   * Function to generate the description for the "Format" field.
   *
   * @param string $dataType
   *   Field data type.
   * @param string $property
   *   Property of array.
   *
   * @return string|array
   *   Description information or options list.
   *
   * @throws \InvalidArgumentException
   */
  public static function generateFormats($dataType, $property) {
    $description = "<p>The format of the data in this field. Supported formats depend on the specified field type:</p>";

    $info = match ($dataType) {
      'string' => FieldValues::returnStringInfo($property),
      'date' => FieldValues::returnDateInfo($property),
      'datetime' => FieldValues::returnDateTimeInfo($property),
      'integer' => FieldValues::returnIntegerInfo($property),
      'number' => FieldValues::returnNumberInfo($property),
      'year' => FieldValues::returnYearInfo($property),
      'boolean' => FieldValues::returnBooleanInfo($property),
      default => throw new \InvalidArgumentException("Unexpected data type: $dataType"),
    };

    return ($property === "description") ? ($description . $info) : $info;
  }

  /**
   * Cleaning the data up.
   */
  public static function processDataResults($data_results, $current_fields, $field_values, $op) {
    if (isset($current_fields)) {
      $data_results = $current_fields;
    }

    if (isset($field_values["field_json_metadata"][0]["dictionary_fields"]["field_collection"])) {
      $field_group = $field_values["field_json_metadata"][0]["dictionary_fields"]["field_collection"]["group"];
      $field_format = $field_group["format"] == 'other' ? $field_group["format_other"] : $field_group["format"];

      $data_pre = [
        [
          "name" => $field_group["name"],
          "title" => $field_group["title"],
          "type" => $field_group["type"],
          "format" => $field_format,
          "description" => $field_group["description"],
        ],
      ];

    }

    if (isset($data_pre) && $op === "add") {
      $data_results = isset($current_fields) ? array_merge($current_fields, $data_pre) : $data_pre;
    }

    return $data_results;
  }

  /**
   * Return acceptable edit actions.
   */
  public static function editActions() {
    return [
      'format',
      'edit',
      'update',
      'abort',
      'delete',
    ];
  }

  /**
   * Set Field Type Options.
   */
  public static function setTypeOptions() {
    return [
      'string' => new TranslatableMarkup('String'),
      'date' => new TranslatableMarkup('Date'),
      'datetime' => new TranslatableMarkup('Datetime'),
      'integer' => new TranslatableMarkup('Integer'),
      'number' => new TranslatableMarkup('Number'),
      'year' => new TranslatableMarkup('Year'),
      'boolean' => new TranslatableMarkup('Boolean'),
    ];
  }

  /**
   * Return true if field is being edited.
   */
  public static function checkEditingField($key, $op_index, $fields_being_modified) {
    $action_list = FieldOperations::editActions();
    if (isset($op_index[0]) && in_array($op_index[0], $action_list) && array_key_exists($key, $fields_being_modified)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Return true if field collection is present.
   */
  public static function checkFieldCollection($data_pre, $op) {
    if (isset($data_pre) && $op === "add") {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Set the elements associated with adding a new field.
   */
  public static function setAddFormState($add_new_field, $element) {
    if ($add_new_field) {
      $element['dictionary_fields']['field_collection'] = $add_new_field;
      $element['dictionary_fields']['field_collection']['#access'] = TRUE;
      $element['dictionary_fields']['add_row_button']['#access'] = FALSE;
      $element['identifier']['#required'] = FALSE;
      $element['title']['#required'] = FALSE;
    }
    return $element;
  }

  /**
   * Create edit and update fields where needed.
   */
  public static function createDictionaryFieldOptions($op_index, $data_results, $fields_being_modified, $element) {
    $current_fields = $element['current_fields'];
    // Creating ajax buttons/fields to be placed in correct location later.
    foreach ($data_results as $key => $data) {
      if (self::checkEditingField($key, $op_index, $fields_being_modified)) {
        $element['edit_fields'][$key] = FieldEditCreation::editFields($key, $current_fields, $fields_being_modified);
      }
      else {
        $element['edit_buttons'][$key]['edit_button'] = FieldButtons::editButtons($key);
      }
    }
    $element['add_row_button'] = FieldButtons::addButton();

    return $element;
  }

  /**
   * Restore the data dictionary fields after a form_state rebuild.
   */
  public static function restoreDictionaryFieldsOnRebuild(&$form, FormStateInterface $form_state) {
    $edit_fields = isset($form_state->getValues()["field_json_metadata"][0]["dictionary_fields"]["edit_fields"]);

    // Resets the date format options when validation fails.
    self::resetDateFormatOptions($form);

    if ($edit_fields) {
      $dictionary_fields = [
        'name',
        'title',
        'type',
        'format',
        'format_other',
        'description',
      ];
      $edit_fields_array = $form_state->getValues()["field_json_metadata"][0]["dictionary_fields"]["edit_fields"];
      $index = key($edit_fields_array);

      // Resets all format options when validation fails.
      self::resetAllFormatOptions($form, $form_state, $index);

      foreach ($dictionary_fields as $field_key) {
        // Resets field values when validation fails.
        self::resetFieldValues($form, $form_state, $index, $field_key);
      }
    }

    return $form["field_json_metadata"]["widget"][0]["dictionary_fields"];
  }

  /**
   * Resets format options and descriptions for a specific dictionary field.
   *
   * This function updates the format options and descriptions for a dictionary
   * field based on the type specified in the form state.
   *
   * @param array &$form
   *   The form array to be modified.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state object.
   * @param int $index
   *   The index of the dictionary field to reset format options for.
   */
  public static function resetAllFormatOptions(array &$form, FormStateInterface $form_state, $index) {
    $type = $form_state->getValue([
      "field_json_metadata",
      0,
      "dictionary_fields",
      "data",
      $index,
      "field_collection",
      "type",
    ]);

    if ($type) {
      $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["edit_fields"][$index]["format"]["#options"] = FieldOperations::generateFormats($type, "options");
      $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["edit_fields"][$index]["format"]["#description"] = FieldOperations::generateFormats($type, "description");
    }
  }

  /**
   * Resets date format options and descriptions for the field collection.
   *
   * This function checks if the field collection associated with dictionary
   * fields has a type of "date". If it does, it updates the format options
   * and descriptions for the date field within the field collection based
   * on the information returned by FieldValues::returnDateInfo().
   *
   * This function is necessary for when validation triggers an
   * error for an empty format_other field.
   *
   * @param array &$form
   *   The form array to be modified.
   */
  public static function resetDateFormatOptions(array &$form) {
    $data_type = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["field_collection"]["group"]["type"]["#value"] ?? NULL;

    if (!$data_type || ($data_type !== "date" && $data_type !== "datetime")) {
      return;
    }

    $options_method = ($data_type === "date") ? 'returnDateInfo' : 'returnDateTimeInfo';
    $options = FieldValues::$options_method('options');
    $description = FieldValues::$options_method('description');

    if ($options && $description) {
      $format_field =& $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["field_collection"]["group"]["format"];
      $format_field["#options"] = $options;
      $format_field["#description"] = $description;
    }
  }

  /**
   * Resets field values based on form state values.
   *
   * This function retrieves the current field value and the new value from the
   * form state based on the provided index and field key. It then updates the
   * form array with the new value if it's not empty and different from the
   * current value. If the new value is empty, it sets the field value to empty.
   *
   * @param array &$form
   *   The form array to be modified. The field value will be updated
   *   directly in this array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state object.
   * @param int $index
   *   The index of the field within the form array.
   * @param string $field_key
   *   The key of the field within the form array.
   */
  public static function resetFieldValues(array &$form, FormStateInterface $form_state, $index, $field_key) {
    $field_value = $form_state->getValue([
      'field_json_metadata',
      0,
      'dictionary_fields',
      'edit_fields',
      $index,
      $field_key,
    ]);

    $new_value = $form_state->getValue([
      "field_json_metadata",
      0,
      "dictionary_fields",
      "data",
      $index,
      "field_collection",
      $field_key,
    ]);

    if (!empty($field_value) && $new_value === "") {
      $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["edit_fields"][$index][$field_key]["#value"] = "";
    }
    elseif (!empty($new_value) && $new_value !== $field_value) {
      $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["edit_fields"][$index][$field_key]["#value"] = $new_value;
    }
  }

}
