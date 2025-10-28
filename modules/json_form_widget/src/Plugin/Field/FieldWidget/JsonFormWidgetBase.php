<?php

namespace Drupal\json_form_widget\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\json_form_widget\FormBuilder;
use Drupal\json_form_widget\ValueHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for JSON Form widgets.
 *
 * @codeCoverageIgnore
 */
abstract class JsonFormWidgetBase extends WidgetBase implements WidgetInterface {

  /**
   * Form builder service.
   */
  protected FormBuilder $builder;

  /**
   * ValueHandler service.
   */
  protected ValueHandler $valueHandler;

  /**
   * Constructs a JsonFormWidget object.
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
   * @param \Drupal\json_form_widget\FormBuilder $builder
   *   The JsonFormBuilder service.
   * @param \Drupal\json_form_widget\ValueHandler $value_handler
   *   The JsonFormValueHandler service.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    FormBuilder $builder,
    ValueHandler $value_handler,
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->builder = $builder;
    $this->valueHandler = $value_handler;
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
      $container->get('json_form.builder'),
      $container->get('json_form.value_handler'),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function handlesMultipleValues() {
    // This widget does not support multiple values.
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $form_state->set('json_form_widget_field', $items->getName());
    $default_data = [];
    // Get default data. There should only be one item.
    if (isset($items[0]) && $items[0]->value) {
      $default_data = json_decode($items[0]->value);
    }

    // Each extension will implement its own schema retrieval logic.
    $schema = $this->resolveSchema($form_state);
    $ui_schema = $this->resolveUiSchema($form_state);
    // Set the schema for the form builder.
    $this->builder->setSchema($schema, $ui_schema);

    // Attempt to build the form.
    $json_form = $this->builder->getJsonForm($default_data, $form_state);
    if ($json_form) {
      return ['value' => $json_form];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function extractFormValues(FieldItemListInterface $items, array $form, FormStateInterface $form_state) {
    $schema = $this->resolveSchema($form_state);
    $this->builder->setSchema($schema);

    // @todo Use $this->fieldDefinition->getName() instead.
    $field_name = $form_state->get('json_form_widget_field');
    $data = [];
    $properties = array_keys((array) $schema->properties);
    $values = $form_state->getValue($field_name)[0]['value'];

    foreach ($properties as $property) {
      $value = $this->valueHandler->flattenValues($values, $property, $schema->properties->{$property});
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
      $field_state['original_deltas'][$delta] = $item->_original_delta ?? $delta;
      unset($item->_original_delta, $item->_weight);
    }
    static::setWidgetState($form['#parents'], $field_name, $form_state, $field_state);
  }

  /**
   * Get the JSON schema for the form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object, in case needed by the implementation.
   *
   * @return object
   *   The JSON schema, as decoded JSON stdClass object.
   */
  abstract protected function resolveSchema(FormStateInterface $form_state): object;

  /**
   * Get the UI JSON schema for the form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object, in case needed by the implementation.
   *
   * @return object|null
   *   The UI schema, or NULL if none provided.
   */
  abstract protected function resolveUiSchema(FormStateInterface $form_state): ?object;

}
