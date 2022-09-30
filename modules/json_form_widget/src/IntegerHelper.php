<?php

namespace Drupal\json_form_widget;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;

/**
 * JSON form widget string helper service.
 */
class IntegerHelper {
  use DependencySerializationTrait;

  /**
   * Field type router.
   *
   * @var \Drupal\json_form_widget\FieldTypeRouter
   */
  protected FieldTypeRouter $builder;

  /**
   * Set the field type router.
   *
   * @param \Drupal\json_form_widget\FieldTypeRouter $builder
   *   Field type router.
   */
  public function setBuilder(FieldTypeRouter $builder): void {
    $this->builder = $builder;
  }

  /**
   * Handle form element for an integer.
   *
   * @param array $definition
   *   Field definition.
   * @param int|null $data
   *   Field data.
   * @param object|null $element_schema
   *   Parent field element schema.
   *
   * @return array
   *   Integer field render array.
   */
  public function handleIntegerElement(array $definition, ?int $data, ?object $element_schema = NULL): array {
    // Extract field name and schema from definition.
    $field_name = $definition['name'];
    $field_schema = $definition['schema'];
    // If no element schema was provided, default to the schema stored in the
    // field type router.
    $element_schema ??= $this->builder->getSchema();

    return array_filter([
      '#title'               => $field_schema->title ?? '',
      '#description'         => $field_schema->description ?? '',
      '#description_display' => 'before',
      '#type'                => 'number',
      '#step'                => 1,
      '#min'                 => 1,
      '#default_value'       => $this->getDefaultValue($data, $field_schema),
      '#required'            => $this->checkIfRequired($field_name, $element_schema),
    ]);
  }

  /**
   * Get default value for element.
   *
   * @param int|null $data
   *   Current field value.
   * @param object|null $field_schema
   *   Field schema.
   *
   * @return int|null
   *   Default field value.
   */
  public function getDefaultValue(?int $data, ?object $field_schema): ?int {
    return $data ?: $field_schema->default ?? NULL;
  }

  /**
   * Check if field is required based on its schema.
   *
   * @param string $field_name
   *   Field name.
   * @param object|null $element_schema
   *   Parent field element schema.
   */
  public function checkIfRequired(string $field_name, ?object $element_schema): bool {
    return in_array($field_name, $element_schema->required ?? []);
  }

}
