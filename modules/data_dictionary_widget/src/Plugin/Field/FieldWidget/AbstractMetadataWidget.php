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
 */
abstract class AbstractMetadataWidget extends WidgetBase implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Retrieve form_state values to be used for various operations.
    $field_values = $form_state->get('new_dictionary_fields');
    $add_new_field = $form_state->get('add_new_field');
    $current_fields = $form_state->get('current_dictionary_fields');
    $fields_being_modified = $form_state->get('dictionary_fields_being_modified') ?? NULL;

    // Retrieve triggered element to be used for various operations.
    $op = $form_state->getTriggeringElement()['#op'] ?? NULL;
    // We split the key to get the index field location.
    $op_index = isset($op) ? explode('_', $op) : NULL;

    // Retrieve form element item values.
    $field_json_metadata = !empty($items[0]->value) ? json_decode($items[0]->value, TRUE) : [];

    // Retrieve initial data results from field JSON metadata.
    $data_results = $this->getFieldResults($field_json_metadata);

    // Process data results.
    $data_results = $this->processDataResults($data_results, $current_fields, $field_values, $op);

    // Create form elements.
    $element = $this->createGeneralFields($element, $field_json_metadata, $current_fields, $form_state);

    // Add pre-render functions.
    $element['dictionary_fields']['#pre_render'] = [[$this, 'preRenderForm']];

    // Add data rows to display in tables.
    $element['dictionary_fields']['data'] = $this->createDictionaryDataRows($current_fields, $data_results, $form_state);

    // Create dictionary fields/buttons for editing.
    $element['dictionary_fields'] = $this->createDictionaryFieldOptions($op_index, $data_results, $fields_being_modified, $element['dictionary_fields']);
    $element['dictionary_fields']['add_row_button']['#access'] = $fields_being_modified == NULL;

    // Get form entity.
    $form_object = $form_state->getFormObject();
    if (!($form_object instanceof EntityFormInterface)) {
      return NULL;
    }
    $form_entity = $form_object->getEntity();

    // Set form entity data type.
    if ($form_entity instanceof FieldableEntityInterface) {
      $form_entity->set('field_data_type', 'data-dictionary');
    }

    // Set form state for adding fields and indexes.
    $element = $this->setAddDictionaryFieldFormState($add_new_field, $element);
    $element = $this->editDictionaryFieldFormState($fields_being_modified, $element);

    return $element;
  }

  /**
   * Get field array from json metadata
   *
   * @param array $field_json_metadata
   *   The json metadata array
   *
   * @return array
   *   Array asociative array of field names and field values.
   */
  abstract protected function getFieldResults(array $field_json_metadata) : array;

  /**
   * Cleaning the data up.
   */
  abstract protected function processDataResults($data_results, $current_fields, $field_values, $op);

  abstract protected function createGeneralFields($element, $field_json_metadata, $current_fields, $form_state);

  /**
   * Create edit and update fields where needed.
   */
  /**
   * Set the elements associated with adding a new field.
   */
  abstract protected function createDictionaryFieldOptions($op_index, $data_results, $fields_being_modified, $element);

  abstract protected function setAddDictionaryFieldFormState($add_new_field, $element);

  /**
   * Set the elements associated with editing a dictionary field.
   */
  abstract protected function editDictionaryFieldFormState($fields_being_modified, $element);

  /**
   * Prerender callback for the dictionary form.
   *
   * Moves the buttons into the table.
   */
  abstract public function preRenderForm(array $dictionaryFields);

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return [
      'preRenderForm',
    ];
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
   * Return true if field is being edited.
   */
  protected static function checkEditingField($key, $op_index, $fields_being_modified) {
    $action_list = FieldOperations::editActions();
    if (isset($op_index[0]) && in_array($op_index[0], $action_list) && array_key_exists($key, $fields_being_modified)) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  abstract protected static function createDictionaryDataRows($current_dictionary_fields, $data_results, $form_state);

}
