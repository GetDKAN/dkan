<?php

namespace Drupal\json_form_widget\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\metastore\SchemaRetriever;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;

/**
 * Plugin implementation of the 'json_form_widget'.
 *
 * @FieldWidget(
 *   id = "json_form_widget",
 *   module = "json_form_widget",
 *   label = @Translation("JSON Form"),
 *   field_types = {
 *     "string_long"
 *   }
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
    $form_state->set('json_form_widget_field', $items->getName());
    $default_data = [];
    // Get default data.
    foreach ($items as $item) {
      $default_data = json_decode($item->value);
    }
    $json_form = $this->getJsonForm($default_data, $form_state);

    return ['value' => $json_form];
  }

  /**
   * Build form based on schema.
   */
  private function getJsonForm($data, $form_state = NULL) {
    $schema = $this->schemaRetriever->retrieve($this->getSetting('schema'));
    $this->schema = json_decode($schema);
    if ($this->schema) {
      $properties = array_keys((array) $this->schema->properties);

      foreach ($properties as $property) {
        $type = $this->schema->properties->{$property}->type ?? "string";
        $value = $data->{$property} ?? NULL;
        $form[$property] = $this->getFormElement($type, $property, $this->schema->properties->{$property}, $value, FALSE, $form_state);
      }
      return $form;
    }
  }

  /**
   * Get form element based on property type.
   */
  private function getFormElement($type, $property_name, $property, $data, $object_schema = FALSE, $form_state = NULL) {
    switch ($type) {
      case 'object':
        return $this->handleObjectElement($property, $property_name, $data, $form_state);

      case 'array':
        return $this->handleArrayElement($property, $property_name, $data, $form_state);

      case 'string':
        return $this->handleStringElement($property, $property_name, $data, $object_schema);
    }
  }

  /**
   * Handle form element for a string.
   */
  private function handleStringElement($property, $field_name, $data, $object_schema = FALSE) {
    // Basic definition.
    $element = [
      '#type' => 'textfield',
    ];
    if (isset($property->title)) {
      $element['#title'] = $property->title;
    }
    if (isset($property->description)) {
      $element['#description'] = $property->description;
    }
    // Add default value.
    if ($data) {
      $element['#default_value'] = $data;
    }
    elseif (isset($property->default)) {
      $element['#default_value'] = $property->default;
    }
    // Check if the field is required.
    $element_schema = $object_schema ? $object_schema : $this->schema;
    $element['#required'] = $this->checkIfRequired($field_name, $element_schema);
    // Convert to select if applicable.
    if (isset($property->enum)) {
      $element['#type'] = 'select';
      $element['#options'] = $this->getSelectOptions($property);
    }
    // Convert to html5 URL render element if needed.
    if (isset($property->format) && $property->format == 'uri') {
      $element['#type'] = 'url';
    }
    return $element;
  }

  /**
   * Handle form element for an array.
   */
  private function handleArrayElement($property_schema, $field_name, $data, $form_state) {
    // Save info about the arrays.
    $widget_array_info = $form_state->get('json_form_widget_array');
    $form_state->set('json_form_widget_schema', $this->schema);
    if (!isset($widget_array_info[$field_name])) {
      $widget_array_info[$field_name]['amount'] = 1;
      $form_state->set('json_form_widget_array', $widget_array_info);
      $amount = 1;
    }
    else {
      $amount = $widget_array_info[$field_name]['amount'];
    }

    if (!isset($widget_array_info[$field_name]['removing'])
      && !isset($widget_array_info[$field_name]['adding'])
      && is_array($data)) {
      $count = count($data);
      $amount = ($count > $amount) ? $count : $amount;
      $widget_array_info[$field_name]['amount'] = $count;
      $form_state->set('json_form_widget_array', $widget_array_info);
    }

    $element = [
      '#type' => 'fieldset',
      '#title' => $field_name,
      '#prefix' => '<div id="' . $field_name . '-fieldset-wrapper">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    ];

    if (isset($property_schema->title)) {
      $element['#title'] = $property_schema->title;
    }

    if (isset($property_schema->description)) {
      $element['#description'] = $property_schema->description;
    }

    for ($i = 0; $i < $amount; $i++) {
      $element[$field_name][$i] = $this->getSingleArrayElement($field_name, $i, $property_schema, $data, $form_state);
    }

    $element['actions'] = [
      '#type' => 'actions',
    ];
    $element['actions']['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add one more'),
      '#submit' => ['json_form_widget_add_one'],
      '#name' => $field_name,
      '#ajax' => [
        'callback' => [$this, 'addmoreCallback'],
        'wrapper' => $field_name . '-fieldset-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];
    // If there is more than one name, add the remove button.
    if ($amount > 1) {
      $element['actions']['remove_name'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove one'),
        '#submit' => ['json_form_widget_remove_one'],
        '#name' => $field_name,
        '#ajax' => [
          'callback' => [$this, 'addmoreCallback'],
          'wrapper' => $field_name . '-fieldset-wrapper',
        ],
        '#limit_validation_errors' => [],
      ];
    }

    return $element;
  }

  /**
   * Handle single element from array.
   *
   * Chooses wether element is simple or complex.
   */
  private function getSingleArrayElement($field_name, $i, $property_schema, $data, $form_state) {
    if (isset($property_schema->items->properties)) {
      // Return complex.
      return $this->getSingleComplexArrayElement($field_name, $i, $property_schema, $data, $form_state);
    }
    else {
      // Return simple.
      return $this->getSingleSimpleArrayElement($field_name, $i, $property_schema, $data);
    }
  }

  /**
   * Returns single simple element from array.
   */
  private function getSingleSimpleArrayElement($field_name, $i, $property_schema, $data) {
    $element = [
      '#type' => 'textfield',
    ];
    if (isset($property_schema->items->title)) {
      $element['#title'] = $property_schema->items->title;
    }
    if (is_array($data) && isset($data[$i])) {
      $element['#default_value'] = $data[$i];
    }
    return $element;
  }

  /**
   * Returns single complex element from array.
   */
  private function getSingleComplexArrayElement($field_name, $i, $property_schema, $data, $form_state) {
    $value = isset($data[$i]) ? $data[$i] : '';
    $element = $this->handleObjectElement($property_schema->items, $field_name, $value, $form_state);
    return $element;
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    $field = $form_state->getTriggeringElement();
    $element = $form;
    foreach ($field['#array_parents'] as $key => $parent) {
      $element = $element[$parent];
      if ($parent === $field['#name']) {
        break;
      }
    }
    return $element;
  }

  /**
   * Handle form element for an object.
   */
  private function handleObjectElement($property_schema, $field_name, $data, $form_state) {
    $element[$field_name] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $property_schema->title,
    ];
    if (isset($property_schema->description)) {
      $element['#description'] = $property_schema->description;
    }
    $properties = array_keys((array) $property_schema->properties);

    foreach ($properties as $child) {
      $type = $property_schema->properties->{$child}->type ?? "string";
      $value = $data->{$child} ?? NULL;
      $element[$field_name][$child] = $this->getFormElement($type, $child, $property_schema->properties->{$child}, $value, $property_schema, $form_state);
    }
    return $element;
  }

  /**
   * Check if field is required based on its schema.
   */
  private function checkIfRequired($name, $element_schema) {
    if (isset($element_schema->required) && in_array($name, $element_schema->required)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get array of options for a property.
   */
  private function getSelectOptions($property) {
    $options = [];
    if (isset($property->enumNames)) {
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
    $field_name = $form_state->get('json_form_widget_field');
    $schema = $form_state->get('json_form_widget_schema');

    $data = [];
    $properties = array_keys((array) $schema->properties);
    $values = $form_state->getValue($field_name)[0]['value'];
    foreach ($properties as $property) {
      $value = $this->flattenValues($values, $property, $schema->properties->{$property});
      if ($value) {
        $data[$property] = $value;
      }
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
   * Flatten values.
   */
  private function flattenValues($formValues, $property, $schema) {
    $data = [];

    switch ($schema->type) {
      case 'string':
        $data = $this->handleStringValues($formValues, $property);
        break;

      case 'object':
        $data = $this->handleObjectValues($formValues[$property][$property], $property, $schema);
        break;

      case 'array':
        $data = $this->handleArrayValues($formValues, $property, $schema);
        break;
    }
    return $data;
  }

  /**
   * Flatten values for string properties.
   */
  private function handleStringValues($formValues, $property) {
    if (!empty($formValues[$property])) {
      return $formValues[$property];
    }
    return FALSE;
  }

  /**
   * Flatten values for object properties.
   */
  private function handleObjectValues($formValues, $property, $schema) {
    $properties = array_keys((array) $schema->properties);

    $data = FALSE;
    if (isset($formValues)) {
      foreach ($properties as $sub_property) {
        $value = $this->flattenValues($formValues, $sub_property, $schema->properties->$sub_property);
        if ($value) {
          $data[$sub_property] = $value;
        }
      }
    }
    return $data;
  }

  /**
   * Flatten values for array properties.
   */
  private function handleArrayValues($formValues, $property, $schema) {
    $data = FALSE;
    $subschema = $schema->items;
    if ($subschema->type === "object") {
      foreach ($formValues[$property][$property] as $key => $item) {
        $value = $this->handleObjectValues($formValues[$property][$property][$key][$property], $property, $schema->items);
        if ($value) {
          $data[$key] = $value;
        }
      }
      return $data;
    }

    foreach ($formValues[$property][$property] as $key => $value) {
      if (!empty($value)) {
        $data[$key] = $value;
      }
    }
    return $data;
  }

}
