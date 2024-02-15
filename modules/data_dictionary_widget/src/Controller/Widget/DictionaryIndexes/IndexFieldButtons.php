<?php

namespace Drupal\data_dictionary_widget\Controller\Widget\DictionaryIndexes;

use Drupal\Core\Controller\ControllerBase;

/**
 * Various operations for creating Data Dictionary Widget fields.
 */
class IndexFieldButtons extends ControllerBase {

  /**
   * Returns the add button.
   */
  public static function addButton() {
    return [
      '#type' => 'submit',
      '#value' => 'Add field',
      '#access' => TRUE,
      '#op' => 'add_new_index_field',
      '#submit' => [
      [
        '\Drupal\data_dictionary_widget\Controller\Widget\DictionaryIndexes\IndexFieldCallbacks',
        'indexAddSubformCallback',
      ],
      ],
      '#ajax' => [
        'callback' => '\Drupal\data_dictionary_widget\Controller\Widget\DictionaryIndexes\IndexFieldCallbacks::subformAjax',
        'wrapper' => 'field-json-metadata-dictionary-index-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];
  }
}