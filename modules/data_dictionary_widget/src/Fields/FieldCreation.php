<?php

namespace Drupal\data_dictionary_widget\Fields;

use Drupal\Core\Form\FormStateInterface;

/**
 * Various operations for creating Data Dictionary Widget fields.
 */
class FieldCreation {

  /**
   * Create basic widget.
   */
  public static function createGeneralFields($element, $field_json_metadata, $current_fields, $form_state) {

    $element['identifier'] = self::createField('identifier', $field_json_metadata, $form_state);

    $element['title'] = self::createField('title', $field_json_metadata, $form_state);

    $element['dictionary_fields'] = [
      '#type' => 'fieldset',
      '#title' => t('Data Dictionary Fields'),
      '#prefix' => '<div id = field-json-metadata-dictionary-fields>',
      '#suffix' => '</div>',
      '#markup' => t('<div class="claro-details__description">A data dictionary for this resource, compliant with the <a href="https://specs.frictionlessdata.io/table-schema/" target="_blank">Table Schema</a> specification.</div>'),
    ];

    $element['dictionary_fields']['current_fields'] = $current_fields;

    if (isset($field_json_metadata['data']['indexes'])) {
      $element['indexes'] = self::createField('indexes', $field_json_metadata, $form_state);
    }

    return $element;
  }

  /**
   * Build data dictionary fields from field_json_metadata.
   *
   * @param string $field
   *   Data dictionary field.
   * @param array $field_json_metadata
   *   Data dictionary indexes.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state object.
   *
   * @return array
   *   Field array structure.
   */
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
    ];

    return $fieldMappings[$field] ?? [];
  }

  /**
   * Create data dictionary data rows.
   */
  public static function createDictionaryDataRows($current_fields, $data_results, $form_state) {

    return [
      '#access' => ((bool) $current_fields || (bool) $data_results),
      '#type' => 'table',
      '#header' => ['NAME', 'TITLE', 'DETAILS'],
      '#rows' => $form_state->get('cancel') ? $current_fields : ($data_results ?? []),
      '#tree' => TRUE,
      '#theme' => 'custom_table',
    ];

  }

}
