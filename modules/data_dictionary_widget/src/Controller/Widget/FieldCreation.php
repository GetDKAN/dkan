<?php

namespace Drupal\data_dictionary_widget\Controller\Widget;

use Drupal\Core\Controller\ControllerBase;

/**
 * Various operations for creating Data Dictionary Widget fields.
 */
class FieldCreation extends ControllerBase {

  /**
   * Create basic widget.
   */
  public static function createGeneralFields($element, $field_json_metadata, $current_fields, $fields_being_modified) {

    $element['identifier'] = [
      '#name' => 'field_json_metadata[0][identifier]',
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => t('Identifier'),
      '#default_value' => $field_json_metadata['identifier'] ?? '',
    ];

    $element['title'] = [
      '#name' => 'field_json_metadata[0][title]',
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => t('Title'),
      '#default_value' => $field_json_metadata['title'] ?? '',
    ];

    $element['dictionary_fields'] = [
      '#type' => 'fieldset',
      '#title' => t('Data Dictionary Fields'),
      '#prefix' => '<div id = field-json-metadata-dictionary-fields>',
      '#suffix' => '</div>',
      '#markup' => t('<div class="claro-details__description">A data dictionary for this resource, compliant with the <a href="https://specs.frictionlessdata.io/table-schema/" target="_blank">Table Schema</a> specification.</div>'),
    ];
    $element['dictionary_fields']['bulk_changes'] = '';
    $element['dictionary_fields']['current_fields'] = $current_fields;

    return $element;
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
