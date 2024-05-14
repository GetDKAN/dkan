<?php

namespace Drupal\data_dictionary_widget\Indexes;

/**
 * Various operations for creating Data Dictionary Widget fields.
 */
class IndexFieldButtons {

  /**
   * Returns the add index field button.
   */
  public static function addIndexFieldButton() {
    return [
      '#type' => 'submit',
      '#value' => 'Add field',
      '#access' => TRUE,
      '#op' => 'add_new_index_field',
      '#submit' => [
      [
        '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks',
        'indexAddSubformCallback',
      ],
      ],
      '#ajax' => [
        'callback' => '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks::subIndexformAjax',
        'wrapper' => 'field-json-metadata-dictionary-index-fields-new',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];
  }

  /**
   * Returns the add index button.
   */
  public static function addIndexButton() {
    return [
      '#type' => 'submit',
      '#value' => 'Add index',
      '#access' => TRUE,
      '#op' => 'add_new_index',
      '#submit' => [
      [
        '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks',
        'indexAddCallback',
      ],
      ],
      '#ajax' => [
        'callback' => '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks::indexformAjax',
        'wrapper' => 'field-json-metadata-dictionary-indexes',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];
  }

  /**
   * Returns the edit buttons.
   */
  public static function editIndexButtons($indexKey) {
    return [
      '#type' => 'image_button',
      '#name' => 'edit_index_' . $indexKey,
      '#id' => 'edit_index_' . $indexKey,
      '#access' => TRUE,
      '#op' => 'edit_' . $indexKey,
      '#src' => 'core/misc/icons/787878/cog.svg',
      '#attributes' => [
        'class' => ['index-field-plugin-settings-edit'],
        'alt' => t('Edit index'),
      ],
      '#submit' => [
          [
            '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks',
            'indexEditSubformCallback',
          ],
      ],
      '#ajax' => [
        'callback' => '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks::subIndexformAjax',
        'wrapper' => 'field-json-metadata-dictionary-index-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];
  }

  /**
   * Create Submit buttons.
   */
  public static function submitIndexFieldButton($location, $indexKey) {
    $callbackClass = $location == 'edit' ? 'indexEditSubformCallback' : 'indexAddSubformCallback';
    $op = !empty($indexKey) ? 'update_' . $indexKey : 'add_index_field';
    $value = $location == 'edit' ? 'Save' : 'Add ';
    $edit_index_button = [
      '#type' => 'submit',
      '#value' => $value,
      '#op' => $op,
      '#submit' => [
          [
            '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks',
            $callbackClass,
          ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks::subIndexformAjax',
        'wrapper' => 'field-json-metadata-dictionary-index-fields-new',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];

    if ($location == 'edit') {
      $edit_index_button['#name'] = 'update_' . $indexKey;
    }
    return $edit_index_button;
  }

  /**
   * Create Submit buttons.
   */
  public static function submitIndexButton($location, $indexKey) {
    $callbackClass = $location == 'edit' ? 'indexEditCallback' : 'indexAddCallback';
    $op = !empty($indexKey) ? 'update_' . $indexKey : 'add_index';
    $value = $location == 'edit' ? 'Save' : 'Submit Index';
    $edit_index_button = [
      '#type' => 'submit',
      '#value' => $value,
      '#op' => $op,
      '#submit' => [
          [
            '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks',
            $callbackClass,
          ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks::indexformAjax',
        'wrapper' => 'field-json-metadata-dictionary-indexes',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];

    if ($location == 'edit') {
      $edit_index_button['#name'] = 'update_' . $indexKey;
    }
    return $edit_index_button;
  }

  /**
   * Create Cancel button.
   */
  public static function cancelIndexFieldButton($location, $indexKey) {
    $callbackClass = $location == 'edit' ? 'indexEditSubformCallback' : 'indexAddSubformCallback';
    $op = $location == 'edit' && $indexKey ? 'abort_' . $indexKey : 'cancel_index_field';
    $cancel_index_button = [
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#op' => $op,
      '#submit' => [
            [
              '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks',
              $callbackClass,
            ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks::subIndexformAjax',
        'wrapper' => 'field-json-metadata-dictionary-index-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];

    if ($location == 'edit') {
      $cancel_index_button['#name'] = 'cancel_update_' . $indexKey;
    }
    return $cancel_index_button;
  }

  /**
   * Create Cancel button.
   */
  public static function cancelIndexButton($location, $indexKey) {
    $callbackClass = $location == 'edit' ? 'indexEditCallback' : 'indexAddCallback';
    $op = $location == 'edit' && $indexKey ? 'abort_' . $indexKey : 'cancel_index';
    $cancel_index_button = [
      '#type' => 'submit',
      '#value' => t('Cancel Index'),
      '#op' => $op,
      '#submit' => [
            [
              '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks',
              $callbackClass,
            ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks::indexformAjax',
        'wrapper' => 'field-json-metadata-dictionary-indexes',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];

    if ($location == 'edit') {
      $cancel_index_button['#name'] = 'cancel_update_' . $indexKey;
    }
    return $cancel_index_button;
  }

  /**
   * Create Delete button.
   */
  public static function deleteIndexButton($indexKey) {
    return [
      '#type' => 'submit',
      '#name' => 'index_delete_' . $indexKey,
      '#value' => t('Delete index field'),
      '#op' => 'delete_' . $indexKey,
      '#submit' => [
            [
              '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks',
              'indexEditSubformCallback',
            ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks::subIndexformAjax',
        'wrapper' => 'field-json-metadata-dictionary-index-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];
  }
}