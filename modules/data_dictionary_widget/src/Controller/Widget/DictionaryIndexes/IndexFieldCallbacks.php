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

    if ($op === 'cancel') {
      $form_state->set('cancel', TRUE);
    }

    if ($op === 'add_new_index_field') {
      $add_fields = IndexFieldAddCreation::addIndexFields();

      $form_state->set('add_new_index_field', $add_index_fields);
    }

    if ($op === 'add') {
      $form_state->set('new_index_fields', $form_state->getUserInput());
      $form_state->set('add', TRUE);
      $form_state->set('cancel', FALSE);
    }

    $form_state->setRebuild();
  }

  /**
   * Ajax callback.
   */
  public static function subformAjax(array &$form, FormStateInterface $form_state) {
    return $form["field_json_metadata"]["widget"][0]["index_fields"];
  }
}