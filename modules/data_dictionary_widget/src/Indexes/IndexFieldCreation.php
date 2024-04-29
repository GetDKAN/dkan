<?php

namespace Drupal\data_dictionary_widget\Indexes;

/**
 * Various operations for creating Data Dictionary Widget fields.
 */
class IndexFieldCreation {
  /**
   * Create basic widget.
   */
  public static function createGeneralIndexFields($element, $field_json_metadata, $current_index_fields, $new_index, $index_fields_being_modified) {

    $element['indexes']['index_fields'] = [
          '#access' => TRUE,
          '#type' => 'fieldset',
          '#title' => t('Fields'),
          '#prefix' => '<div id = field-json-metadata-dictionary-index-fields>',
          '#suffix' => '</div>',
          '#markup' => t('<div class="claro-details__description">One or more fields included in index. Must be keys from the fields object.</div>'),
        ];
        
    $element['indexes']['index_fields']['current_index_fields'] = $current_index_fields;

    return $element;
  }

  /**
   * Create basic widget.
   */
  public static function createGeneralIndex($element, $field_json_metadata, $current_index, $index_fields_being_modified) {

    $element['indexes'] = [
          '#type' => 'fieldset',
          '#title' => t('Data Dictionary Indexes'),
          '#prefix' => '<div id = field-json-metadata-dictionary-indexes>',
          '#suffix' => '</div>',
          '#markup' => t('<div class="claro-details__description">One or more indexes.</div>'),
        ];
        
    $element['indexes']['current_index'] = $current_index;

    return $element;
  }

  /**
   * Create data index data rows.
   */
  public static function createIndexFieldsDataRows($current_index_fields, $index_data_results, $form_state) {

    return [
      '#access' => ((bool) $current_index_fields || (bool) $index_data_results),
      '#type' => 'table',
      '#header' => ['NAME', 'LENGTH'],
      '#prefix' => '<div id = field-json-metadata-dictionary-index-fields>',
      '#suffix' => '</div>',
      '#rows' => $form_state->get('cancel_index_field') ? $current_index_fields : ($index_data_results ?? []),
      '#tree' => TRUE,
      '#theme' => 'custom_index_fields_table',
    ];

  }

    /**
   * Create data index data rows.
   */
  public static function createIndexDataRows($current_indexes, $index_data_results, $form_state) {

    return [
      '#access' => ((bool) $current_indexes || (bool) $index_data_results),
      '#type' => 'table',
      '#header' => ['INDEX'],
      '#rows' => $form_state->get('cancel_index') ? $current_indexes : ($index_data_results ?? []),
      '#tree' => TRUE,
      '#theme' => 'custom_index_table',
    ];

  }
}