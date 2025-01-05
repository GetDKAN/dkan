<?php

namespace Drupal\data_dictionary_widget\Indexes;

use Drupal\Core\Form\FormStateInterface;

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
        '#rows' => $form_state->get('cancel_dictionary_field') ? $current_index_fields : ($index_fields_data_results ?? []),
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
      '#rows' => $form_state->get('cancel_update') ? $current_indexes : ($index_data_results ?? []),
      '#tree' => TRUE,
      '#theme' => 'custom_index_table',
    ];
  }

  protected static function createField(string $field, array $field_json_metadata, FormStateInterface &$form_state) {
    $identifier_uuid = $field_json_metadata['identifier'] ?? $form_state->getUserInput()["field_json_metadata"][0]["identifier"] ?? NULL;

    $fieldMappings = [
      'title' => [
        '#name' => 'field_json_metadata[0][title]',
        '#type' => 'textfield',
        '#required' => TRUE,
        '#title' => t('Title'),
        '#default_value' => $field_json_metadata['title'] ?? ($field_json_metadata['data']['title'] ?? ''),
      ],
      'identifier' => [
        '#name' => 'field_json_metadata[0][identifier]',
        '#type' => 'textfield',
        '#required' => TRUE,
        '#title' => t('Identifier'),
        '#attributes' => ['readonly' => 'readonly'],
        '#default_value' => $identifier_uuid ?? '',
        '#description' => t('<div class="form-item__description">This is the UUID of this Data Dictionary. To assign this data dictionary to a specific distribution use this <a href="@url" target="_blank">URL</a>.</div>', ['@url' => '/api/1/metastore/schemas/data-dictionary/items/' . $identifier_uuid]),
      ],
      'indexes' => [
        '#type' => 'textarea',
        '#access' => FALSE,
        '#required' => TRUE,
        '#title' => t('Index'),
        '#default_value' => isset($field_json_metadata['data']['indexes']) ? json_encode($field_json_metadata['data']['indexes']) : '',
      ],
      'dd_fields' => [
        '#type' => 'textarea',
        '#access' => FALSE,
        '#required' => TRUE,
        '#title' => t('DD Fields'),
        '#default_value' => isset($field_json_metadata['data']['fields']) ? json_encode($field_json_metadata['data']['fields']) : '',
      ],
    ];

    return $fieldMappings[$field] ?? [];
  }


}
