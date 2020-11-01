<?php

namespace Drupal\json_form_widget;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\metastore\SchemaRetriever;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class JsonFormBuilder.
 */
class JsonFormBuilder implements ContainerInjectionInterface {
  use StringTranslationTrait;

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
  protected $schema;

  /**
   * String Helper.
   *
   * @var \Drupal\json_form_widget\JsonFormStringHelper
   */
  protected $stringHelper;

  /**
   * Object Helper.
   *
   * @var \Drupal\json_form_widget\JsonFormObjectHelper
   */
  protected $objectHelper;

  /**
   * Inherited.
   *
   * @{inheritdocs}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.metastore.schema_retriever'),
      $container->get('json_form.string_helper'),
      $container->get('json_form.object_helper')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(SchemaRetriever $schema_retriever, JsonFormStringHelper $string_helper, JsonFormObjectHelper $object_helper) {
    $this->schemaRetriever = $schema_retriever;
    $this->stringHelper = $string_helper;
    $this->objectHelper = $object_helper;
  }

  /**
   * Set schema.
   */
  public function setSchema($schema_name) {
    $schema = $this->schemaRetriever->retrieve($schema_name);
    $this->schema = json_decode($schema);
  }

  /**
   * Get schema.
   */
  public function getSchema($schema_name) {
    return $this->schema;
  }

  /**
   * Build form based on schema.
   */
  public function getJsonForm($data, $form_state = NULL) {
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
  public function getFormElement($type, $property_name, $property, $data, $object_schema = FALSE, $form_state = NULL) {
    switch ($type) {
      case 'object':
        return $this->objectHelper->handleObjectElement($property, $property_name, $data, $form_state, $this);

      case 'array':
        return $this->handleArrayElement($property, $property_name, $data, $form_state);

      case 'string':
        return $this->stringHelper->handleStringElement($property, $property_name, $data, $object_schema);
    }
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
   * Handle form element for an array.
   */
  public function handleArrayElement($property_schema, $field_name, $data, $form_state) {
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

    if (
      !isset($widget_array_info[$field_name]['removing'])
      && !isset($widget_array_info[$field_name]['adding'])
      && is_array($data)
    ) {
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
  public function getSingleArrayElement($field_name, $i, $property_schema, $data, $form_state) {
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
  public function getSingleSimpleArrayElement($field_name, $i, $property_schema, $data) {
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
  public function getSingleComplexArrayElement($field_name, $i, $property_schema, $data, $form_state) {
    $value = isset($data[$i]) ? $data[$i] : '';
    $element = $this->objectHelper->handleObjectElement($property_schema->items, $field_name, $value, $form_state, $this);
    return $element;
  }

}
