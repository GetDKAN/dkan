<?php

namespace Drupal\json_form_widget;

/**
 * Class ObjectHelper.
 */
class ObjectHelper {

  /**
   * Handle form element for an object.
   */
  public function handleObjectElement($property_schema, $field_name, $data, $form_state, $parent) {
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
      $element[$field_name][$child] = $parent->getFormElement($type, $child, $property_schema->properties->{$child}, $value, $property_schema, $form_state);
    }
    return $element;
  }

}
