<?php

namespace Drupal\data_dictionary_widget\Controller\Widget\DictionaryIndexes;

use Drupal\Core\Controller\ControllerBase;

/**
 * Various operations for creating Data Dictionary Widget fields.
 */
class IndexFieldCreation extends ControllerBase {

  /**
   * Create basic widget.
   */
public static function createGeneralIndexFields($element, $field_json_metadata, $current_index_fields, $index_fields_being_modified) {

  $element['index_fields'] = [
        '#type' => 'fieldset',
        '#title' => t('Fields'),
        '#prefix' => '<div id = field-json-metadata-dictionary-index-fields>',
        '#suffix' => '</div>',
        '#markup' => t('<div class="claro-details__description">One or more fields included in index. Must be keys from the fields object.</div>'),
      ];
      
  $element['index_fields']['current_index_fields'] = $current_index_fields;

  return $element;
  }

  /**
   * Create data index data rows.
   */
  public static function createIndexDataRows($current_index_fields, $index_data_results, $form_state) {

    return [
      '#access' => ((bool) $current_index_fields || (bool) $index_data_results),
      '#type' => 'table',
      '#header' => ['NAME', 'LENGTH'],
      '#rows' => $form_state->get('cancel_index') ? $current_index_fields : ($index_data_results ?? []),
      '#tree' => TRUE,
      '#theme' => 'custom_index_fields_table',
    ];

  }
}