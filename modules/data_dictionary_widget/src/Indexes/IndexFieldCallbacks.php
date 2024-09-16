<?php

namespace Drupal\data_dictionary_widget\Indexes;

use Drupal\Core\Form\FormStateInterface;

/**
 * Various operations for the Index callbacks.
 */
class IndexFieldCallbacks {

  /**
   * Submit callback for the Index Add button.
   */
  public static function indexAddSubformCallback(array &$form, FormStateInterface $form_state) {
    // Get the button's trigger value.
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'];
    // Get the current fields data.
    $current_dictionary_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"] ?? [];
    $current_index = $form["field_json_metadata"]["widget"][0]['indexes']["data"]["#rows"] ?? [];
    $current_index_fields = $form["field_json_metadata"]["widget"][0]['indexes']["fields"]["data"]["#rows"] ?? [];

    if ($current_index_fields) {
      $form_state->set('current_index_fields', $current_index_fields);
    }

    // If cancelling index field.
    if ($op === 'cancel_index_field') {
      // Set the display to show the current index fields values.
      $form_state->set('cancel_index_field', TRUE);
      // Hide the field collection.
      $form_state->set('add_new_index_field', '');
    }

    // If adding new index field, this is triggered when you click the button to
    // 'Add'
    if ($op === 'add_new_index_field') {
      // @TODO not being used so maybe removable or update the comment.
      $form_state->set('add_index_field', '');
      // Get the form fields for adding new index fields.
      $add_index_fields = IndexFieldAddCreation::addIndexFields($current_index_fields);
      // Set the fields in the field collection.
      $form_state->set('add_new_index_field', $add_index_fields);
      // @TODO not being used so maybe removable or update the comment.
      $form_state->set('index_added', FALSE);
      // @TODO not being used so maybe removable or update the comment.
      $form_state->set('adding_new_index_fields', TRUE);
    }

    // If saving new index field.
    if ($op === 'add_index_field') {
      // @TODO not being used so maybe removable or update the comment.
      $form_state->set('add_new_index_field', '');
      // Get and save the entered values.
      $form_state->set('new_index_fields', $form_state->getUserInput());
      // @TODO not being used so maybe removable or update the comment.
      $form_state->set('add', TRUE);
      // Set the display to show the entered values.
      $form_state->set('cancel_index_field', FALSE);
      // @TODO not being used so maybe removable or update the comment.
      $form_state->set('adding_new_index_fields', FALSE);
    }

    // Let's retain the fields that are already stored on the form,
    // but aren't currently being modified.
    $form_state->set('current_dictionary_fields', $current_dictionary_fields);
    $form_state->set('current_index', $current_index);
    $form_state->set('current_index_fields', $current_index_fields);
    // Let's rebuild the form.
    $form_state->setRebuild();
  }

  /**
   * Submit callback for the Index Add button.
   */
  public static function indexAddCallback(array &$form, FormStateInterface $form_state) {
    // Get the button's trigger value.
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'];
    // Get the current fields data.
    $current_dictionary_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"];
    $current_index = $form["field_json_metadata"]["widget"][0]["indexes"]["data"]["#rows"];
    $current_index_fields = $form["field_json_metadata"]["widget"][0]['indexes']["fields"]["data"]["#rows"] ?? NULL;
    // Initialize the various field storage values.
    $form_state->set('add_new_index_field', '');
    $form_state->set('new_index_fields', '');
    $form_state->set('add_new_index', '');
    // @TODO not being used so maybe removable or update the comment.
    $form_state->set('adding_new_index_fields', FALSE);

    if ($current_index) {
      $form_state->set('current_index', $current_index);
    }

    // If cancelling index.
    if ($op === 'cancel_index') {
      // Set the display to show the current index values.
      $form_state->set('cancel_index', TRUE);
    }

    // If adding new index, this is triggered when you click the button to
    // 'Add index'
    if ($op === 'add_new_index') {
      // Get the form fields for adding new index.
      $add_new_index = IndexFieldAddCreation::addIndex();
      // Set the new_index values to empty.
      $form_state->set('new_index', '');
      // Set the fields in the field collection.
      $form_state->set('add_new_index', $add_new_index);
    }

    // If saving new index.
    if ($op === 'add_index') {
      // Empty the fields in the field collection.
      $form_state->set('add_new_index', '');
      // Get and save the entered values.
      $form_state->set('new_index', $form_state->getUserInput());
      // @TODO not being used so maybe removable or update the comment.
      $form_state->set('add', TRUE);
      // @TODO not being used so maybe removable or update the comment.
      $form_state->set('index_added', TRUE);
      // Set the display to show the entered values.
      $form_state->set('cancel_index', FALSE);
    }

    // Let's retain the fields that are already stored on the form,
    // but aren't currently being modified.
    $form_state->set('current_dictionary_fields', $current_dictionary_fields);
    $form_state->set('current_index', $current_index);
    $form_state->set('current_index_fields', $current_index_fields);
    // Let's rebuild the form.
    $form_state->setRebuild();
  }

