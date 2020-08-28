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
        return $this->handleObjectElement($property, $property_name, $data);

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
      '#title' => $property->title,
    ];
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
      '#title' => $property_schema->title,
      '#description' => $property_schema->description,
      '#prefix' => '<div id="' . $field_name . '-fieldset-wrapper">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    ];

    for ($i = 0; $i < $amount; $i++) {
      $element[$field_name][$i] = [
        '#type' => 'textfield',
        '#title' => $property_schema->items->title,
      ];
      if (is_array($data) && isset($data[$i])) {
        $element[$field_name][$i]['#default_value'] = $data[$i];
      }
    }

    $element['actions'] = [
      '#type' => 'actions',
    ];
    $element['actions']['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add one more'),
      '#submit' => ['json_form_widget_add_one'],
      '#ajax' => [
        'callback' => [$this, 'addmoreCallback'],
        'wrapper' => $field_name . '-fieldset-wrapper',
      ],
    ];
    // If there is more than one name, add the remove button.
    if ($amount > 1) {
      $element['actions']['remove_name'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove one'),
        '#submit' => ['json_form_widget_remove_one'],
        '#ajax' => [
          'callback' => [$this, 'addmoreCallback'],
          'wrapper' => $field_name . '-fieldset-wrapper',
        ],
      ];
    }

    return $element;
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    $base_field = $form_state->get('json_form_widget_field');
    $widget_array_info = $form_state->get('json_form_widget_array');
    $field = array_keys($widget_array_info)[0];
    return $form[$base_field]['widget'][0]['value'][$field];
  }

  /**
   * Handle form element for an object.
   */
  private function handleObjectElement($property_schema, $field_name, $data) {
    $element[$field_name] = [
      '#type' => 'fieldset',
      '#title' => $property_schema->title,
      '#description' => $property_schema->description,
    ];
    $properties = array_keys((array) $property_schema->properties);

    foreach ($properties as $child) {
      $type = $property_schema->properties->{$child}->type ?? "string";
      $value = $data->{$child} ?? NULL;
      $element[$field_name][$child] = $this->getFormElement($type, $child, $property_schema->properties->{$child}, $value, $property_schema);
    }
    return $element;
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
      if (isset($values[$property])) {
        if (is_array($values[$property])) {
          $data[$property] = [];
          if (isset($values[$property][$property])) {
            foreach ($values[$property][$property] as $key => $value) {
              if ($value) {
                $data[$property][$key] = $value;
              }
            }
          }
        }
        elseif (!empty($values[$property])) {
          $data[$property] = $values[$property];
        }
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
   * Validate the json field.
   */
  public static function validate($element, FormStateInterface $form_state) {
    // Validate.
  }

}
