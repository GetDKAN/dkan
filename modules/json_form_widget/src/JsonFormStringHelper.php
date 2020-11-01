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
    $element_schema = $object_schema ? $object_schema : $this->builder->schema;
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
