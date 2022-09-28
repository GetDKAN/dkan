<?php

namespace Drupal\json_form_widget;

use Drupal\Core\Form\FormStateInterface;

/**
 * Object form element helper.
 */
class ObjectHelper {

  /**
   * Handle building form element for an object.
   *
   * @param array $definition
   *   Form field definition.
   * @param FieldTypeRouter $builder
   *   Form field builder.
   * @param object $data
   *   Form field data.
   * @param FormStateInterface $form_state
   *   Form state.
   *
   * @return array
   *   Form field array.
   */
  public function handleObjectElement(array $definition, ?object $data, FormStateInterface $form_state, array $context, FieldTypeRouter $builder): array {
    $field_name = $definition['name'];
    $element = $this->generateObjectElement($definition, $builder, $data, $form_state, $context);
    return [$field_name => iterator_to_array($element)];
  }

  /**
   * Generate object element form array.
   *
   * @param array $definition
   *   Form field definition.
   * @param FieldTypeRouter $builder
   *   Form field builder.
   * @param object $data
   *   Form field data.
   * @param FormStateInterface $form_state
   *   Form state.
   *
   * @return \Generator
   *   Array iterator.
   */
  protected function generateObjectElement(array $definition, FieldTypeRouter $builder, ?object $data, FormStateInterface $form_state, array $context): \Generator {
    yield from array_filter([
      '#type'                => 'details',
      '#open'                => TRUE,
      '#title'               => $definition['schema']->title ?? NULL,
      '#description_display' => 'before',
      '#description'         => $definition['schema']->description ?? NULL,
    ]);

    yield from $this->generateProperties($definition, $builder, $data, $form_state, $context);
  }

  /**
   * Generate object form element properties.
   *
   * @param array $definition
   *   Form field definition.
   * @param FieldTypeRouter $builder
   *   Form field builder.
   * @param object $data
   *   Form field data.
   * @param FormStateInterface $form_state
   *   Form state.
   *
   * @return \Generator
   *   Array iterator.
   */
  protected function generateProperties(array $definition, FieldTypeRouter $builder, ?object $data, FormStateInterface $form_state, array $context): \Generator {
    $properties = (array) $definition['schema']->properties;
    foreach ($properties as $property_name => $property) {
      yield $property_name => $builder->getFormElement(
        $property->type ?? 'string', // type
        ['name' => $property_name, 'schema' => $property], // definition
        $data->{$property_name} ?? NULL, // value
        $definition['schema'], // schema
        $form_state,
        $context,
      );
    }
  }

}
