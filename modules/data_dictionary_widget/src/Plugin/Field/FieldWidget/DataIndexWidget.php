<?php

namespace Drupal\data_dictionary_widget\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\data_dictionary_widget\Fields\FieldCreation;
use Drupal\data_dictionary_widget\Fields\FieldOperations;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\data_dictionary_widget\Indexes\IndexFieldButtons;
use Drupal\data_dictionary_widget\Indexes\IndexFieldCreation;
use Drupal\data_dictionary_widget\Indexes\IndexFieldEditCreation;
use Drupal\data_dictionary_widget\Indexes\IndexFieldOperations;

/**
 * A data-dictionary widget.
 *
 * @FieldWidget(
 *   id = "data_index_widget",
 *   label = @Translation("Data-Dictionary Index Widget"),
 *   field_types = {
 *     "string_long"
 *   }
 * )
 */
class DataIndexWidget extends AbstractMetadataWidget implements TrustedCallbackInterface {

  protected function getDelta() {
    return \Drupal::request()->query->get('index');
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $current_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"] ?? [];
    $field_collection = $values[0]["indexes"]["fields"]["field_collection"]["group"] ?? [];
    $dictionary_collection = $values[0]['dictionary_fields']["field_collection"]["group"] ?? [];
    $indexes = isset($values[0]["indexes"]) ? json_decode($values[0]["indexes"]) : [];

    $data_results = !empty($field_collection) ? [
      [
        "name" => $index_fields["name"] ?? '',
        "length" => isset($index_fields["length"]) ? (int) $index_fields["length"] : 0,
      ],
    ] : [];

    $updated = array_merge($current_fields ?? [], $data_results);
// @todo Add updates
    $json_data = [
      'identifier' => $values[0]['identifier'] ?? '',
      'data' => [
        'title' => $values[0]['title'] ?? '',
        'fields' => $dictionary_collection,
        'indexes' => $indexes,
      ],
    ];

    return json_encode($json_data);
  }

  /**
   * Prerender callback for the index field form.
   *
   * Moves the buttons into the table.
   */
  public function preRenderForm(array $dictionaryFields) {
    return IndexFieldOperations::setIndexFieldsAjaxElements($dictionaryFields);
  }

//  /**
//   * Prerender callback for the index field form.
//   *
//   * Moves the buttons into the table.
//   */
//  public function preRenderIndexFieldFormOnAdd(array $indexFields) {
//    return IndexFieldOperations::setIndexFieldsAjaxElementsOnAdd($indexFields);
//  }
//
//  /**
//   * {@inheritdoc}
//   */
//  public static function trustedCallbacks() {
//    return [
//      'preRenderForm',
//      'preRenderIndexFieldFormOnAdd',
//      'preRenderIndexFieldForm',
//      'preRenderIndexForm',
//    ];
//  }


  /**
   * @inheritDoc
   */
  protected function processDataResults($data_results, $current_fields, $field_values, $op) {
    $index_fields_results = $data_results['indexes'][$this->getDelta()]['fields'] ?? [];
    return IndexFieldOperations::processIndexFieldsDataResults($data_results, $current_fields, $field_values, $op);
  }

  protected function createGeneralFields($element, $field_json_metadata, $current_fields, $form_state) {
    $element['dictionary_fields'] = [
      '#type' => 'fieldset',
      '#title' => t('Fields'),
      '#prefix' => '<div id = field-json-metadata-index-fields>',
      '#suffix' => '</div>',
      '#markup' => t('<div class="claro-details__description">One or more fields included in index. Must be keys from the fields object.</div>'),
      '#required' => TRUE,
    ];

    $element['dictionary_fields']['current_dictionary_fields'] = $current_fields;

    return $element;
  }

  /**
   * @inheritDoc
   */
  protected function createDictionaryFieldOptions($op_index, $data_results, $fields_being_modified, $element) {
    $current_fields = $element['current_dictionary_fields'];
    // Creating ajax buttons/fields to be placed in correct location later.
    foreach ($data_results as $key => $data) {
      if (self::checkEditingField($key, $op_index, $fields_being_modified)) {
        $element['edit_fields'][$key] = IndexFieldEditCreation::editIndexFields($key, $current_fields, $fields_being_modified);
      }
      else {
        $element['edit_buttons'][$key]['edit_button'] = IndexFieldButtons::editIndexButtons($key);
      }
    }
    $element['add_row_button'] = IndexFieldButtons::addIndexButton();

    return $element;
  }

  /**
   * @inheritDoc
   */
  protected function createFieldOptions($op_index, $data_results, $fields_being_modified, $element) {
    // Creating ajax buttons/fields to be placed in correct location later.
    foreach ($data_results as $key => $data) {
      if (self::checkEditingField($key, $op_index, $fields_being_modified)) {
        $element['edit_fields'][$key] = IndexFieldEditCreation::editIndexFields($key, $fields_being_modified);
      }
      else {
        $element['edit_buttons'][$key]['edit_button'] = IndexFieldButtons::editIndexButtons($key);
      }
    }
    $element['add_row_button'] = IndexFieldButtons::addIndexButton();

    return $element;
  }

}