  /**
   * Submit callback for the Index Field Edit button.
   */
  public static function indexEditSubformCallback(array &$form, FormStateInterface $form_state) {
    // Get the button's trigger value.
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'];
    // The location of the index field is stored in the operation key.
    // We split the key to get the index field location.
    $op_index = explode('_', $trigger['#op']);
    // Get the current fields data.
    $current_index_fields = $form["field_json_metadata"]["widget"][0]["indexes"]["fields"]["data"]["#rows"];
    $current_dictionary_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"];
    $currently_modifying_index_fields = $form_state->get('index_fields_being_modified') != NULL ? $form_state->get('index_fields_being_modified') : [];
    $currently_modifying = $form_state->get('dictionary_fields_being_modified') != NULL ? $form_state->get('dictionary_fields_being_modified') : [];

    // If the op (trigger) contains abort,
    // We're canceling the field we're currently modifying so unset it.
    if (str_contains($op, 'abort')) {
      unset($currently_modifying_index_fields[$op_index[4]]);
    }

    // If the op (trigger) contains delete,
    // We're deleting the field we're editing so...
    if (str_contains($op, 'delete')) {
      // Unset it from being currently modified.
      unset($currently_modifying_index_fields[$op_index[4]]);
      // Remove the respective field/data from the form.
      unset($current_index_fields[$op_index[4]]);
    }

    // If the op (trigger) contains update,
    // We're saving the field we're editing so...
    if (str_contains($op, 'update')) {
      // Get the entered data.
      $update_values = $form_state->getUserInput();
      // Unset the respective currently modifying field.
      unset($currently_modifying_index_fields[$op_index[4]]);
      // Unset the respective field/data from the form.
      unset($current_index_fields[$op_index[4]]);
      // Update the respective current field data with our new input data.
      $current_index_fields[$op_index[4]] = IndexFieldValues::updateIndexFieldValues($op_index[4], $update_values, $current_index_fields);
      // Sort the current index fields data.
      ksort($current_index_fields);
    }

    // If the op (trigger) contains edit
    // We're editing a specific field so...
    if (str_contains($op, 'edit_index_field')) {
      // Set the field we're modifying to that field.
      $currently_modifying_index_fields[$op_index[4]] = $current_index_fields[$op_index[4]];
    }

    // Let's retain the fields that are being modified.
    $form_state->set('dictionary_fields_being_modified', $currently_modifying);
    $form_state->set('index_fields_being_modified', $currently_modifying_index_fields);
    // Let's retain the fields that are already stored on the form,
    // but aren't currently being modified.
    $form_state->set('current_index_fields', $current_index_fields);
    $form_state->set('current_dictionary_fields', $current_dictionary_fields);
    // Let's rebuild the form.
    $form_state->setRebuild();
  }

