<?php

namespace Drupal\data_dictionary_widget\Indexes;

/**
 * Various operations for index widget buttons.
 */
class IndexFieldButtons {

  /**
   * Returns the add index field button.
   */
  public static function addIndexFieldButton() {
    return [
      '#type' => 'submit',
      '#value' => 'Add field to index',
      '#name' => 'add_index_field',
      '#access' => TRUE,
      '#op' => 'add_new_index_field',
      '#submit' => [
        [
          '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks',
          'indexAddSubformCallback',
        ],
      ],
      '#ajax' => [
        'callback' => '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks::subIndexFormAjax',
        'wrapper' => 'field-json-metadata-index-fields',
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
      '#name' => 'add_index',
      '#access' => TRUE,
      '#op' => 'add_new_index',
      '#submit' => [
        [
          '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks',
          'indexAddCallback',
        ],
      ],
      '#ajax' => [
        'callback' => '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks::indexFormAjax',
        'wrapper' => 'field-json-metadata-index',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];
  }

  /**
   * Returns the edit buttons.
   */
  public static function editIndexButtons($indexKey) {
    if (str_contains($indexKey, 'field')) {
      $callback = 'indexEditSubformCallback';
      $id = 'field-json-metadata-index-fields';
      $function = 'subIndexFormAjax';
    }
    else {
      $callback = 'indexEditCallback';
      $id = 'field-json-metadata-index';
      $function = 'indexFormAjax';
    }
    return [
      '#type' => 'image_button',
      '#name' => 'edit_' . $indexKey,
      '#id' => 'edit_' . $indexKey,
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
          $callback,
        ],
      ],
      '#ajax' => [
        'callback' => '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks::' . $function,
        'wrapper' => $id,
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [
        [
          'field_json_metadata',
          0,
          'indexes',
          'field_collection',
          'group',
          'index',
          'type',
        ],
      ],
    ];
  }

  /**
   * Create Submit buttons.
   */
  public static function submitIndexFieldButton($location, $indexKey) {
    $callbackClass = $location == 'edit' ? 'indexEditSubformCallback' : 'indexAddSubformCallback';
    $op = !empty($indexKey) ? 'update_' . $indexKey : 'add_index_field';
    $value = $location == 'edit' ? 'Save index field edit' : 'Save field to index';
    // Index fields cannot be edited once submitted so we use the same function
    // for both add and edit.
    $function = 'subIndexFormAjax';
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
        'callback' => 'Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks::' . $function,
        'wrapper' => 'field-json-metadata-index-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [
        [
          'field_json_metadata',
          0,
          'indexes',
          'fields',
          'field_collection',
          'group',
          'index',
          'fields',
          'name',
        ],
        [
          'field_json_metadata',
          0,
          'indexes',
          'fields',
          'field_collection',
          'group',
          'index',
          'fields',
          'length',
        ],
      ],
      '#element_validate' => [
        [
          'Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks',
          'indexFieldVal',
        ],
      ],
    ];

    if ($location == 'edit') {
      $indexKeyExplode = explode("_", $indexKey);
      $edit_index_button['#name'] = 'update_' . $indexKey;
      $edit_index_button['#limit_validation_errors'] = [
        [
          'field_json_metadata',
          0,
          'indexes',
          'fields',
          'edit_index_fields',
          $indexKeyExplode[3],
          'name',
        ],
        [
          'field_json_metadata',
          0,
          'indexes',
          'fields',
          'edit_index_fields',
          $indexKeyExplode[3],
          'length',
        ],
      ];
    }
    return $edit_index_button;
  }

  /**
   * Create Submit buttons.
   */
  public static function submitIndexButton($location, $indexKey) {
    $callbackClass = $location == 'edit' ? 'indexEditCallback' : 'indexAddCallback';
    $op = !empty($indexKey) ? 'update_' . $indexKey : 'add_index';
    $value = $location == 'edit' ? 'Save index edit' : 'Save index';
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
        'callback' => 'Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks::indexFormAjax',
        'wrapper' => 'field-json-metadata-index',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [
        [
          'field_json_metadata',
          0,
          'indexes',
          'field_collection',
          'group',
          'index',
          'description',
        ],
        [
          'field_json_metadata',
          0,
          'indexes',
          'field_collection',
          'group',
          'index',
          'fields',
        ],
      ],
    ];

    if ($location == 'edit') {
      $edit_index_button['#name'] = 'update_' . $indexKey;
    }
    return $edit_index_button;
  }

  /**
   * Create Cancel adding index field button.
   */
  public static function cancelIndexFieldButton($location, $indexKey, $id) {
    $callbackId = ($id === 'field-json-metadata-index-fields-new') ? 'subIndexFormExistingFieldAjax' : 'subIndexFormAjax';
    $callbackClass = $location == 'edit' ? 'indexEditSubformCallback' : 'indexAddSubformCallback';
    $op = $location == 'edit' && $indexKey ? 'abort_' . $indexKey : 'cancel_index_field';
    $cancel_index_button = [
      '#type' => 'submit',
      '#value' => t('Cancel adding field to index'),
      '#name' => 'cancel_index_field',
      '#op' => $op,
      '#submit' => [
        [
          '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks',
          $callbackClass,
        ],
      ],
      '#ajax' => [
        'callback' => "Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks::$callbackId",
        'wrapper' => $id,
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];

    if ($location == 'edit') {
      $cancel_index_button['#name'] = 'cancel_update_' . $indexKey;
      $cancel_index_button['#value'] = t('Cancel index field edit');
    }
    return $cancel_index_button;
  }

  /**
   * Create Cancel adding index button.
   */
  public static function cancelIndexButton($location, $indexKey) {
    $callbackClass = $location == 'edit' ? 'indexEditCallback' : 'indexAddCallback';
    $op = $location == 'edit' && $indexKey ? 'abort_' . $indexKey : 'cancel_index';
    $cancel_index_button = [
      '#type' => 'submit',
      '#value' => t('Cancel adding index'),
      '#name' => 'cancel_index',
      '#op' => $op,
      '#submit' => [
        [
          '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks',
          $callbackClass,
        ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks::indexFormAjax',
        'wrapper' => 'field-json-metadata-index',
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
  public static function deleteIndexFieldButton($indexKey) {
    return [
      '#type' => 'submit',
      '#name' => 'index_delete_' . $indexKey,
      '#value' => t('Remove field from index'),
      '#op' => 'delete_' . $indexKey,
      '#submit' => [
        [
          '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks',
          'indexEditSubformCallback',
        ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks::subIndexFormAjax',
        'wrapper' => 'field-json-metadata-index-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];
  }

  /**
   * Create Delete button.
   */
  public static function deleteIndexButton($indexKey) {
    return [
      '#type' => 'submit',
      '#name' => 'index_delete_' . $indexKey,
      '#value' => t('Remove index'),
      '#op' => 'delete_' . $indexKey,
      '#submit' => [
        [
          '\Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks',
          'indexEditCallback',
        ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Indexes\IndexFieldCallbacks::indexFormAjax',
        'wrapper' => 'field-json-metadata-index',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];
  }

}
