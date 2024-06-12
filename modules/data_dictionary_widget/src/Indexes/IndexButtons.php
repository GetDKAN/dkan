<?php

namespace Drupal\data_dictionary_widget\Indexes;

use Drupal\Core\Form\FormStateInterface;
/**
 * Various operations for creating Data Dictionary Widget fields.
 */
class IndexButtons {

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
        '\Drupal\data_dictionary_widget\Indexes\IndexCallbacks',
        'indexFieldAddSubformCallback',
      ],
      ],
      '#ajax' => [
        'callback' => '\Drupal\data_dictionary_widget\Indexes\IndexCallbacks::subIndexFormAjax',
        'wrapper' => 'field-json-metadata-dictionary-index-fields',
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
          '\Drupal\data_dictionary_widget\Indexes\IndexCallbacks',
          'indexAddCallback',
        ],
      ],
      '#ajax' => [
        'callback' => '\Drupal\data_dictionary_widget\Indexes\IndexCallbacks::indexFormAjax',
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
            '\Drupal\data_dictionary_widget\Indexes\IndexCallbacks',
            'indexEditSubformCallback',
          ],
      ],
      '#ajax' => [
        'callback' => '\Drupal\data_dictionary_widget\Indexes\IndexCallbacks::subIndexFormAjax',
        'wrapper' => 'field-json-metadata-dictionary-index-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [
        ['field_json_metadata', 0, 'indexes', 'field_collection', 'group', 'index', 'type'],
      ],
    ];
  }

  /**
   * Returns the edit Index Field Buttons.
   */
  public static function editIndexFieldButtons($indexFieldKey) {
    return [
      '#type' => 'image_button',
      // '#name' => 'edit_index_field' . $indexKey . '_' .  $indexFieldKey,
      // '#id' => 'edit_index_field' . $indexKey . '_' . $indexFieldKey,
      // '#access' => TRUE,
      // '#op' => 'index_field_edit_' . $indexKey . '_' . $indexFieldKey,
      '#name' => 'edit_index_field' . $indexFieldKey,
      '#id' => 'edit_index_field' . $indexFieldKey,
      '#access' => TRUE,
      '#op' => 'index_field_edit_' . $indexFieldKey,
      '#src' => 'core/misc/icons/787878/cog.svg',
      '#attributes' => [
        'class' => ['index-field-plugin-settings-edit'],
        'alt' => t('Edit index field'),
      ],
      '#submit' => [
          [
            '\Drupal\data_dictionary_widget\Indexes\IndexCallbacks',
            'indexFieldEditSubformCallback',
          ],
      ],
      '#ajax' => [
        'callback' => '\Drupal\data_dictionary_widget\Indexes\IndexCallbacks::subIndexformAjax',
        'wrapper' => 'field-json-metadata-dictionary-index-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];
  }

  /**
   * Create Submit buttons.
   */
  public static function submitIndexFieldButton($location, $indexFieldKey) {
    $callbackClass = $location == 'edit' ? 'indexEditSubformCallback' : 'indexAddSubformCallback';
    $op = !empty($indexFieldKey) ? 'update_' . $indexFieldKey : 'add_index_field';
    $value = $location == 'edit' ? 'Save' : 'Add ';
    $edit_index_field_button = [
      '#type' => 'submit',
      '#value' => $value,
      '#op' => $op,
      '#submit' => [
        [
          '\Drupal\data_dictionary_widget\Indexes\IndexCallbacks',
          $callbackClass,
        ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Indexes\IndexCallbacks::subIndexFormAjax',
        'wrapper' => 'field-json-metadata-dictionary-index-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [
        ['field_json_metadata', 0, 'indexes', 'fields', 'field_collection', 'group', 'index', 'fields', 'name'],
        ['field_json_metadata', 0, 'indexes', 'fields', 'field_collection', 'group', 'index', 'fields', 'length'],
      ],
    ];

    if ($location == 'edit') {
      $edit_index_button['#name'] = 'update_' . $indexFieldKey;
    }
    return $edit_index__field_button;
  }

  /**
   * Create Submit buttons.
   */
  public static function submitIndexButton($location, $indexKey) {
    $class = static::class;
    $callbackClass = $location == 'edit' ? 'indexEditCallback' : 'indexAddCallback';
    $op = !empty($indexKey) ? 'update_' . $indexKey : 'add_index';
    $value = $location == 'edit' ? 'Save' : 'Submit Index';
    $edit_index_button = [
      '#type' => 'submit',
      '#value' => $value,
      '#op' => $op,
      '#submit' => [
        [
          '\Drupal\data_dictionary_widget\Indexes\IndexCallbacks',
          $callbackClass,
        ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Indexes\IndexCallbacks::indexFormAjax',
        'wrapper' => 'field-json-metadata-dictionary-indexes',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [
        ['field_json_metadata', 0, 'indexes', 'field_collection', 'group', 'index', 'description'],
        ['field_json_metadata', 0, 'indexes', 'field_collection', 'group', 'index', 'fields'],
      ],
    ];

    if ($location == 'edit') {
      $edit_index_button['#name'] = 'update_' . $indexKey;
    }
    return $edit_index_button;
  }

  /**
   * Create Cancel button.
   */
  public static function cancelIndexFieldButton($location, $indexFieldKey, $id) {
    $callbackId = ($id === 'field-json-metadata-dictionary-index-fields-new') ? 'subIndexFormExistingFieldAjax' : 'subIndexFormFieldAjax';
    $callbackClass = $location == 'edit' ? 'indexFieldEditSubformCallback' : 'indexFieldAddSubformCallback';
    $op = $location == 'edit' && $indexFieldKey ? 'abort_' . $indexFieldKey : 'cancel_index_field';
    $cancel_index_button = [
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#op' => $op,
      '#submit' => [
        [
          '\Drupal\data_dictionary_widget\Indexes\IndexCallbacks',
          $callbackClass,
        ],
      ],
      '#ajax' => [
        'callback' => "Drupal\data_dictionary_widget\Indexes\IndexCallbacks::$callbackId",
        'wrapper' => $id,
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];

    if ($location == 'edit') {
      $cancel_index_button['#name'] = 'cancel_update_' . $indexFieldKey;
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
          '\Drupal\data_dictionary_widget\Indexes\IndexCallbacks',
          $callbackClass,
        ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Indexes\IndexCallbacks::indexFormAjax',
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
          '\Drupal\data_dictionary_widget\Indexes\IndexCallbacks',
          'indexEditSubformCallback',
        ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Indexes\IndexCallbacks::subIndexFormAjax',
        'wrapper' => 'field-json-metadata-dictionary-index-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];
  }

  /**
   * Create Delete Index Field button.
   */
  public static function deleteIndexFieldButton($indexFieldKey) {
    return [
      '#type' => 'submit',
      '#name' => 'index_delete_' . $indexFieldKey,
      '#value' => t('Delete index field'),
      '#op' => 'delete_' . $indexFieldKey,
      '#submit' => [
            [
              '\Drupal\data_dictionary_widget\Indexes\IndexCallbacks',
              'indexFieldEditSubformCallback',
            ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Indexes\IndexCallbacks::subIndexformAjax',
        'wrapper' => 'field-json-metadata-dictionary-index-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];
  }
}