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
use Drupal\data_dictionary_widget\Indexes\IndexCreation;
use Drupal\data_dictionary_widget\Indexes\IndexOperations;
use PHPUnit\Framework\Constraint\IsTrue;

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
    $index_added = $form_state->get("index_added");
    $adding_new_index_fields = $form_state->get('adding_new_index_fields');

    $current_fields = $form_state->get('current_dictionary_fields');
    $current_indexes = $form_state->get('current_index');
    $current_index_fields = $form_state->get('current_index_fields');

    $fields_being_modified = $form_state->get("dictionary_fields_being_modified") ?? NULL;
    $index_being_modified = $form_state->get("index_being_modified") ?? NULL;
    $index_fields_being_modified = $form_state->get("index_fields_being_modified") ?? NULL;

    $op = $form_state->getTriggeringElement()['#op'] ?? NULL;
    $field_json_metadata = !empty($items[0]->value) ? json_decode($items[0]->value, TRUE) : [];
    $op_index = isset($form_state->getTriggeringElement()['#op']) ? explode("_", $form_state->getTriggeringElement()['#op']) : NULL;

    $data_results = $field_json_metadata ? $field_json_metadata["data"]["fields"] : [];
    
    // Combine current and new index fields
    $index_fields_results = $field_json_metadata ? $field_json_metadata["data"]["indexes"][0]["fields"] : [];
    $index_results = $field_json_metadata ? $field_json_metadata["data"]["indexes"] : [];


    // Build the data_results array to display the rows in the data table.
    $data_results = FieldOperations::processDataResults($data_results, $current_fields, $field_values, $op);

    // Build the index_field_data_results array to display the rows in the data table.
    $index_fields_data_results = IndexOperations::processIndexFieldsDataResults($index_fields_results, $current_index_fields, $index_field_values, $op);

    // Build the index_data_results array to display the rows in the data table.
    $index_data_results = IndexOperations::processIndexDataResults($index_results, $current_indexes, $index_values, $index_fields_data_results, $op);

    // if ($index_data_results) {
    //   unset($current_index_fields);
    // }

    $element = FieldCreation::createGeneralFields($element, $field_json_metadata, $current_fields, $form_state);
    $element = IndexCreation::createGeneralIndex($element, $field_json_metadata, $current_indexes, $form_state);
    
    if ($index_field_values || $current_index_fields) {
      $element = IndexCreation::createGeneralIndexFields($element, $field_json_metadata, $current_index_fields, $form_state->get('add_new_index'), $form_state);
    }

    $element['dictionary_fields']['#pre_render'] = [
      [$this, 'preRenderForm'],
    ];

    $element['indexes']['#pre_render'] = [
      [$this, 'preRenderIndexForm'],
    ];

    $element['indexes']['fields']['#pre_render'] = [
      [$this, 'preRenderIndexFieldForm'],
    ];

    $element['dictionary_fields']['data'] = FieldCreation::createDictionaryDataRows($current_fields, $data_results, $form_state);
    //$element["indexes"]["data"]["#rows"][0]["index_fields"];
    //$element['indexes'][] = $index_data_results;
    $element['indexes']['data'] = IndexCreation::createIndexDataRows($current_indexes, $index_data_results, $form_state);
    $element['indexes']['fields']['data'] = IndexCreation::createIndexFieldsDataRows($index_added, $adding_new_index_fields, $index_field_values, $index_values, $current_index_fields, $index_fields_data_results, $index_data_results, $form_state);

    // Creating ajax buttons/fields to be placed in correct location later.
    $element['dictionary_fields'] = FieldOperations::createDictionaryFieldOptions($op_index, $data_results, $fields_being_modified, $element['dictionary_fields']);
    $element['dictionary_fields']['add_row_button']['#access'] = $fields_being_modified == NULL ? TRUE : FALSE;

    // Creating ajax buttons/fields to be placed in correct location later for index fields.
    $element['indexes'] = IndexOperations::createDictionaryIndexOptions($op_index, $index_data_results, $index_fields_being_modified, $element['indexes']);

    if ($index_field_values || $current_index_fields) {
      $element["indexes"]["fields"] = IndexOperations::createDictionaryIndexFieldOptions($op_index, $index_fields_data_results, $index_fields_being_modified, $element['indexes']['fields']);
    }
    $element['indexes']['fields']['add_row_button']['#access'] = $index_field_values ? TRUE : FALSE;
    
    $form_object = $form_state->getFormObject();
    if (!($form_object instanceof EntityFormInterface)) {
      return;
    }
    $form_entity = $form_object->getEntity();

    if ($form_entity instanceof FieldableEntityInterface) {
      $form_entity->set('field_data_type', 'data-dictionary');
    }
    $element = FieldOperations::setAddFormState($form_state->get('add_new_field'), $element);
    
    
    $element = IndexOperations::setAddIndexFormState($form_state->get('add_new_index'), $element);

    //if (empty($element['indexes'])) {
      $element = IndexOperations::setAddIndexFieldFormState($form_state->get('add_new_index_field'), $form_state->get('add_index_field'), $element);
    //}

    // if ($form_state->get('add_new_index_field') || $form_state->get('new_index_fields')) {
    //   $element['indexes']['index_fields']['#access'] = TRUE;
    // } else {
    //   $element['indexes']['index_fields']['#access'] = FALSE;
    // }

    if ($form_state->get('add_new_index_field') || $form_state->get('new_index_fields')) {
      $element['indexes']['fields']['#access'] = TRUE;
    } else {
      $element['indexes']['fields']['#access'] = FALSE;
    }

    //$element['indexes']['fields']['#access'] = TRUE;

    // if ($adding_new_index_fields ||) {
    //   $element['indexes']['fields']['#access'] = TRUE;
    // }
    


    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $current_fields = $form["field_json_metadata"]["widget"][0]["dictionary_fields"]["data"]["#rows"];
    $current_indexes = $form["field_json_metadata"]["widget"][0]["indexes"]["data"]["#rows"];
    //$current_indexes = isset($values[0]["indexes"]) ? json_decode($values[0]["indexes"]) : NULL;

    $field_collection = $values[0]['dictionary_fields']["field_collection"]["group"] ?? [];
    $indexes_collection = $values[0]["indexes"]["fields"]["field_collection"]["group"] ?? [];

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
        "name" => $indexes_collection["indexes"]["fields"]["name"],
        "length" => (int)$indexes_collection["indexes"]["fields"]["length"],
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
        'indexes' => $indexes,
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
   * Prerender callback for the index Field form.
   *
   * Moves the buttons into the table.
   */
  public function preRenderIndexFieldForm(array $dictionaryIndexFields) {
    return IndexOperations::setIndexFieldsAjaxElements($dictionaryIndexFields);
  }

  /**
   * Prerender callback for the index form.
   *
   * Moves the buttons into the table.
   */
  public function preRenderIndexForm(array $dictionaryIndexes) {
    return IndexOperations::setIndexAjaxElements($dictionaryIndexes);
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['preRenderForm', 'preRenderIndexFieldForm', 'preRenderIndexForm'];
  }

}
