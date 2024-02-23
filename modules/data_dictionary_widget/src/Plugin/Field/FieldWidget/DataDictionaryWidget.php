<?php

namespace Drupal\data_dictionary_widget\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\data_dictionary_widget\Controller\Widget\FieldCreation;
use Drupal\data_dictionary_widget\Controller\Widget\FieldOperations;
use Drupal\data_dictionary_widget\Controller\Widget\DictionaryIndexes\IndexFieldCreation;
use Drupal\data_dictionary_widget\Controller\Widget\DictionaryIndexes\IndexFieldOperations;

/**
 * A data-dictionary widget.
 *
 * @FieldWidget(
 *   id = "data_dictionary_widget",
 *   label = @Translation("Data-Dictionary Widget"),
 *   field_types = {
 *     "string_long"
 *   }
 * )
 */
class DataDictionaryWidget extends WidgetBase implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $field_values = $form_state->get("new_fields");
    $index_field_values = $form_state->get("new_index_fields");

    $current_fields = $form_state->get('current_fields');
    $current_index_fields = $form_state->get('current_index_fields');

    $fields_being_modified = $form_state->get("fields_being_modified") ?? NULL;
    $index_fields_being_modified = $form_state->get("index_fields_being_modified") ?? NULL;

    $op = $form_state->getTriggeringElement()['#op'] ?? NULL;
    $field_json_metadata = !empty($items[0]->value) ? json_decode($items[0]->value, TRUE) : [];
    $op_index = isset($form_state->getTriggeringElement()['#op']) ? explode("_", $form_state->getTriggeringElement()['#op']) : NULL;

    $data_results = $field_json_metadata ? $field_json_metadata["data"]["fields"] : [];
    $index_data_results = $field_json_metadata ? $field_json_metadata["data"]["fields"] : [];

    // Build the data_results array to display the rows in the data table.
    $data_results = FieldOperations::processDataResults($data_results, $current_fields, $field_values, $op);

    // Build the index_data_results array to display the rows in the data table.
    $index_data_results = IndexFieldOperations::processIndexDataResults($index_data_results, $current_index_fields, $index_field_values, $op);

    $element = FieldCreation::createGeneralFields($element, $field_json_metadata, $current_fields, $fields_being_modified);

    $element = IndexFieldCreation::createGeneralIndexFields($element, $field_json_metadata, $current_index_fields, $index_fields_being_modified);

    $element['dictionary_fields']['#pre_render'] = [
      [$this, 'preRenderForm'],
    ];

    $element['index_fields']['#pre_render'] = [
      [$this, 'preRenderIndexForm'],
    ];

    $element['dictionary_fields']['data'] = FieldCreation::createDictionaryDataRows($current_fields, $data_results, $form_state);

    // Creating ajax buttons/fields to be placed in correct location later.
    $element['dictionary_fields'] = FieldOperations::createDictionaryFieldOptions($op_index, $data_results, $fields_being_modified, $element['dictionary_fields']);
    $element['dictionary_fields']['add_row_button']['#access'] = $fields_being_modified == NULL ? TRUE : FALSE;

    // Creating ajax buttons/fields to be placed in correct location later for index fields.
    $element['index_fields'] = IndexFieldOperations::createDictionaryIndexFieldOptions($op_index, $data_results, $index_fields_being_modified, $element['index_fields']);
    $element['index_fields']['add_row_button']['#access'] = $index_fields_being_modified == NULL ? TRUE : FALSE;

    $form_entity = $form_state->getFormObject()->getEntity();

    if ($form_entity instanceof FieldableEntityInterface) {
      $form_entity->set('field_data_type', 'data-dictionary');
    }
    $element = FieldOperations::setAddFormState($form_state->get('add_new_field'), $element);
    $element = IndexFieldOperations::setAddIndexFormState($form_state->get('add_new_index_field'), $element);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $current_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"];
    $field_collection = $values[0]['dictionary_fields']["field_collection"]["group"] ?? [];
    if (!empty($field_collection)) {
      $data_results = [
        [
          "name" => $field_collection["name"],
          "title" => $field_collection["title"],
          "type" => $field_collection["type"],
          "format" => $field_collection["format"],
          "description" => $field_collection["description"],
        ],
      ];
      $updated = array_merge($current_fields ?? [], $data_results);
    }
    else {
      $updated = $current_fields ?? [];
    }

    $json_data = [
      'identifier' => $values[0]['identifier'] ?? '',
      'title' => $values[0]['title'] ?? '',
      'data' => [
        'fields' => $updated,
      ],
    ];

    return json_encode($json_data);
  }

  /**
   * Prerender callback for the form.
   *
   * Moves the buttons into the table.
   */
  public function preRenderForm(array $dictionaryFields) {
    return FieldOperations::setAjaxElements($dictionaryFields);
  }

  /**
   * Prerender callback for the index form.
   *
   * Moves the buttons into the table.
   */
  public function preRenderIndexForm(array $dictionaryIndexFields) {
    return IndexFieldOperations::setIndexAjaxElements($dictionaryIndexFields);
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['preRenderForm', 'preRenderIndexForm'];
  }

}
