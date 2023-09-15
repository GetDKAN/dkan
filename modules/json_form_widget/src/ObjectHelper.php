<?php

namespace Drupal\json_form_widget;

use Drupal\Core\Form\FormStateInterface;

/**
 * Object form element helper.
 */
class ObjectHelper {

  /**
   * Builder object.
   *
   * @var \Drupal\json_form_widget\FieldTypeRouter
   */
  public FieldTypeRouter $builder;

  /**
   * Set builder.
   *
   * @param \Drupal\json_form_widget\FieldTypeRouter $builder
   *   Field type router to use for building properties.
   */
  public function setBuilder(FieldTypeRouter $builder): void {
    $this->builder = $builder;
  }

  /**
   * Handle building form element for an object.
   *
   * @param array $definition
   *   Form field definition.
   * @param object|null $data
   *   Form field data.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param string[] $context
   *   Contextual hierarchy for field.
   *
   * @return array
   *   Form field element array.
   */
  public function handleObjectElement(array $definition, ?object $data, FormStateInterface $form_state, array $context): array {
    $field_name = $definition['name'];
    $element = $this->generateObjectElement($definition, $data, $form_state, $context);
    return [$field_name => iterator_to_array($element)];
  }

  /**
   * Generate object element form array.
   *
   * @param array $definition
   *   Form field definition.
   * @param object|null $data
   *   Form field data.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param string[] $context
   *   Contextual hierarchy for field.
   *
   * @return \Generator
   *   Array iterator.
   */
  protected function generateObjectElement(array $definition, ?object $data, FormStateInterface $form_state, array $context): \Generator {
    yield from array_filter([
      '#type'                => 'details',
      '#open'                => TRUE,
      '#title'               => $definition['schema']->title ?? NULL,
      '#description_display' => 'before',
      '#description'         => $definition['schema']->description ?? NULL,
    ]);

    yield from $this->generateProperties($definition, $data, $form_state, $context);
  }

  /**
   * Generate object form element properties.
   *
   * @param array $definition
   *   Form field definition.
   * @param object|null $data
   *   Form field data.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param string[] $context
   *   Contextual hierarchy for field.
   *
   * @return \Generator
   *   Array iterator.
   */
  protected function generateProperties(array $definition, ?object $data, FormStateInterface $form_state, array $context): \Generator {
    $properties = (array) $definition['schema']->properties;
    // Build field for each property.
    foreach ($properties as $property_name => $property) {
      yield $property_name => $this->builder->getFormElement(
        // Field type.
        $property->type ?? 'string',
        // Field definition.
        ['name' => $property_name, 'schema' => $property],
        // Field value.
        $data->{$property_name} ?? NULL,
        // Field schema.
        $definition['schema'],
        $form_state,
        $context,
      );
    }
  }

}
