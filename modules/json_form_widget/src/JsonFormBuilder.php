<?php

namespace Drupal\json_form_widget;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
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
  public $schema;

  /**
   * Schema.
   *
   * @var object
   */
  public $schemaUi;

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
   * Array Helper.
   *
   * @var \Drupal\json_form_widget\JsonFormArrayHelper
   */
  protected $arrayHelper;

  /**
   * Inherited.
   *
   * @{inheritdocs}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.metastore.schema_retriever'),
      $container->get('json_form.string_helper'),
      $container->get('json_form.object_helper'),
      $container->get('json_form.array_helper')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(SchemaRetriever $schema_retriever, JsonFormStringHelper $string_helper, JsonFormObjectHelper $object_helper, JsonFormArrayHelper $array_helper) {
    $this->schemaRetriever = $schema_retriever;
    $this->stringHelper = $string_helper;
    $this->objectHelper = $object_helper;
    $this->arrayHelper = $array_helper;

    $this->arrayHelper->setBuilder($this);
    $this->stringHelper->setBuilder($this);
  }

  /**
   * Set schema.
   */
  public function setSchema($schema_name) {
    $schema = $this->schemaRetriever->retrieve($schema_name);
    $this->schema = json_decode($schema);
    $this->setSchemaUi($schema_name);
  }

  /**
   * Set schema.
   */
  public function setSchemaUi($schema_name) {
    $schema_ui = $this->schemaRetriever->retrieve($schema_name . '.ui');
    // Fix if there isn't a schema ui.
    $this->schemaUi = json_decode($schema_ui);
  }

  /**
   * Get schema.
   */
  public function getSchema($schema_name) {
    return $this->schema;
  }

  /**
   * Get schema UI.
   */
  public function getSchemaUi($schema_name) {
    return $this->schema_ui;
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
      if ($this->schemaUi) {
        $form_updated = $this->applySchemaUi($form);
        return $form_updated;
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
        return $this->arrayHelper->handleArrayElement($property, $property_name, $data, $form_state);

      case 'string':
        return $this->stringHelper->handleStringElement($property, $property_name, $data, $object_schema);
    }
  }

  /**
   * Apply schema UI to form.
   */
  public function applySchemaUi($form) {
    foreach ((array) $this->schemaUi as $property => $spec) {
      $form[$property] = $this->handlePropertySpec($property, $spec, $form[$property]);
    }
    return $form;
  }

  /**
   * Helper function for handling Schema UI specs.
   */
  public function handlePropertySpec($property, $spec, $element) {
    $element = $this->applyOnSimpleFields($spec, $element);
    // Handle UI specs for array items.
    if (isset($spec->items)) {
      $element = $this->applyOnArrayFields($property, $spec->items, $element);
    }
    else {
      $element = $this->applyOnObjectFields($property, $spec, $element);
    }
    return $element;
  }

  /**
   * Apply schema UI to simple fields.
   */
  public function applyOnSimpleFields($spec, $element) {
    if (isset($spec->{"ui:options"})) {
      $element = $this->updateWidgets($spec->{"ui:options"}, $element);
      $element = $this->disableFields($spec->{"ui:options"}, $element);
      $element = $this->addPlaceholders($spec->{"ui:options"}, $element);
      $element = $this->changeFieldDescriptions($spec->{"ui:options"}, $element);
      $element = $this->changeFieldTitle($spec->{"ui:options"}, $element);
    }
    return $element;
  }

  /**
   * Apply schema UI to object fields.
   */
  public function applyOnObjectFields($property, $spec, $element) {
    foreach ((array) $spec as $field => $sub_spec) {
      if (isset($element[$property][$field])) {
        $element[$property][$field] = $this->applyOnSimpleFields($sub_spec, $element[$property][$field]);
      }
    }
    return $element;
  }

  /**
   * Apply schema UI to array fields.
   */
  public function applyOnArrayFields($property, $spec, $element) {
    $fields = array_keys((array) $spec);
    foreach ($element[$property] as &$item) {
      foreach ($fields as $field) {
        if (isset($item[$property][$field])) {
          $item[$property][$field] = $this->handlePropertySpec($field, $spec->{$field}, $item[$property][$field]);
        }
        else {
          $item = $this->applyOnSimpleFields($spec, $item);
        }
      }
    }
    return $element;
  }

  /**
   * Helper function for handling widgets.
   */
  public function updateWidgets($spec, $element) {
    if (!isset($spec->widget)) {
      return $element;
    }
    switch ($spec->widget) {
      case 'hidden':
        $element['#access'] = FALSE;
        break;

      case 'textarea':
        $element['#type'] = 'textarea';
        if (isset($spec->rows)) {
          $element['#rows'] = $spec->rows;
        }
        if (isset($spec->cols)) {
          $element['#cols'] = $spec['#cols'];
        }
        break;

    }
    return $element;
  }

  /**
   * Helper function for disabling fields.
   */
  public function disableFields($spec, $element) {
    if (isset($spec->disabled)) {
      $element['#disabled'] = TRUE;
    }
    return $element;
  }

  /**
   * Helper function for adding placeholders.
   */
  public function addPlaceholders($spec, $element) {
    if (isset($spec->placeholder)) {
      $element['#attributes']['placeholder'] = $spec->placeholder;
    }
    return $element;
  }

  /**
   * Helper function for changing help text.
   */
  public function changeFieldDescriptions($spec, $element) {
    if (isset($spec->description)) {
      $element['#description'] = $spec->description;
    }
    return $element;
  }

  /**
   * Helper function for changing help text.
   */
  public function changeFieldTitle($spec, $element) {
    if (isset($spec->title)) {
      $element['#title'] = $spec->title;
    }
    return $element;
  }

}
