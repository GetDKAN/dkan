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
    $this->objectHelper->setBuilder($builder);
  }

  /**
   * Update wrapper element of the triggering button after build.
   *
   * @param array $form
   *   Newly built form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return array
   *   Field wrapper render array.
   */
  public function addOrRemoveButtonCallback(array &$form, FormStateInterface $form_state): array {
    // Retrieve triggering button element.
    $button = $form_state->getTriggeringElement();
    // Extract full heritage for the triggered button.
    $button_heritage = $button['#array_parents'];
    // Determine name of wrapper element of the triggering button which
    // will be updated.
    $button_parent = $button['#attributes']['data-parent'];

    // Initialize target element to root form render array.
    $target_element = $form;
    // Iterate down element heritage from root form element in order to find
    // immediate parent wrapper element.
    foreach ($button_heritage as $button_ancestor) {
      // Navigate deeper into form hierarchy according to the next listed
      // button field ancestor.
      $target_element = $target_element[$button_ancestor];
      if ($button_ancestor === $button_parent) {
        // We've found the parent element, so we can return it.
        return $target_element;
      }
    }

    throw new \RuntimeException('Failed to find wrapper element for button.');
  }

  /**
   * Handle form element for an array.
   */
  public function handleArrayElement(array $definition, ?array $data, FormStateInterface $form_state, array $context): array {
    // Extract field name from field definition and min items from field schema
    // for later reference.
    $field_name = $definition['name'];
    $min_items = $definition['schema']->minItems ?? 0;
    // Build context name.
    $context_name = self::buildContextName($context);
    // Determine number of form items to generate.
    $item_count = $this->getItemCount($context_name, count($data ?? []), $min_items, $form_state);

    // Determine if this field is required.
    $required_fields = $this->builder->getSchema()->required ?? [];
    $field_required = in_array($field_name, $required_fields);
    // Build the specified number of field item elements.
    $field_properties = [];
    for ($i = 0; $i < $item_count; $i++) {
      $property_required = $field_required && ($i < $min_items);
      $field_properties[] = $this->buildArrayElement($definition, $data[$i] ?? NULL, $form_state, array_merge($context, [$i]), $property_required);
    }

    // Build field element.
    return [
      '#type'                => 'fieldset',
      '#title'               => ($definition['schema']->title ?? $field_name),
      '#description'         => ($definition['schema']->description ?? ''),
      '#description_display' => 'before',
      '#prefix'              => '<div id="' . self::buildWrapperIdentifier($context_name) . '">',
      '#suffix'              => '</div>',
      '#tree'                => TRUE,
      'actions'              => $this->buildActions($item_count, $min_items, $field_name, $context_name),
      $field_name            => $field_properties,
    ];
  }

  /**
   * Get the form items count for the given field.
   *
   * @param string $context_name
   *   Field context to target.
   * @param int $data_count
   *   Number of items in the data array.
   * @param int $items_min
   *   Minimum number of items required.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return int
   *   Form field items count.
   */
  protected function getItemCount(string $context_name, int $data_count, int $items_min, FormStateInterface $form_state): int {
    // Retrieve the item count from form state (this is not necessarily the
    // current number of items on the form, but the number we wish to be
    // present on the form).
    $count_property = self::buildCountProperty($context_name);
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
   * Build unique identifier from field context.
   *
   * @param string[] $context
   *   Field context.
   *
   * @return string
   *   Unique context identifier.
   */
  public static function buildContextName(array $context): string {
    return implode('-', $context);
  }

  /**
   * Build fieldset wrapper identifier from context name.
   *
   * @param string $context_name
   *   Context name.
   *
   * @return string
   *   Fieldset wrapper identifier.
   */
  protected static function buildWrapperIdentifier(string $context_name): string {
    return $context_name . '-fieldset-wrapper';
  }

  /**
   * Build count property.
   *
   * @param string $context_name
   *   Field element context name.
   *
   * @return string[]
   *   Full count property array.
   */
  public static function buildCountProperty(string $context_name): array {
    return ['json_form_widget_info', $context_name, 'count'];
  }

  /**
   * Helper function to build form field actions.
   */
  protected function buildActions(int $item_count, int $min_items, string $parent, string $context_name): array {
    $actions = [];

    // Build add action.
    $actions['add'] = $this->buildAction($this->t('Add one'), 'json_form_widget_add_one', $parent, $context_name);
    // Build remove action if there are more than the minimum required elements
    // in this field array.
    if ($item_count > $min_items) {
      $actions['remove'] = $this->buildAction($this->t('Remove one'), 'json_form_widget_remove_one', $parent, $context_name);
    }

    return [
      '#type'   => 'actions',
      'actions' => $actions,
    ];
  }

  /**
   * Helper function to get action.
   */
  protected function buildAction(string $title, string $function, string $parent, string $context_name): array {
    return [
      '#type'   => 'submit',
      '#name'   => $context_name,
      '#value'  => $title,
      '#submit' => [$function],
      '#ajax'   => [
        'callback' => [$this, 'addOrRemoveButtonCallback'],
        'wrapper'  => self::buildWrapperIdentifier($context_name),
      ],
      '#attributes' => [
        'data-parent'  => $parent,
        'data-context' => $context_name,
      ],
      '#limit_validation_errors' => [],
    ];
  }

  /**
   * Handle single element from array.
   *
   * Chooses whether element is simple or complex.
   */
  protected function buildArrayElement(array $definition, $data, FormStateInterface $form_state, array $context, bool $required): array {
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
  protected function buildSimpleArrayElement(array $definition, $data): array {
    return array_filter([
      '#type'          => 'textfield',
      '#title'         => $definition['schema']->items->title ?? NULL,
      '#default_value' => $data,
    ]);
  }

  /**
   * Returns single complex element from array.
   */
  protected function buildComplexArrayElement(array $definition, $data, FormStateInterface $form_state, array $context): array {
    $subdefinition = [
      'name'   => $definition['name'],
      'schema' => $definition['schema']->items,
    ];
    return $this->objectHelper->handleObjectElement($subdefinition, $data, $form_state, $context, $this->builder);
  }

}
