<?php

namespace Drupal\data_dictionary_widget\Controller\Widget;

use Drupal\Core\Controller\ControllerBase;

/**
 * Various operations for creating Data Dictionary Widget fields.
 */
class FieldButtons extends ControllerBase {

  /**
   * Returns the add button.
   */
  public static function addButton() {
    return [
      '#type' => 'submit',
      '#value' => 'Add field',
      '#access' => TRUE,
      '#op' => 'add_new_field',
      '#submit' => [
      [
        '\Drupal\data_dictionary_widget\Controller\Widget\FieldCallbacks',
        'addSubformCallback',
      ],
      ],
      '#ajax' => [
        'callback' => '\Drupal\data_dictionary_widget\Controller\Widget\FieldCallbacks::subformAjax',
        'wrapper' => 'field-json-metadata-dictionary-fields',
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
        'class' => ['field-plugin-settings-edit'],
        'alt' => t('Edit'),
      ],
      '#submit' => [
          [
            '\Drupal\data_dictionary_widget\Controller\Widget\FieldCallbacks',
            'editSubformCallback',
          ],
      ],
      '#ajax' => [
        'callback' => '\Drupal\data_dictionary_widget\Controller\Widget\FieldCallbacks::subformAjax',
        'wrapper' => 'field-json-metadata-dictionary-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];

  }

  /**
   * Create Submit buttons.
   */
  public static function submitButton($location, $key) {
    $callbackClass = $location == 'edit' ? 'editSubformCallback' : 'addSubformCallback';
    $op = is_int($key) ? 'update_' . $key : 'add';
    $value = $location == 'edit' ? 'Save' : 'Add';
    $edit_button = [
      '#type' => 'submit',
      '#value' => $value,
      '#op' => $op,
      '#submit' => [
          [
            '\Drupal\data_dictionary_widget\Controller\Widget\FieldCallbacks',
            $callbackClass,
          ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Controller\Widget\FieldCallbacks::subformAjax',
        'wrapper' => 'field-json-metadata-dictionary-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];

    if ($location == 'edit') {
      $edit_button['#name'] = 'update_' . $key;
    }
    return $edit_button;
  }

  /**
   * Create Cancel button.
   */
  public static function cancelButton($location, $key) {
    $callbackClass = $location == 'edit' ? 'editSubformCallback' : 'addSubformCallback';
    $op = $location == 'edit' && $key ? 'abort_' . $key : 'cancel';
    $cancel_button = [
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#op' => $op,
      '#submit' => [
            [
              '\Drupal\data_dictionary_widget\Controller\Widget\FieldCallbacks',
              $callbackClass,
            ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Controller\Widget\FieldCallbacks::subformAjax',
        'wrapper' => 'field-json-metadata-dictionary-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];

    if ($location == 'edit') {
      $cancel_button['#name'] = 'cancel_update_' . $key;
    }
    return $cancel_button;
  }

  /**
   * Create bulk changes buttons.
   */
  public static function bulkChangesButtons($type = NULL) {
    if ($type == 'edit_all') {
      return self::bulkButtons('edit');
    }
    else {
      return [
        '#type' => 'actions',
        'save_all_settings' => self::bulkButtons('save'),
        'cancel_all_settings' => self::bulkButtons('cancel'),
      ];
    }

  }

  /**
   * Create save, cancel. and edit all buttons.
   */
  public static function bulkButtons($type) {
    $button = [
      '#type' => 'submit',
      '#value' => ucfirst($type) . ' All',
      '#title' => ucfirst($type) . ' all fields in edit mode.',
      '#op' => $type . '_all',
      '#submit' => [
            [
              '\Drupal\data_dictionary_widget\Controller\Widget\FieldCallbacks',
              'bulkChangeSubformCallback',
            ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Controller\Widget\FieldCallbacks::subformAjax',
        'wrapper' => 'field-json-metadata-dictionary-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];

    return $button;
  }

  /**
   * Create Delete button.
   */
  public static function deleteButton($key) {
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
        'wrapper' => 'field-json-metadata-dictionary-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];
  }

}
