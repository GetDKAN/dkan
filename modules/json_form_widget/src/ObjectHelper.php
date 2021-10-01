<?php

namespace Drupal\json_form_widget;

use Drupal\Core\Form\FormStateInterface;

use Drupal\json_form_widget\FieldTypeRouter;

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

    foreach ($property_schema->allOf ?? [] as $condition) {
      $element[$field_name] = $this->handleCondition($condition, $element[$field_name], $data, $form_state, $parent);
    }

    return $element;
  }

  protected function handleCondition(object $condition, array $field, $data, FormStateInterface $form_state, FieldTypeRouter $parent): array {
    if (isset($condition->if, $condition->then) && $field_states = $this->validateElementAgainstSubschema($field, $condition->if)) {
      $subdefinition = [
        'name' => 'temp',
        'schema' => $condition->then,
      ];
      $field += $this->handleObjectElement($subdefinition, $data, $form_state, $parent)['temp'];
      foreach ($field_states as $name => $value) {
        $field['#states']['visible'][':input[name="' . $name . '"]']['value'] = $value;
      }
    }

    return $field;
  }

  protected function validateElementAgainstSubschema(array $field, object $subschema): ?array {
    $field_states = [];

    foreach ($subschema->properties ?? [] as $prop => $value) {
      if (!isset($field[$prop], $value->const)) {
        return NULL;
      }
      $field_states[$prop] = $value->const;
    }

    return $field_states;
  }
}
