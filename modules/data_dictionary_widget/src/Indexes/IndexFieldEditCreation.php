<?php

namespace Drupal\data_dictionary_widget\Indexes;

/**
 * Various operations for creating Data Dictionary Widget add fields.
 */

class IndexFieldEditCreation {
  /**
   * Create edit fields for Data Dictionary Widget.
   */
  public static function editIndexFields($indexKey, $current_index_fields, $index_fields_being_modified) {
    $id = $current_index_fields ? "field-json-metadata-dictionary-index-fields" : "field-json-metadata-dictionary-index-fields-new";
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

    $edit_index_fields['update_index_field']['actions'] = self::createIndexFieldActionFields($indexKey, $id);

    //$edit_index_fields['update_index_field']['actions'] = self::createIndexActionFields($indexKey, $id);

    return $edit_index_fields;
  }

  /**
   * Create edit fields for Data Dictionary Widget.
   */
  public static function editIndex($indexKey, $current_index, $index_being_modified, $element) {
    $id = $current_index ? "field-json-metadata-dictionary-index-new" : "field-json-metadata-dictionary-index";
    $indexKeyExplode = explode("_", $indexKey);
    if ($element["fields"]["data"]["#rows"]) {
      $test = $element["fields"]["data"]["#rows"];
    } else {
      $test = $current_index[$indexKeyExplode[2]]['fields'];
    }

    $edit_index['description'] = [
      '#name' => "field_json_metadata[0][indexes][edit_index][index_key_" . $indexKeyExplode[2] . "][description]",
      '#type' => 'textfield',
      '#description' => t('Description of index purpose or functionality.'),
      '#value' => $current_index[$indexKeyExplode[2]]['description'],
      '#title' => 'Name',
      '#required' => TRUE,
    ];
    $edit_index['type'] = [
      '#name' => "field_json_metadata[0][indexes][edit_index][index_key_" . $indexKeyExplode[2] . "][type]",
      '#value' => $current_index[$indexKeyExplode[2]]['type'],
      '#type' => 'select',
      '#description' => t('Index type.'),
      '#title' => 'Index Type',
      '#options' => [
        'index' => t('index'),
        'fulltext' => t('fulltext'),
      ],
      '#required' => TRUE,
    ];

    $edit_index['fields'] = [
      '#type' => 'fieldset',
      '#title' => t('Fields'),
      '#prefix' => '<div id = field-json-metadata-dictionary-index-fields>',
      '#suffix' => '</div>',
      '#markup' => t('<div class="claro-details__description">One or more fields included in index. Must be keys from the fields object.</div>'),
      '#attributes' => [
        'class' => ['index-fields-form'],
      ],
    ];

    $edit_index['fields']['index_fields'] = $element["fields"]["data"];

    $button = IndexFieldButtons::editIndexFieldButtons('index_field_key_0');

    $edit_button['button'] = $button;
    //$edit_index['edit_index_field_buttons']['index_field_key_' . $indexKey]['edit_index_field_button'] = IndexFieldButtons::editIndexFieldButtons('index_field_key_' . $indexKey);
    $edit_index["fields"]["index_fields"]["#rows"][0] = array_merge($element["fields"]["data"]["#rows"][0], $edit_button);

    $edit_index['fields']['add_row_button'] = IndexFieldButtons::addIndexFieldButton();

    //IndexFieldOperations::setIndexFieldsAjaxElements($current_index[$indexKeyExplode[2]]['fields']);
    
    // $edit_index['fields']['name'] = [
    //   '#name' => 'field_json_metadata[0][index][data][' . $indexKeyExplode[3] . '][field_collection][name]',
    //   '#type' => 'textfield',
    //   '#value' => $current_index[$indexKeyExplode[3]]['name'],
    //   '#title' => 'Name',
    // ];
    // $edit_index['fields']['length'] = [
    //   '#name' => 'field_json_metadata[0][index][data]['. $indexKeyExplode[3] .'][field_collection][length]',
    //   '#type' => 'number',
    //   '#value' => $current_index[$indexKeyExplode[3]]['length'],
    //   '#title' => 'Length',
    // ];

    $edit_index['update_index']['actions'] = self::createIndexActionFields($indexKey, $id );

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
      'delete_field' => IndexFieldButtons::deleteIndexButton($indexKey),
    ];
  }

  /**
   * Create Action buttons.
   */
  private static function createIndexFieldActionFields($indexKey, $id) {
    return [
      '#type' => 'actions',
      'save_update' => IndexFieldButtons::submitIndexFieldButton('edit', $indexKey),
      'cancel_updates' => IndexFieldButtons::cancelIndexFieldButton('edit', $indexKey, $id),
      'delete_field' => IndexFieldButtons::deleteIndexButton($indexKey),
    ];
  }

}