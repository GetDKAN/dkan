<?php

namespace Drupal\data_dictionary_widget\Indexes;

/**
 * Various operations for creating edit fields for indexes.
 */
class IndexFieldEditCreation {

  /**
   * Create edit index fields.
   */
  public static function editIndexFields($indexKey, $current_index_fields) {
    $id = $current_index_fields ? 'field-json-metadata-index-fields' : 'field-json-metadata-index-fields-new';
    // We split the key to get the index field location.
    $indexKeyExplode = explode("_", $indexKey);
    $edit_index_fields['name'] = [
      '#name' => 'field_json_metadata[0][indexes][fields][edit_index_fields][' . $indexKeyExplode[3] . '][name]',
      '#type' => 'textfield',
      '#value' => $current_index_fields[$indexKeyExplode[3]]['name'],
      '#title' => 'Name',
      '#required' => TRUE,
    ];
    $edit_index_fields['length'] = [
      '#name' => 'field_json_metadata[0][indexes][fields][edit_index_fields][' . $indexKeyExplode[3] . '][length]',
      '#type' => 'number',
      '#value' => $current_index_fields[$indexKeyExplode[3]]['length'],
      '#title' => 'Length',
      '#required' => TRUE,
    ];

    $edit_index_fields['update_index_field']['actions'] = self::createIndexFieldsActionFields($indexKey, $id);

    return $edit_index_fields;
  }

  /**
   * Create edit index.
   */
  public static function editIndex($indexKey, $current_index, $form_state) {
    $id = $current_index ? "field-json-metadata-index-new" : "field-json-metadata-index";
    $indexKeyExplode = explode("_", $indexKey);

    $edit_index['description'] = [
      '#name' => "field_json_metadata[0][indexes][edit_index][index_key_" . $indexKeyExplode[2] . "][description]",
      '#type' => 'textfield',
      '#value' => $current_index[$indexKeyExplode[2]]['description'],
      '#title' => 'Name',
    ];
    $edit_index['type'] = [
      '#name' => "field_json_metadata[0][indexes][edit_index][index_key_" . $indexKeyExplode[2] . "][type]",
      '#type' => 'select',
      '#description' => t('Index type.'),
      '#title' => 'Index Type',
      '#default_value' => 'index',
      '#op' => 'index_type',
      '#required' => TRUE,
      '#options' => [
        'index' => t('index'),
        'fulltext' => t('fulltext'),
      ],
      '#value' => $current_index[$indexKeyExplode[2]]['type'],
    ];

    $edit_index['group'] = [
      '#type' => 'fieldset',
      '#title' => t('Fields'),
      '#prefix' => '<div id = field-json-metadata-index-fields>',
      '#suffix' => '</div>',
      '#markup' => t('<div class="claro-details__description">One or more fields included in index. Must be keys from the fields object.</div>'),
      '#attributes' => [
        'class' => ['index-fields-form'],
      ],
    ];

    $edit_index['group']['fields']['data'] = IndexFieldCreation::createIndexFieldsDataRows($current_index[$indexKeyExplode[2]]['fields'], $current_index[$indexKeyExplode[2]]['fields'], $current_index[$indexKeyExplode[2]]['fields'], $form_state);

    $edit_index['update_index']['actions'] = self::createIndexActionFields($indexKey, $id);

    return $edit_index;
  }

  /**
   * Create Action buttons.
   */
  private static function createIndexActionFields($indexKey, $id) {
    return [
      '#type' => 'actions',
      'save_update' => IndexFieldButtons::submitIndexButton('edit', $indexKey),
      'cancel_updates' => IndexFieldButtons::cancelIndexButton('edit', $indexKey, $id),
      'delete_index' => IndexFieldButtons::deleteIndexButton($indexKey),
    ];
  }

  /**
   * Create Action buttons.
   */
  private static function createIndexFieldsActionFields($indexKey, $id) {
    return [
      '#type' => 'actions',
      'save_update' => IndexFieldButtons::submitIndexFieldButton('edit', $indexKey),
      'cancel_updates' => IndexFieldButtons::cancelIndexFieldButton('edit', $indexKey, $id),
      'delete_field' => IndexFieldButtons::deleteIndexFieldButton($indexKey),
    ];
  }

}
