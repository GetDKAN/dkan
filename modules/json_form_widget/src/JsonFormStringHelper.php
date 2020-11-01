<?php

namespace Drupal\json_form_widget;

/**
 * Class JsonFormStringHelper.
 */
class JsonFormStringHelper {

  /**
   * Builder object.
   *
   * @var \Drupal\json_form_widget\JsonFormBuilder
   */
  public $builder;

  /**
   * Set builder.
   */
  public function setBuilder($builder) {
    $this->builder = $builder;
  }

  /**
   * Handle form element for a string.
   */
  public function handleStringElement($property, $field_name, $data, $object_schema = FALSE) {
    // Basic definition.
    $element = [
      '#type' => $this->getElementType($property),
    ];
    $element['#title'] = isset($property->title) ? $property->title : '';
    $element['#description'] = isset($property->description) ? $property->description : '';
    $element['#default_value'] = $this->getDefaultValue($data, $property);

    // Check if the field is required.
    $element_schema = $object_schema ? $object_schema : $this->builder->schema;
    $element['#required'] = $this->checkIfRequired($field_name, $element_schema);

    // Add options if element type is select.
    if ($element['#type'] === 'select') {
      $element['#options'] = $this->getSelectOptions($property);
    }
    return $element;
  }

  /**
   * Get type of element.
   */
  public function getElementType($property) {
    if (isset($property->format) && $property->format == 'uri') {
      return 'url';
    }
    if (isset($property->enum)) {
      return 'select';
    }
    return 'textfield';
  }

  /**
   * Get default value for element.
   */
  public function getDefaultValue($data, $property) {
    if ($data) {
      return $data;
    }
    elseif (isset($property->default)) {
      return $property->default;
    }
  }

  /**
   * Check if field is required based on its schema.
   */
  public function checkIfRequired($name, $element_schema) {
    if (isset($element_schema->required) && in_array($name, $element_schema->required)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get array of options for a property.
   */
  public function getSelectOptions($property) {
    $options = [];
    if (isset($property->enumNames)) {
      $options = array_combine($property->enum, $property->enumNames);
    }
    else {
      $options = array_combine($property->enum, $property->enum);
    }
    return $options;
  }

}
