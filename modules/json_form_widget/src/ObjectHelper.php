<?php

namespace Drupal\json_form_widget;

use Drupal\Core\Form\FormStateInterface;

use Drupal\json_form_widget\FieldTypeRouter;

use Parsedown;

/**
 * Class ObjectHelper.
 */
class ObjectHelper {

  /**
   * Handle form element for an object.
   */
  public function handleObjectElement(array $definition, $data, FormStateInterface $form_state, FieldTypeRouter $parent): array {
    $property_schema = $definition['schema'];
    $field_name = $definition['name'];
    $element[$field_name] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $property_schema->title,
    ];
    if (isset($property_schema->description)) {
      $parsedown = new Parsedown();
      $element[$field_name]['#description'] = $parsedown->text($property_schema->description);
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
