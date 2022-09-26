<?php

namespace Drupal\json_form_widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drupal render array helper service.
 */
class ArrayHelper implements ContainerInjectionInterface {
  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * Object Helper.
   *
   * @var \Drupal\json_form_widget\ObjectHelper
   */
  protected ObjectHelper $objectHelper;

  /**
   * Builder object.
   *
   * @var \Drupal\json_form_widget\FieldTypeRouter
   */
  public FieldTypeRouter $builder;

  /**
   * Inherited.
   *
   * @{inheritdocs}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('json_form.object_helper')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(ObjectHelper $object_helper) {
    $this->objectHelper = $object_helper;
  }

  /**
   * Set builder.
   */
  public function setBuilder(FieldTypeRouter $builder): void {
    $this->builder = $builder;
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   *
   * @codeCoverageIgnore
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state): array {
    $field = $form_state->getTriggeringElement();
    $element = $form;
    foreach ($field['#array_parents'] as $parent) {
      $element = $element[$parent];
      if ($parent === $field['#name']) {
        break;
      }
    }
    return $element;
  }

  /**
   * Handle form element for an array.
   */
  public function handleArrayElement(array $definition, ?array $data, FormStateInterface $form_state, array $context): array {
    // Extract field name from field definition and min items from field schema
    // for later reference.
    $field_name = $definition['name'];
    $min_items = $definition['schema']->minItems ?? 0;
    // Determine number of form items to generate.
    $item_count = $this->getItemCount($context, count($data ?? []), $min_items, $form_state);
    // Determine if this field is required.
    $required_fields = $this->builder->getSchema()->required;
    $required = in_array($field_name, $required_fields);
    // Build field properties.
    $field_properties = $this->generateFieldProperties($definition, $data, $item_count, $min_items, $form_state, $context, $required);

    // Build field element.
    return [
      '#type'                => 'fieldset',
      '#title'               => ($definition['schema']->title ?? $field_name),
      '#description'         => ($definition['schema']->description ?? ''),
      '#description_display' => 'before',
      '#prefix'              => '<div id="' . $field_name . '-fieldset-wrapper">',
      '#suffix'              => '</div>',
      '#tree'                => TRUE,
      'actions'              => $this->addArrayActions($field_name, $item_count, $min_items),
      $field_name            => iterator_to_array($field_properties),
    ];
  }

  /**
   * Generate field properties.
   *
   * @param array $definition
   *   Field definition.
   * @param array|null $data
   *   Field submission data.
   * @param integer $item_count
   *   Field item count.
   * @param integer $min_items
   *   Minimum required items.
   * @param FormStateInterface $form_state
   *   Form state.
   * @param array $context
   *   Context of current field.
   * @param boolean $field_required
   *   Field requirement state.
   *
   * @return \Generator
   *   Field element properties.
   */
  protected function generateFieldProperties(
    array $definition,
    ?array $data,
    int $item_count,
    int $min_items,
    FormStateInterface $form_state,
    array $context,
    bool $field_required
  ): \Generator {
    // Build and yield `$item_count` field item elements.
    for ($i = 0; $i < $item_count; $i++) {
      $property_required = $field_required && ($i < $min_items);
      yield $this->buildArrayElement($definition, $data[$i] ?? NULL, $form_state, $context, $property_required);
    }
  }

  /**
   * Get the form items count for the given field.
   *
   * @param array $context
   *   Field context to target.
   * @param int $data_count
   *   Number of items in the data array.
   * @param int $items_min
   *   Minimum number of items required.
   *
   * @return int
   *   Form field items count.
   */
  protected function getItemCount(array $context, int $data_count, int $items_min, FormStateInterface $form_state): int {
    // Retrieve the item count from form state (this is not necessarily the
    // current number of items on the form, but the number we wish to be
    // present on the form).
    $count_property = array_merge(['json_form_widget_info'], $context, ['count']);
    $item_count = $form_state->get($count_property);
    // If item count is not set in form state...
    if (!isset($item_count)) {
      // Defer to the number of items in the data array, or fallback on the
      // item minimum if the current data items count is smaller than minimum.
      $item_count = max($data_count, $items_min);
      $form_state->set($count_property, $item_count);
    }
    return $item_count;
  }

  /**
   * Helper function to add actions to array.
   */
  private function addArrayActions(string $field_name, int $item_count, int $min_items): array {
    // Build add action.
    $add_action = $this->getAction($this->t('Add one'), 'json_form_widget_add_one', $field_name);
    // Build remove action if there are more than `$min_items` field elements
    // in this field array.
    $remove_action = ($item_count > $min_items) ?
      $this->getAction($this->t('Remove one'), 'json_form_widget_remove_one', $field_name) :
      NULL;

    return [
      '#type'   => 'actions',
      'actions' => array_filter([
        'add'    => $add_action,
        'remove' => $remove_action,
      ]),
    ];
  }

  /**
   * Helper function to get action.
   */
  private function getAction(string $title, string $function, string $field_name): array {
    return [
      '#type'   => 'submit',
      '#value'  => $title,
      '#submit' => [$function],
      '#name'   => $field_name,
      '#ajax'   => [
        'callback' => [$this, 'addmoreCallback'],
        'wrapper'  => $field_name . '-fieldset-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];
  }

  /**
   * Handle single element from array.
   *
   * Chooses whether element is simple or complex.
   */
  public function buildArrayElement(array $definition, $data, FormStateInterface $form_state, array $context, bool $required): array {
    // If this element's definition has properties defined...
    $element = isset($definition['schema']->items->properties) ?
      // Attempt to build a complex element, otherwise...
      $this->buildComplexArrayElement($definition, $data, $form_state, $context) :
      // Build a simple element.
      $this->buildSimpleArrayElement($definition, $data);

    // Set element requirement.
    $element['#required'] = $required;

    return $element;
  }

  /**
   * Returns single simple element from array.
   */
  public function buildSimpleArrayElement(array $definition, $data): array {
    return array_filter([
      '#type'          => 'textfield',
      '#title'         => $definition['schema']->items->title ?? NULL,
      '#default_value' => $data,
    ]);
  }

  /**
   * Returns single complex element from array.
   */
  public function buildComplexArrayElement(array $definition, $data, FormStateInterface $form_state, array $context): array {
    $subdefinition = [
      'name'   => $definition['name'],
      'schema' => $definition['schema']->items,
    ];
    return $this->objectHelper->handleObjectElement($subdefinition, $data, $form_state, $context, $this->builder);
  }

}