  /**
   * Submit callback for the Index Edit button.
   */
  public static function indexEditCallback(array &$form, FormStateInterface $form_state) {
    // Get the button's trigger value.
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'];
    // The location of the index is stored in the operation key.
    // We split the key to get the index location.
    $op_index = explode('_', $trigger['#op']);
    // Get the current fields data.
    $current_index_fields = $form['field_json_metadata']['widget'][0]['indexes']['fields']['data']['#rows'] ?? [];
    $current_index = $form['field_json_metadata']['widget'][0]['indexes']['data']['#rows'] ?? [];
    $current_dictionary_fields = $form['field_json_metadata']['widget'][0]['dictionary_fields']['data']['#rows'] ?? [];
    $currently_modifying_index_fields = $form_state->get('index_fields_being_modified') != NULL ? $form_state->get('index_fields_being_modified') : [];
    $currently_modifying_index = $form_state->get('index_being_modified') != NULL ? $form_state->get('index_being_modified') : [];
    $currently_modifying_dictionary_fields = $form_state->get('dictionary_fields_being_modified') != NULL ? $form_state->get('dictionary_fields_being_modified') : [];

    // If the op (trigger) contains abort,
    // We're canceling the index we're currently modifying so unset it.
    if (str_contains($op, 'abort_index_key')) {
      unset($currently_modifying_index[$op_index[3]]);
    }
    // We're canceling the index field we're currently modifying so unset it.
    if (str_contains($op, 'abort_index_field_key')) {
      unset($currently_modifying_index_fields[$op_index[4]]);
    }

    // If the op (trigger) contains delete,
    // We're deleting the index we're editing so...
    if (str_contains($op, 'delete_index_key')) {
      // Unset it from being currently modified.
      unset($currently_modifying_index[$op_index[3]]);
      // Remove the respective field/data from the form.
      unset($current_index[$op_index[3]]);
    }
    // We're deleting the index field we're editing so...
    if (str_contains($op, 'delete_index_field_key')) {
      // Unset it from being currently modified.
      unset($currently_modifying_index_fields[$op_index[4]]);
      // Remove the respective field/data from the form.
      unset($current_index_fields[$op_index[4]]);
    }

    // If the op (trigger) contains update,
    // We're saving the field we're editing so...
    if (str_contains($op, 'update')) {
      // Get the entered data.
      $update_values = $form_state->getUserInput();
      // Update the respective current field data with our new input data.
      $current_index[$op_index[3]] = IndexFieldValues::updateIndexValues($op_index[3], $update_values, $current_index);
      // Unset the respective field/data from the form.
      unset($currently_modifying_index[$op_index[3]]);
      // Sort the current index data.
      ksort($current_index);
    }

    // If the op (trigger) contains edit
    // We're editing a specific field so...
    if (str_contains($op, 'edit')) {
      // Set the field we're modifying to that field.
      $currently_modifying_index[$op_index[3]] = $current_index[$op_index[3]];
    }

    // Let's retain the fields that are being modified.
    $form_state->set('dictionary_fields_being_modified', $currently_modifying_dictionary_fields);
    $form_state->set('index_fields_being_modified', $currently_modifying_index_fields);
    $form_state->set('index_being_modified', $currently_modifying_index);
    // Let's retain the fields that are already stored on the form,
    // but aren't currently being modified.
    $form_state->set('current_index_fields', $current_index_fields);
    $form_state->set('current_index', $current_index);
    $form_state->set('current_dictionary_fields', $current_dictionary_fields);
    // Let's rebuild the form.
    $form_state->setRebuild();
  }

  /**
   * Ajax callback to return index fields.
   */
  public static function subIndexEditFormAjax(array &$form, FormStateInterface $form_state) {
    return $form["field_json_metadata"]["widget"][0]["indexes"]["edit_index"]["index_key_0"]["group"]["fields"]["fields"];
  }

  /**
   * Ajax callback to return index fields.
   */
  public static function subIndexFormAjax(array &$form, FormStateInterface $form_state) {
    return $form["field_json_metadata"]["widget"][0]["indexes"]["fields"];
  }

  /**
   * Ajax callback to return indexes.
   */
  public static function indexFormAjax(array &$form, FormStateInterface $form_state) {
    $index_fields = $form["field_json_metadata"]["widget"][0]["indexes"]["fields"];

    // Validation errors skip submit callbacks, this will set the index fields
    // in the correct location.
    if ($index_fields["data"]) {
      $form["field_json_metadata"]["widget"][0]["indexes"]["field_collection"]["group"]["index"]["fields"] = $index_fields;
      $form["field_json_metadata"]["widget"][0]["indexes"]["fields"]['#access'] = FALSE;
    }

    return $form["field_json_metadata"]["widget"][0]["indexes"];
  }

  /**
   * Ajax callback to return index fields fieldset with Add Field button.
   */
  public static function subIndexFormFieldAjax(array &$form, FormStateInterface $form_state) {
    return $form["field_json_metadata"]["widget"][0]["indexes"]["field_collection"]["group"]["index"]["fields"];
  }

  /**
   * Ajax callback to return index fields fieldset with existing fields and Add
   * Field button.
   */
  public static function subIndexFormExistingFieldAjax(array &$form, FormStateInterface $form_state) {
    $form["field_json_metadata"]["widget"][0]["indexes"]["field_collection"]["group"]["index"]["fields"]["add_row_button"]['#access'] = TRUE;
    return $form["field_json_metadata"]["widget"][0]["indexes"]["field_collection"]["group"]["index"]["fields"]["add_row_button"];
  }

  /**
   * Widget validation callback.
   */
  public static function indexFieldVal($element, FormStateInterface &$form_state, array &$form) {
    $fields_to_validate = [
      'name' => 'Name',
      'length' => 'Length',
    ];

    foreach ($fields_to_validate as $field_key => $field_label) {
      IndexValidation::indexFieldVal($form_state, $field_key, $field_label);
    }
  }

}
