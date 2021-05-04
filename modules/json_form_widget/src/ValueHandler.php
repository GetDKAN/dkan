<?php

namespace Drupal\json_form_widget;

use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Class ValueHandler.
 */
class ValueHandler {

  /**
   * Flatten values.
   */
  public function flattenValues($formValues, $property, $schema) {
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
  public function handleStringValues($formValues, $property) {
    // Handle datetime elements.
    if (isset($formValues[$property]) && $formValues[$property] instanceof DrupalDateTime) {
      return $formValues[$property]->__toString();
    }
    // Handle select_or_other_select.
    if (isset($formValues[$property]['select'])) {
      return $formValues[$property][0];
    }
    if (!empty($formValues[$property])) {
      return $formValues[$property];
    }
    return FALSE;
  }

  /**
   * Flatten values for object properties.
   */
  public function handleObjectValues($formValues, $property, $schema) {
    if (!isset($formValues)) {
      return FALSE;
    }

    $properties = array_keys((array) $schema->properties);
    $data = FALSE;
    foreach ($properties as $sub_property) {
      $value = $this->flattenValues($formValues, $sub_property, $schema->properties->$sub_property);
      if ($value) {
        $data[$sub_property] = $value;
      }
    }
    return $data;
  }

  /**
   * Flatten values for array properties.
   */
  public function handleArrayValues($formValues, $property, $schema) {
    $data = [];
    $subschema = $schema->items;
    if ($subschema->type === "object") {
      return $this->getObjectInArrayData($formValues, $property, $subschema);
    }

    foreach ($formValues[$property][$property] as $value) {
      $data = array_merge($data, $this->flattenArraysInArrays($value));
    }
    return !empty($data) ? $data : FALSE;
  }

  /**
   * Flatten values for arrays in arrays.
   */
  private function flattenArraysInArrays($value) {
    $data = [];
    if (is_array($value)) {
      foreach ($value as $item) {
        $data[] = $item;
      }
    }
    elseif (!empty($value)) {
      $data[] = $value;
    }
    return $data;
  }

  /**
   * Flatten values for objects in arrays.
   */
  private function getObjectInArrayData($formValues, $property, $schema) {
    $data = [];
    foreach ($formValues[$property][$property] as $key => $item) {
      $value = $this->handleObjectValues($formValues[$property][$property][$key][$property], $property, $schema);
      if ($value) {
        $data[$key] = $value;
      }
    }
    return $data;
  }

}
