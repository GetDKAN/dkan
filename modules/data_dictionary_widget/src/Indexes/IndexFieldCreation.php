<?php

namespace Drupal\data_dictionary_widget\Indexes;

/**
 * Various operations for creating Index fields.
 */
class IndexFieldCreation {

  /**
   * Create basic index fields fieldset.
   */
  public static function createGeneralIndexFields($element) {
    $element['indexes']['fields'] = [
      '#type' => 'fieldset',
      '#title' => t('Fields'),
      '#prefix' => '<div id = field-json-metadata-index-fields>',
      '#suffix' => '</div>',
      '#markup' => t('<div class="claro-details__description">One or more fields included in index. Must be keys from the fields object.</div>'),
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * Create basic indexes fieldset.
   */
  public static function createGeneralIndex($element, $current_indexes) {
    $element['indexes'] = [
      '#type' => 'fieldset',
      '#title' => t('Indexes'),
      '#prefix' => '<div id = field-json-metadata-index>',
      '#suffix' => '</div>',
      '#markup' => t('<div class="claro-details__description">Adding indexes to your datastore tables can improve response times from common queries.</div>'),
    ];

    $element['indexes']['current_index'] = $current_indexes;

    return $element;
  }

  /**
   * Create data index fields data rows.
   */
  public static function createIndexFieldsDataRows($index_field_values, $current_index_fields, $index_fields_data_results, $form_state) {
    if ($index_field_values) {
      return [
        '#access' => ((bool) $current_index_fields || (bool) $index_fields_data_results),
        '#type' => 'table',
        '#header' => ['NAME', 'LENGTH'],
        '#rows' => $form_state->get('cancel_index_field') ? $current_index_fields : ($index_fields_data_results ?? []),
        '#tree' => TRUE,
        '#theme' => 'custom_index_fields_table',
      ];
    }
  }

  /**
   * Create data index data rows.
   */
  public static function createIndexDataRows($current_indexes, $index_data_results, $form_state) {
    return [
      '#access' => ((bool) $current_indexes || (bool) $index_data_results),
      '#type' => 'table',
      '#header' => ['NAME', 'TYPE', 'FIELDS'],
      '#prefix' => '<div id = field-json-metadata-indexes>',
      '#suffix' => '</div>',
      '#rows' => $form_state->get('cancel_index') ? $current_indexes : ($index_data_results ?? []),
      '#tree' => TRUE,
      '#theme' => 'custom_index_table',
    ];
  }

}
