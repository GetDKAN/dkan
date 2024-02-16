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
      '#value' => 'Add index field',
      '#access' => TRUE,
      '#op' => 'add_new_index_field',
      '#submit' => [
      [
        '\Drupal\data_dictionary_widget\Controller\Widget\DictionaryIndexes\IndexFieldCallbacks',
        'indexAddSubformCallback',
      ],
      ],
      '#ajax' => [
        'callback' => '\Drupal\data_dictionary_widget\Controller\Widget\DictionaryIndexes\IndexFieldCallbacks::subIndexformAjax',
        'wrapper' => 'field-json-metadata-dictionary-index-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];
  }

  /**
   * Returns te edit buttons.
   */
  public static function editButtons($key) {
    return [
      '#type' => 'image_button',
      '#name' => 'edit_' . $key,
      '#id' => 'edit_' . $key,
      '#access' => TRUE,
      '#op' => 'edit_' . $key,
      '#src' => 'core/misc/icons/787878/cog.svg',
      '#attributes' => [
        'class' => ['index-field-plugin-settings-edit'],
        'alt' => t('Edit'),
      ],
      '#submit' => [
          [
            '\Drupal\data_dictionary_widget\Controller\Widget\DictionaryIndexes\IndexFieldCallbacks',
            'editIndexSubformCallback',
          ],
      ],
      '#ajax' => [
        'callback' => '\Drupal\data_dictionary_widget\Controller\Widget\DictionaryIndexes\IndexFieldCallbacks::subIndexformAjax',
        'wrapper' => 'field-json-metadata-dictionary-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];
  }
}