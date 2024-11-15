<?php

namespace Drupal\json_form_widget;

use Drupal\Component\Utility\NestedArray;
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
   * {@inheritdoc}
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
    // Extract field name from field definition and min items from field schema.
    $field_name = $definition['name'];
    $min_items = $definition['schema']->minItems ?? 0;

    $context_name = self::buildContextName($context);
    $item_count = $this->getItemCount($context_name, count($data ?? []), $min_items, $form_state);
    $is_required = in_array($field_name, $this->builder->getSchema()->required ?? []);

    // Build the specified number of field item elements.
    $items = [];
    for ($i = 0; $i < $item_count; $i++) {
      $property_required = $is_required && ($i < $min_items);
      $items[] = $this->buildArrayElement($definition, $data[$i] ?? NULL, $form_state, array_merge($context, [$i]), $property_required);
    }

    // Build field element.
    return [
      '#type' => 'fieldset',
      '#title' => ($definition['schema']->title ?? $field_name),
      '#description' => ($definition['schema']->description ?? ''),
      '#description_display' => 'before',
      '#prefix' => '<div id="' . self::buildWrapperIdentifier($context_name) . '">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
      'actions' => [
        '#type'   => 'actions',
        'actions' => [
          'add' => $this->buildAction($this->t('Add one'), 'addOne', $field_name, $context_name),
        ],
      ],
      $field_name => $items,
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
   * Build count property.
   *
   * @param string $context_name
   *   Field element context name.
   *
   * @return string[]
   *   Full count property array.
   */
  public static function buildAlterProperty(string $context_name): array {
    return ['json_form_widget_info', $context_name, 'alter'];
  }

  /**
   * Helper function to build an action button.
   *
   * @param string $title
   *   Button title.
   * @param string $method
   *   Button submit method; should be a static method from this class.
   * @param string $parent
   *   The parent element for the action; usually the current field name.
   * @param string $context_name
   *   The context name, output of ::buildContextName().
   */
  protected function buildAction(string $title, string $method, string $parent, string $context_name): array {
    return [
      '#type'   => 'submit',
      '#name'   => $context_name,
      '#value'  => $title,
      '#submit' => [self::class . '::' . $method],
      '#ajax'   => [
        'callback' => [$this, 'addOrRemoveButtonCallback'],
        'wrapper'  => self::buildWrapperIdentifier($parent),
      ],
      '#attributes' => [
        'data-parent'  => $parent,
      ],
      '#limit_validation_errors' => [],
    ];
  }

  /**
   * Build the remove/reorder actions for a single element.
   *
   * @param string $parent
   *   Parent element name.
   * @param string $context_name
   *   Data context.
   *
   * @return array{#type: string, remove: array}
   *   Actions render array.
   */
  protected function buildElementActions(string $parent, string $context_name) {
    return [
      '#type' => 'actions',
      'remove' => $this->buildAction($this->t('Remove'), 'remove', $parent, $context_name),
      'move_up' => $this->buildAction($this->t('Move Up'), 'moveUp', $parent, $context_name),
      'move_d' => $this->buildAction($this->t('Move Down'), 'moveDown', $parent, $context_name),
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
      $this->buildSimpleArrayElement($definition, $data, $context);

    // Set element requirement.
    $element['#required'] = $required;

    return $element;
  }

  /**
   * Returns single simple element from array.
   */
  protected function buildSimpleArrayElement(array $definition, $data, array $context): array {
    return array_filter([
      '#type'          => 'textfield',
      '#title'         => $definition['schema']->items->title ?? NULL,
      '#default_value' => $data,
      'actions' => $this->buildElementActions($definition['name'], self::buildContextName($context))
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
    $element = $this->objectHelper->handleObjectElement($subdefinition, $data, $form_state, $context, $this->builder);
    $element[$definition['name']]['actions'] = $this->buildElementActions($definition['name'], self::buildContextName($context));
    return $element;
  }

  public static function remove(array &$form, FormStateInterface $form_state) {
    $button_element = $form_state->getTriggeringElement();
    $parent = $button_element['#attributes']['data-parent'];
    $parents = $button_element['#parents'];
    $element_index = str_replace("{$parent}-", '', $button_element['#name']);
    $count_property = self::buildCountProperty($parent);
    $user_input = $form_state->getUserInput();

    // Update the user input to remove the specific element.
    $key_exists = NULL;
    // We actually want the parent container of all elements. Hopefully going
    // back 4 levels will work in all situations.
    array_splice($parents, -4);
    $distributions = &NestedArray::getValue($user_input, $parents, $key_exists);
    if ($key_exists) {
      unset($distributions[$element_index]);
      // Re-index the array to maintain proper keys.
      $distributions = \array_values($distributions);
    }

    $form_state->setUserInput($user_input);

    // Modify stored item count. The form rebuilds before the alter, so it needs
    // to be one more than the current item count to avoid removing twice.
    $item_count = count($distributions);
    $form_state->set($count_property, $item_count);

    $form_state->setRebuild();
  }

  public static function moveUp(array &$form, FormStateInterface $form_state) {
    return static::moveElement($form_state, -1);
  }

  public static function moveDown(array &$form, FormStateInterface $form_state) {
    return static::moveElement($form_state, 1);
  }

  protected static function moveElement(FormStateInterface $form_state, int $offset) {
    $button_element = $form_state->getTriggeringElement();
    $parent = $button_element['#attributes']['data-parent'];
    $parents = $button_element['#parents'];
    $element_index = str_replace("{$parent}-", '', $button_element['#name']);
    $user_input = $form_state->getUserInput();

    // Update the user input to change the order.
    $key_exists = NULL;
    // We actually want the parent container of all elements. Hopefully going
    // back 4 levels will work in all situations.
    array_splice($parents, -4);
    $distributions = &NestedArray::getValue($user_input, $parents, $key_exists);
    if ($key_exists) {
      $moved_element = array_splice($distributions, $element_index, 1);
      array_splice($distributions, $element_index + $offset, 0, $moved_element);
      // Re-index the array to maintain proper keys.
      $distributions = \array_values($distributions);
    }

    $form_state->setUserInput($user_input);
    $form_state->setRebuild();
  }

  /**
   * Update count property by the given offset.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public static function addOne(array &$form, FormStateInterface $form_state) {
    $button_element = $form_state->getTriggeringElement();
    $alter_property = self::buildAlterProperty($button_element['#name']);
    $items_alter_index = $form_state->get($alter_property) ?? [];
    $items_alter_index[] = count($items_alter_index);

    $count_property = static::buildCountProperty($button_element['#name']);
    // Modify stored item count.
    $item_count = $form_state->get($count_property) ?? 0;
    $item_count++;
    $form_state->set($count_property, $item_count);

    $form_state->set($alter_property, $items_alter_index);
    $form_state->setRebuild();
  }

}
