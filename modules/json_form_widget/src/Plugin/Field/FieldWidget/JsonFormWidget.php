<?php

namespace Drupal\json_form_widget\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\metastore\SchemaRetriever;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Plugin implementation of the 'field_example_text' widget.
 *
 * @FieldWidget(
 * id = "json_form_widget",
 * module = "json_form_widget",
 * label = @Translation("JSON Form"),
 * field_types = {
 * "string_long"
 * }
 * )
 */
class JsonFormWidget extends WidgetBase {

  /**
   * SchemaRetriever.
   *
   * @var \Drupal\metastore\SchemaRetriever
   */
  protected $schemaRetriever;

  /**
   * Schema.
   *
   * @var object
   */
  private $schema;

  /**
   * Constructs a WidgetBase object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\metastore\SchemaRetriever $schema_retriever
   *   Any third party settings.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, SchemaRetriever $schema_retriever) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->schemaRetriever = $schema_retriever;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('dkan.metastore.schema_retriever')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'schema' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element['schema'] = [
      '#type' => 'textfield',
      '#title' => $this->t('JSON Schema name'),
      '#default_value' => $this->getSetting('schema'),
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = $this->t('JSON Schema Name: @schema', ['@schema' => $this->getSetting('schema')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $default_data = [];
    // Get default data.
    foreach ($items as $item) {
      $default_data = json_decode($item->value);
    }
    $json_form = $this->getJsonForm($default_data);

    return ['value' => $json_form];
  }

  /**
   * Build form based on schema.
   */
  private function getJsonForm($data) {
    $schema = $this->schemaRetriever->retrieve($this->getSetting('schema'));
    $this->schema = json_decode($schema);
    if ($this->schema) {
      $properties = array_keys((array) $this->schema->properties);

      foreach ($properties as $property) {
        $type = $this->schema->properties->{$property}->type ?? "string";
        $form[$property] = $this->getFormElement($type, $property, $this->schema->properties->{$property}, $data->{$property});
      }
      return $form;
    }
  }

  /**
   * Get form element based on property type.
   */
  private function getFormElement($type, $property_name, $property, $data) {
    switch ($type) {
      case 'object':
        return $this->handleObjectElement($property, $property_name, $data);

      case 'array':
        return $this->handleArrayElement($property, $property_name, $data);

      case 'string':
        return $this->handleStringElement($property, $property_name, $data);
    }
  }

  /**
   * Handle form element for a string.
   */
  private function handleStringElement($property, $field_name, $data, $has_children = FALSE) {
    // Basic definition.
    $element = [
      '#type' => 'textfield',
      '#title' => $property->title,
      '#description' => $property->description,
    ];
    // Add default value.
    if ($data) {
      $element['#default_value'] = $data;
    }
    elseif ($property->default) {
      $element['#default_value'] = $property->default;
    }
    // Check if the field is required.
    $element_schema = $has_children ? $property : $this->schema;
    $element['#required'] = $this->checkIfRequired($field_name, $element_schema);
    // Convert to select if applicable.
    if ($property->enum) {
      $element['#type'] = 'select';
      $element['#options'] = $this->getSelectOptions($property);
    }
    // Convert to html5 URL render element if needed.
    if ($property->format == 'uri') {
      $element['#type'] = 'url';
    }
    return $element;
  }

  /**
   * Handle form element for an array.
   */
  private function handleArrayElement($type, $property) {
    // Handle array.
  }

  /**
   * Handle form element for an object.
   */
  private function handleObjectElement($type, $property) {
    // Handle object.
  }

  /**
   * Check if field is required based on its schema.
   */
  private function checkIfRequired($name, $element_schema) {
    if (in_array($name, $element_schema->required)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get array of options for a property.
   */
  private function getSelectOptions($property) {
    $options = [];
    if ($property->enumNames) {
      $options = array_combine($property->enum, $property->enumNames);
    }
    else {
      $options = array_combine($property->enum, $property->enum);
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function extractFormValues(FieldItemListInterface $items, array $form, FormStateInterface $form_state) {
    $field_name = $this->fieldDefinition->getName();
    $data = [];
    $properties = array_keys((array) $this->schema->properties);
    $values = $form_state->getValue($field_name)[0]['value'];
    foreach ($properties as $property) {
      $data[$property] = isset($values[$property]) ? $values[$property] : '';
    }
    $json = [json_encode($data)];
    $values = $this->massageFormValues($json, $form, $form_state);
    $items->setValue($values);
    $items->filterEmptyItems();

    $field_state = static::getWidgetState($form['#parents'], $field_name, $form_state);
    foreach ($items as $delta => $item) {
      $field_state['original_deltas'][$delta] = isset($item->_original_delta) ? $item->_original_delta : $delta;
      unset($item->_original_delta, $item->_weight);
    }
    static::setWidgetState($form['#parents'], $field_name, $form_state, $field_state);

  }

  /**
   * Validate the json field.
   */
  public static function validate($element, FormStateInterface $form_state) {
    // Validate.
  }

}
