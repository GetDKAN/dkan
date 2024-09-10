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
    // Retrieve data-dictionary form_state values to be used for various
    // operations.
    $dictionary_field_values = $form_state->get('new_dictionary_fields');
    $add_new_dictionary_field = $form_state->get('add_new_field');
    $current_dictionary_fields = $form_state->get('current_dictionary_fields');
    $dictionary_fields_being_modified = $form_state->get('dictionary_fields_being_modified') ?? NULL;

    // Retrieve indexes form_state values to be used for various operations.
    $current_indexes = $form_state->get('current_index');
    $index_values = $form_state->get('new_index');
    $add_new_index = $form_state->get('add_new_index');
    $index_being_modified = $form_state->get('index_being_modified') ?? NULL;

    // Retrieve index fields form_state values to be used for various
    // operations.
    $current_index_fields = $form_state->get('current_index_fields');
    $index_field_values = $form_state->get('new_index_fields');
    $add_index_field = $form_state->get('add_new_index_field');
    $index_fields_being_modified = $form_state->get('index_fields_being_modified') ?? NULL;

    // Retrieve triggered element to be used for various operations.
    $op = $form_state->getTriggeringElement()['#op'] ?? NULL;
    $op_index = isset($op) ? explode('_', $op) : NULL;

    // Retrieve form element item values.
    $field_json_metadata = !empty($items[0]->value) ? json_decode($items[0]->value, TRUE) : [];

    // Retrieve initial data results from field JSON metadata.
    $data_results = $field_json_metadata['data']['fields'] ?? [];
    $index_fields_results = $field_json_metadata['data']['indexes'][0]['fields'] ?? [];
    $index_results = $field_json_metadata['data']['indexes'] ?? [];

    // Process data results.
    $data_results = FieldOperations::processDataResults($data_results, $current_dictionary_fields, $dictionary_field_values, $op);
    $index_fields_data_results = IndexFieldOperations::processIndexFieldsDataResults($index_fields_results, $current_index_fields, $index_field_values, $op);
    $index_data_results = IndexFieldOperations::processIndexDataResults($index_results, $current_indexes, $index_values, $index_fields_data_results, $op);

    // Create form elements.
    $element = FieldCreation::createGeneralFields($element, $field_json_metadata, $current_dictionary_fields, $form_state);
    $element = IndexFieldCreation::createGeneralIndex($element, $current_indexes);
    if ($index_field_values || $add_index_field || $index_fields_being_modified) {
      $element = IndexFieldCreation::createGeneralIndexFields($element);
    }

    // Add pre-render functions.
    $element['dictionary_fields']['#pre_render'] = [[$this, 'preRenderForm']];
    $element['indexes']['#pre_render'] = [[$this, 'preRenderIndexForm']];
    $element['indexes']['fields']['#pre_render'] = [
      [
        $this,
        'preRenderIndexFieldFormOnAdd',
      ],
    ];

    // Add data rows to display in tables.
    $element['dictionary_fields']['data'] = FieldCreation::createDictionaryDataRows($current_dictionary_fields, $data_results, $form_state);
    $element['indexes']['data'] = IndexFieldCreation::createIndexDataRows($current_indexes, $index_data_results, $form_state);
    $element['indexes']['fields']['data'] = IndexFieldCreation::createIndexFieldsDataRows($index_field_values, $current_index_fields, $index_fields_data_results, $form_state);

    // Create dictionary fields/buttons for editing.
    $element['dictionary_fields'] = FieldOperations::createDictionaryFieldOptions($op_index, $data_results, $dictionary_fields_being_modified, $element['dictionary_fields']);
    $element['dictionary_fields']['add_row_button']['#access'] = $dictionary_fields_being_modified == NULL;

    // Create index fields/buttons for editing.
    $element['indexes'] = IndexFieldOperations::createIndexOptions($op_index, $index_data_results, $index_being_modified, $element['indexes'], $form_state);

    // Create edit buttons and fields for index fields.
    if ($index_field_values || $current_index_fields) {
      $element['indexes']['fields'] = IndexFieldOperations::createIndexFieldOptions($op_index, $index_fields_data_results, $index_fields_being_modified, $element['indexes']['fields']);
    }

    // Set access to index fields Add button when index fields being modified.
    $element['indexes']['add_row_button']['#access'] = $index_being_modified == NULL;

    // Get form entity.
    $form_object = $form_state->getFormObject();
    if (!($form_object instanceof EntityFormInterface)) {
      return;
    }
    $form_entity = $form_object->getEntity();

    // Set form entity data type.
    if ($form_entity instanceof FieldableEntityInterface) {
      $form_entity->set('field_data_type', 'data-dictionary');
    }

    // Set form state for adding fields and indexes.
    $element = FieldOperations::setAddDictionaryFieldFormState($add_new_dictionary_field, $element);
    $element = FieldOperations::editDictionaryFieldFormState($dictionary_fields_being_modified, $element);
    $element = IndexFieldOperations::setAddIndexFormState($add_new_index, $element);
    $element = IndexFieldOperations::setAddIndexFieldFormState($add_index_field, $element);
    $element = IndexFieldOperations::editIndexFormState($index_being_modified, $element);

    // Display index fields only when new index fields are being created.
    if ($add_index_field || $index_field_values) {
      $element['indexes']['fields']['#access'] = TRUE;
    }
    else {
      $element['indexes']['fields']['#access'] = FALSE;
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $current_dictionary_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"] ?? [];
    $current_indexes = $form["field_json_metadata"]["widget"][0]["indexes"]["data"]["#rows"] ?? [];
    $field_collection = $values[0]['dictionary_fields']["field_collection"]["group"] ?? [];
    $indexes_collection = $values[0]["indexes"]["fields"]["field_collection"]["group"] ?? [];

    if (!empty($indexes_collection) && !empty($indexes_collection["indexes"]["fields"])) {
      $index_fields = $indexes_collection["indexes"]["fields"];
    }

    $dictionary_fields_input = !empty($field_collection) ? [
      [
        "name" => $field_collection["name"] ?? '',
        "title" => $field_collection["title"] ?? '',
        "type" => $field_collection["type"] ?? '',
        "format" => $field_collection["format"] ?? '',
        "description" => $field_collection["description"] ?? '',
      ],
    ] : [];

    $index_inputs = !empty($indexes_collection) ? [
      [
        "name" => $index_fields["name"] ?? '',
        "length" => isset($index_fields["length"]) ? (int) $index_fields["length"] : 0,
      ],
    ] : [];

    $dictionary_fields = array_merge($current_dictionary_fields ?? [], $dictionary_fields_input);
    $indexes = array_merge($current_indexes ?? [], $index_inputs);

    $json_data = [
      'identifier' => $values[0]['identifier'] ?? '',
      'data' => [
        'title' => $values[0]['title'] ?? '',
        'fields' => $dictionary_fields,
        'indexes' => $indexes,
      ],
    ];

    return json_encode($json_data);
  }

  /**
   * Prerender callback for the dictionary form.
   *
   * Moves the buttons into the table.
   */
  public function preRenderForm(array $dictionaryFields) {
    return FieldOperations::setAjaxElements($dictionaryFields);
  }

  /**
   * Prerender callback for the index field form.
   *
   * Moves the buttons into the table.
   */
  public function preRenderIndexFieldForm(array $indexFields) {
    return IndexFieldOperations::setIndexFieldsAjaxElements($indexFields);
  }

  /**
   * Prerender callback for the index field form.
   *
   * Moves the buttons into the table.
   */
  public function preRenderIndexFieldFormOnAdd(array $indexFields) {
    return IndexFieldOperations::setIndexFieldsAjaxElementsOnAdd($indexFields);
  }

  /**
   * Prerender callback for the index form.
   *
   * Moves the buttons into the table.
   */
  public function preRenderIndexForm(array $indexes) {
    return IndexFieldOperations::setIndexAjaxElements($indexes);
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return [
      'preRenderForm',
      'preRenderIndexFieldFormOnAdd',
      'preRenderIndexFieldForm',
      'preRenderIndexForm',
    ];
  }

}
