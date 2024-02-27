<?php

namespace Drupal\data_dictionary_widget\Controller\Widget\DictionaryIndexes;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Various operations for the Data Dictionary Widget callbacks.
 */
class IndexFieldCallbacks extends ControllerBase {
  /**
   * Submit callback for the Index Add button.
   */
  public static function indexAddSubformCallback(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $op = $trigger['#op'];
    $form_state->set('add_new_index_field', '');
    // $fields_being_added = $form_state->set('fields_being_added', '');
    $current_index_fields = $form["field_json_metadata"]["widget"][0]["index_fields"]["data"]["#rows"];
    if ($current_index_fields) {
      $form_state->set('current_index_fields', $current_index_fields);
    }

    if ($op === 'cancel_index_field') {
      $form_state->set('cancel_index', TRUE);
    }

    if ($op === 'add_new_index_field') {
      $add_index_fields = IndexFieldAddCreation::addIndexFields();

      $form_state->set('add_new_index_field', $add_index_fields);
    }

    if ($op === 'add_index_field') {
      $form_state->set('new_index_fields', $form_state->getUserInput());
      $form_state->set('add', TRUE);
      $form_state->set('cancel_index', FALSE);
    }

    $form_state->setRebuild();
  }

  /**
   * Submit callback for the Index Edit button.
   */
  public static function indexEditSubformCallback(array &$form, FormStateInterface $form_state) {
    $trigger = $form_state->getTriggeringElement();
    $current_index_fields = $form["field_json_metadata"]["widget"][0]["index_fields"]["data"]["#rows"];
    $op = $trigger['#op'];
    $op_index = explode("_", $trigger['#op']);
    $currently_modifying_index_fields = $form_state->get('index_fields_being_modified') != NULL ? $form_state->get('index_fields_being_modified') : [];

    if (str_contains($op, 'abort')) {
      unset($currently_modifying_index_fields[$op_index[1]]);
    }

    if (str_contains($op, 'delete')) {
      unset($currently_modifying_index_fields[$op_index[1]]);
      unset($current_index_fields[$op_index[1]]);
    }

    if (str_contains($op, 'update')) {
      $update_values = $form_state->getUserInput();
      unset($currently_modifying_index_fields[$op_index[1]]);
      unset($current_index_fields [$op_index[1]]);
      $current_index_fields [$op_index[1]] = IndexFieldValues::updateIndexFieldValues($op_index[1], $update_values, $current_index_fields );
      ksort($current_index_fields );
    }

    if (str_contains($op, 'edit')) {
      $currently_modifying_index_fields[$op_index[1]] = $current_index_fields [$op_index[1]];
    }

    $form_state->set('index_fields_being_modified', $currently_modifying_index_fields);
    $form_state->set('current_index_fields', $current_index_fields );
    $form_state->setRebuild();
  }

  /**
   * Ajax callback.
   */
  public static function subIndexformAjax(array &$form, FormStateInterface $form_state) {
    return $form["field_json_metadata"]["widget"][0]["index_fields"];
  }
}