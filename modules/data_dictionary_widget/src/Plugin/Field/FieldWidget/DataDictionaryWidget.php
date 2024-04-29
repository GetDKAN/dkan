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
use Drupal\data_dictionary_widget\Indexes\IndexFieldCreation;
use Drupal\data_dictionary_widget\Indexes\IndexFieldOperations;

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
    $field_values = $form_state->get("new_dictionary_fields");
    $index_values = $form_state->get('new_index');
    $index_field_values = $form_state->get("new_index_fields");

    $current_fields = $form_state->get('current_dictionary_fields');
    $current_indexes = $form_state->get('current_index');
    $current_index_fields = $form_state->get('current_index_fields');

    $fields_being_modified = $form_state->get("fields_being_modified") ?? NULL;
    $index_fields_being_modified = $form_state->get("index_fields_being_modified") ?? NULL;

    $op = $form_state->getTriggeringElement()['#op'] ?? NULL;
    $field_json_metadata = !empty($items[0]->value) ? json_decode($items[0]->value, TRUE) : [];
    $op_index = isset($form_state->getTriggeringElement()['#op']) ? explode("_", $form_state->getTriggeringElement()['#op']) : NULL;

    $data_results = $field_json_metadata ? $field_json_metadata["data"]["fields"] : [];
    $index_data_results = $field_json_metadata ? $field_json_metadata["data"]["indexes"][0]["fields"] : [];

    // Build the data_results array to display the rows in the data table.
    $data_results = FieldOperations::processDataResults($data_results, $current_fields, $field_values, $op);

    // Build the index_data_results array to display the rows in the data table.
    $index_data_results = IndexFieldOperations::processIndexDataResults($index_data_results, $current_index_fields, $index_field_values, $op);

    $element = FieldCreation::createGeneralFields($element, $field_json_metadata, $current_fields, $form_state);
    $element = IndexFieldCreation::createGeneralIndex($element, $field_json_metadata, $current_indexes, $form_state);
    
    if ($index_field_values || $current_index_fields) {
      $element = IndexFieldCreation::createGeneralIndexFields($element, $field_json_metadata, $current_index_fields, $form_state->get('add_new_index'), $form_state);
    }

    $element['dictionary_fields']['#pre_render'] = [
      [$this, 'preRenderForm'],
    ];

    $element['indexes']['#pre_render'] = [
      [$this, 'preRenderIndexForm'],
    ];

    $element['indexes']['index_fields']['#pre_render'] = [
      [$this, 'preRenderIndexFieldForm'],
    ];

    $element['dictionary_fields']['data'] = FieldCreation::createDictionaryDataRows($current_fields, $data_results, $form_state);
    $element['indexes']['data'] = IndexFieldCreation::createIndexDataRows($current_indexes, $index_data_results, $form_state);
    $element['indexes']['index_fields']['data'] = IndexFieldCreation::createIndexFieldsDataRows($current_index_fields, $index_data_results, $form_state);

    // Creating ajax buttons/fields to be placed in correct location later.
    $element['dictionary_fields'] = FieldOperations::createDictionaryFieldOptions($op_index, $data_results, $fields_being_modified, $element['dictionary_fields']);
    $element['dictionary_fields']['add_row_button']['#access'] = $fields_being_modified == NULL ? TRUE : FALSE;

    // Creating ajax buttons/fields to be placed in correct location later for index fields.
    $element['indexes'] = IndexFieldOperations::createDictionaryIndexOptions($op_index, $index_data_results, $index_fields_being_modified, $element['indexes']);

    $element["indexes"]["index_fields"] = IndexFieldOperations::createDictionaryIndexFieldOptions($op_index, $index_data_results, $index_fields_being_modified, $element['indexes']['index_fields']);
    $element['indexes']['index_fields']['add_row_button']['#access'] = $index_field_values ? TRUE : FALSE;

    $form_object = $form_state->getFormObject();
    if (!($form_object instanceof EntityFormInterface)) {
      return;
    }
    $form_entity = $form_object->getEntity();

    if ($form_entity instanceof FieldableEntityInterface) {
      $form_entity->set('field_data_type', 'data-dictionary');
    }
    $element = FieldOperations::setAddFormState($form_state->get('add_new_field'), $element);
    $element = IndexFieldOperations::setAddIndexFormState($form_state->get('add_new_index'), $element);
    $element = IndexFieldOperations::setAddIndexFieldFormState($form_state->get('add_new_index_field'), $element);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $current_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"];
    $current_indexes = $form["field_json_metadata"]["widget"][0]["indexes"]["index_fields"]["data"]["#rows"];
    //$current_indexes = isset($values[0]["indexes"]) ? json_decode($values[0]["indexes"]) : NULL;

    $field_collection = $values[0]['dictionary_fields']["field_collection"]["group"] ?? [];
    $indexes_collection = $values[0]["indexes"]["index_fields"]["field_collection"]["group"] ?? [];

    $fields_input = !empty($field_collection) ? [
      [
        "name" => $field_collection["name"],
        "title" => $field_collection["title"],
        "type" => $field_collection["type"],
        "format" => $field_collection["format"],
        "description" => $field_collection["description"],
      ],
    ] : [];

    $index_inputs = !empty($indexes_collection) ? [
      [
        "name" => $indexes_collection["name"],
        "length" => (int)$indexes_collection["length"],
      ],
    ] : [];

    if (isset($fields_input)) {
      $fields = array_merge($current_fields ?? [], $fields_input);
    }
    else {
      $fields = $current_fields ?? [];
    }


    //$fields = array_merge($current_fields ?? [], $fields_input);
    $indexes = array_merge($current_indexes ?? [], $index_inputs);

    $json_data = [
      'identifier' => $values[0]['identifier'] ?? '',
      'data' => [
        'title' => $values[0]['title'] ?? '',
        'fields' => $fields,
        'indexes' => [
          [
            'fields' => $indexes
          ]
          ],
      ],
    ];

    $test = json_encode($json_data);

    return $test;
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
  public function preRenderIndexFieldForm(array $dictionaryIndexFields) {
    return IndexFieldOperations::setIndexFieldsAjaxElements($dictionaryIndexFields);
  }

  /**
   * Prerender callback for the index form.
   *
   * Moves the buttons into the table.
   */
  public function preRenderIndexForm(array $dictionaryIndexes) {
    return IndexFieldOperations::setIndexAjaxElements($dictionaryIndexes);
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['preRenderForm', 'preRenderIndexFieldForm', 'preRenderIndexForm'];
  }

}
