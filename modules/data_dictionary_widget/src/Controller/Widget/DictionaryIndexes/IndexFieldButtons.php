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
   * Returns the edit buttons.
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
            'indexEditSubformCallback',
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
   * Create Submit buttons.
   */
  public static function submitIndexFieldButton($location, $key) {
    $callbackClass = $location == 'edit' ? 'indexEditSubformCallback' : 'indexAddSubformCallback';
    $op = is_int($key) ? 'update_' . $key : 'add_index_field';
    $value = $location == 'edit' ? 'Save' : 'Add index field';
    $edit_index_button = [
      '#type' => 'submit',
      '#value' => $value,
      '#op' => $op,
      '#submit' => [
          [
            '\Drupal\data_dictionary_widget\Controller\Widget\DictionaryIndexes\IndexFieldCallbacks',
            $callbackClass,
          ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Controller\Widget\DictionaryIndexes\IndexFieldCallbacks::subIndexformAjax',
        'wrapper' => 'field-json-metadata-dictionary-index-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];

    if ($location == 'edit') {
      $edit_index_button['#name'] = 'index_field_update_' . $key;
    }
    return $edit_index_button;
  }

  /**
   * Create Cancel button.
   */
  public static function cancelIndexFieldButton($location, $key) {
    $callbackClass = $location == 'edit' ? 'indexEditSubformCallback' : 'indexAddSubformCallback';
    $op = $location == 'edit' && $key ? 'index_abort_' . $key : 'cancel_index_field';
    $cancel_index_button = [
      '#type' => 'submit',
      '#value' => t('Cancel index field'),
      '#op' => $op,
      '#submit' => [
            [
              '\Drupal\data_dictionary_widget\Controller\Widget\DictionaryIndexes\IndexFieldCallbacks',
              $callbackClass,
            ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Controller\Widget\DictionaryIndexes\IndexFieldCallbacks::subIndexformAjax',
        'wrapper' => 'field-json-metadata-dictionary-index-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];

    if ($location == 'edit') {
      $cancel_index_button['#name'] = 'index_field_cancel_update_' . $key;
    }
    return $cancel_index_button;
  }

  /**
   * Create Delete button.
   */
  public static function deleteIndexButton($key) {
    return [
      '#type' => 'submit',
      '#name' => 'delete_' . $key,
      '#value' => t('Delete'),
      '#op' => 'delete_' . $key,
      '#submit' => [
            [
              '\Drupal\data_dictionary_widget\Controller\Widget\FieldCallbacks',
              'editSubformCallback',
            ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Controller\Widget\FieldCallbacks::subformAjax',
        'wrapper' => 'field-json-metadata-dictionary-index-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];
  }
}