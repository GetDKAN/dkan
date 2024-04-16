<?php

namespace Drupal\data_dictionary_widget\Fields;

/**
 * Various operations for creating Data Dictionary Widget fields.
 */
class FieldButtons {

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
        '\Drupal\data_dictionary_widget\Fields\FieldCallbacks',
        'addSubformCallback',
      ],
      ],
      '#ajax' => [
        'callback' => '\Drupal\data_dictionary_widget\Fields\FieldCallbacks::subformAjax',
        'wrapper' => 'field-json-metadata-dictionary-fields',
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
        'class' => ['field-plugin-settings-edit'],
        'alt' => t('Edit'),
      ],
      '#submit' => [
          [
            '\Drupal\data_dictionary_widget\Fields\FieldCallbacks',
            'editSubformCallback',
          ],
      ],
      '#ajax' => [
        'callback' => '\Drupal\data_dictionary_widget\Fields\FieldCallbacks::subformAjax',
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
            '\Drupal\data_dictionary_widget\Fields\FieldCallbacks',
            $callbackClass,
          ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Fields\FieldCallbacks::subformAjax',
        'wrapper' => 'field-json-metadata-dictionary-fields',
        'effect' => 'fade',
      ],
      '#element_validate' => [self::assembleValidation()],
    ];

    if ($location == 'edit') {
      $edit_button['#name'] = 'update_' . $key;
    }

    return $edit_button;
  }

  /**
   * Assemble element validation property.
   */
  protected static function assembleValidation() {
    return ['Drupal\data_dictionary_widget\Fields\FieldCallbacks',
      'customValidationCallback',
    ];
  }

  /**
   * Create Cancel button.
   */
  public static function cancelButton($location, $key) {
    $callbackClass = $location == 'edit' ? 'editSubformCallback' : 'addSubformCallback';
    $op = $location == 'edit' && is_int($key) ? 'abort_' . $key : 'cancel';
    $cancel_button = [
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#op' => $op,
      '#submit' => [
            [
              '\Drupal\data_dictionary_widget\Fields\FieldCallbacks',
              $callbackClass,
            ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Fields\FieldCallbacks::subformAjax',
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
              '\Drupal\data_dictionary_widget\Fields\FieldCallbacks',
              'editSubformCallback',
            ],
      ],
      '#ajax' => [
        'callback' => 'Drupal\data_dictionary_widget\Fields\FieldCallbacks::subformAjax',
        'wrapper' => 'field-json-metadata-dictionary-fields',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => [],
    ];
  }

}
