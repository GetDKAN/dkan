<?php

namespace Drupal\json_form_widget\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\json_form_widget\FormBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\json_form_widget\ValueHandler;

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
 *
 * @codeCoverageIgnore
 */
class JsonFormWidget extends WidgetBase {

  /**
   * FormBuilder.
   *
   * @var \Drupal\json_form_widget\FormBuilder
   */
  protected $builder;

  /**
   * ValueHandler.
   *
   * @var \Drupal\json_form_widget\ValueHandler
   */
  protected $valueHandler;

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
   * @param \Drupal\json_form_widget\FormBuilder $builder
   *   The JsonFormBuilder service.
   * @param \Drupal\json_form_widget\ValueHandler $value_handler
   *   The JsonFormValueHandler service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, FormBuilder $builder, ValueHandler $value_handler) {
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
      $container->get('json_form.value_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'schema' => 'dataset',
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
    $type = $form_state->getformObject()->getEntity()->get('field_data_type')->value;
    $type = isset($type) ? $type : $this->getSetting('schema');
    $this->builder->setSchema($this->getSetting('schema'), $type);
    $this->schema = $this->builder->getSchema();
    $json_form = $this->builder->getJsonForm($default_data, $form_state);

    if ($json_form) {
      return ['value' => $json_form];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function extractFormValues(FieldItemListInterface $items, array $form, FormStateInterface $form_state) {
    $field_name = $form_state->get('json_form_widget_field');
    // TODO: there is duplicated code here.
    $type = $form_state->getformObject()->getEntity()->get('field_data_type')->value;
    $type = isset($type) ? $type : $this->getSetting('schema');
    $this->builder->setSchema($this->getSetting('schema'), $type);
    $schema = $this->builder->getSchema();

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
      $field_state['original_deltas'][$delta] = isset($item->_original_delta) ? $item->_original_delta : $delta;
      unset($item->_original_delta, $item->_weight);
    }
    static::setWidgetState($form['#parents'], $field_name, $form_state, $field_state);
  }

}
