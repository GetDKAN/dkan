<?php

namespace Drupal\json_form_widget;

/**
 * Class ObjectHelper.
 */
class ObjectHelper {

  /**
   * Handle form element for an object.
   */
  public function handleObjectElement($definition, $data, $form_state, $parent) {
    $property_schema = $definition['schema'];
    $field_name = $definition['name'];
    $element[$field_name] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $property_schema->title,
    ];
    if (isset($property_schema->description)) {
      $element[$field_name]['#description'] = $property_schema->description;
    }
    $properties = array_keys((array) $property_schema->properties);

    foreach ($properties as $child) {
      $type = $property_schema->properties->{$child}->type ?? "string";
      $value = $data->{$child} ?? NULL;
      $subdefinition = [
        'name' => $child,
        'schema' => $property_schema->properties->{$child},
      ];
      $element[$field_name][$child] = $parent->getFormElement($type, $subdefinition, $value, $property_schema, $form_state);
    }
    return $element;
  }

}
