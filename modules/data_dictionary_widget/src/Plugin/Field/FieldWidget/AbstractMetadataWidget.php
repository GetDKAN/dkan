<?php

namespace Drupal\data_dictionary_widget\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\data_dictionary_widget\Fields\FieldOperations;
use Drupal\Core\Entity\EntityFormInterface;

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
    $data_results = $field_json_metadata['data'] ?? [];

    // Process data results.
    $data_results = $this->processDataResults($data_results, $current_fields, $field_values, $op);

    // Create form elements.
    $element = $this->createGeneralFields($element, $field_json_metadata, $current_fields, $form_state);

    // Add pre-render functions.
    $element['dictionary_fields']['#pre_render'] = [[$this, 'preRenderForm']];

    // Add data rows to display in tables.
    $element['dictionary_fields']['data'] = $this->createDataRows($current_fields, $data_results, $form_state);

    // Create dictionary fields/buttons for editing.
    $element['dictionary_fields'] = $this->createFieldOptions($op_index, $data_results, $fields_being_modified, $element['dictionary_fields']);
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

    // Set form state for adding and editing fields.
    $element = $this->setAddDictionaryFieldFormState($add_new_field, $element);
    if ($fields_being_modified) {
      unset($element['dictionary_fields']["edit_buttons"]);
    }
    return $element;
  }

  /**
   * Cleaning the data up.
   */
  abstract protected function processDataResults($data_results, $current_fields, $field_values, $op);

  abstract protected function createGeneralFields($element, $field_json_metadata, $current_fields, $form_state);

  protected function setAddDictionaryFieldFormState($add_new_field, $element) {
    if ($add_new_field) {
      unset($element['dictionary_fields']["edit_buttons"]);
      $element['dictionary_fields']['field_collection'] = $add_new_field;
      $element['dictionary_fields']['field_collection']['#access'] = TRUE;
      $element['dictionary_fields']['add_row_button']['#access'] = FALSE;
      $element['identifier']['#required'] = FALSE;
      $element['title']['#required'] = FALSE;
    }
    return $element;
  }

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
  abstract protected function createField(string $field, array $field_json_metadata, FormStateInterface &$form_state);

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

  abstract protected static function createDataRows($current_dictionary_fields, $data_results, $form_state);

  abstract protected function createFieldOptions($op_index, $data_results, $fields_being_modified, $element);

}
