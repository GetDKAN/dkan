<?php

namespace Drupal\json_form_widget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drupal render array helper service.
 */
class ArrayHelper implements ContainerInjectionInterface {
  use StringTranslationTrait;
  use DependencySerializationTrait;

  const STATE_PROP = 'json_form_widget_info';
  const STATE_PROP_COUNT = 'count';
  const STATE_PROP_ADD = 'add';

  /**
   * Object Helper.
   */
  protected ObjectHelper $objectHelper;


  /**
   * String Helper.
   */
  protected StringHelper $stringHelper;

  /**
   * Builder object.
   */
  public FieldTypeRouter $builder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('json_form.object_helper'),
      $container->get('json_form.string_helper')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(ObjectHelper $object_helper, StringHelper $string_helper) {
    $this->objectHelper = $object_helper;
    $this->stringHelper = $string_helper;
  }

  /**
   * Set builder.
   */
  public function setBuilder(FieldTypeRouter $builder): void {
    $this->builder = $builder;
    $this->objectHelper->setBuilder($builder);
  }

  /**
   * Shared AJAX callback function for all array buttons.
   *
   * @param array $form
   *   Newly built form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return array
   *   Field wrapper render array.
   */
  public function arrayActionButtonCallback(array &$form, FormStateInterface $form_state): array {
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
   * Create an array element from a schema field definition.
   *
   * @param array $definition
   *   Field definition, should contain 'schema' and 'name' keys.
   * @param mixed $data
   *   The field data, read directly from the existing JSON value.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param array $context
   *   Field context, used to build unique identifiers for the field.
   *
   * @todo Can we eliminate the $context parameter or make it more intuitive?
   */
  public function handleArrayElement(array $definition, $data, FormStateInterface $form_state, array $context): array {
    // Extract field name from field definition and min items from field schema.
    $min_items = $definition['schema']->minItems ?? 0;

    $context_name = self::buildContextName($context);
    $item_count = $this->getItemCount($context_name, count($data ?? []), $min_items, $form_state);
    $add_property = self::buildStateProperty(self::STATE_PROP_ADD, $context_name);
    $add = $form_state->get($add_property) ?? FALSE;
    $is_required = in_array($definition['name'], $this->builder->getSchema()->required ?? []);

    // Build the parent fieldset.
    $element = $this->buildArrayParentElement($definition, $is_required, $context_name);

    // Build the specified number of field item elements.
    $items = [];
    for ($i = 0; $i < $item_count; $i++) {
      $item = $this->buildArrayItemElement($definition, $data[$i] ?? NULL, $form_state, array_merge($context, [$i]));
      $item['#required'] = $is_required && ($i < $min_items);
      $items[] = $item;
    }
    // If add is true, overwrite the last item with a new empty item.
    if ($add && $item_count > 0) {
      $items[$item_count - 1] = $this->buildArrayItemElement($definition, NULL, $form_state, array_merge($context, [$item_count - 1]));
      $items[$item_count - 1]['#required'] = $is_required;
      $form_state->set($add_property, FALSE);
    }

    $element[$definition['name']] = $items;
    return $element;
  }

  /**
   * Build the parent fieldset for an array.
   *
   * @param array $definition
   *   Field definition.
   * @param bool $is_required
   *   Whether the field is required.
   * @param string $context_name
   *   Field context name.
   *
   * @return array
   *   Render array for the array parent element.
   */
  protected function buildArrayParentElement(array $definition, bool $is_required, string $context_name) {
    $element = [
      '#type' => 'fieldset',
      '#title' => ($definition['schema']->title ?? $definition['name']),
      '#description' => ($definition['schema']->description ?? ''),
      '#description_display' => 'before',
      '#prefix' => '<div id="' . self::buildWrapperIdentifier($context_name) . '">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
      '#required' => $is_required,
      'array_actions' => [
        '#type'   => 'actions',
        'actions' => [
          'add' => $this->buildAction($this->t('Add one'), 'addOne', $definition['name'], $context_name),
        ],
      ],
    ];
    return $element;
  }

  /**
   * Build a single element from an array.
   *
   * @param array $definition
   *   Field definition.
   * @param mixed $data
   *   Field data.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param string[] $context
   *   Field context.
   *
   * @return array
   *   Render array for the array element.
   */
  protected function buildArrayItemElement(array $definition, $data, FormStateInterface $form_state, array $context): array {
    // Use the simple or complex method depending on whether items are objects.
    if (isset($definition['schema']->items->properties)) {
      $element = $this->buildComplexArrayElement($definition, $data, $form_state, $context);
    }
    else {
      $element = $this->buildSimpleArrayElement($definition, $data, $context);
    }
    return $element;
  }

  /**
   * Returns single simple element from array.
   *
   * @param array $definition
   *   Field definition.
   * @param mixed $data
   *   Field data.
   * @param string[] $context
   *   Field context.
   *
   * @return array
   *   Render array for the simple array element.
   */
  protected function buildSimpleArrayElement(array $definition, $data, array $context): array {
    return [
      '#type' => 'fieldset',
      '#attributes' => [
        'data-parent' => $definition['name'],
        'class' => ['json-form-widget-array-item'],
      ],
      'field' => array_filter([
        '#type'          => 'textfield',
        '#title'         => $definition['schema']->items->title ?? NULL,
        '#default_value' => $data,
      ]),
      'actions' => $this->buildElementActions($definition['name'], self::buildContextName($context)),
    ];
  }

  /**
   * Returns single complex element from array.
   *
   * @param array $definition
   *   Field definition.
   * @param mixed $data
   *   Field data.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param string[] $context
   *   Field context.
   *
   * @return array
   *   Render array for the complex array element.
   *
   * @todo better document the context parameter.
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

  /**
   * Get the form items count for the given field.
   *
   * We use both the provided data and the schema to determine the proper number
   * of items to display on the form.
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
    $count_property = self::buildStateProperty(self::STATE_PROP_COUNT, $context_name);
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
   * Build a form_state property pointer specific to this array that can be set.
   *
   * @param string $name
   *   The specific property to build (e.g., "count").
   * @param string $context_name
   *   Field element context name.
   *
   * @return string[]
   *   Full count property array.
   */
  public static function buildStateProperty(string $name, string $context_name): array {
    return [self::STATE_PROP, $context_name, $name];
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
    $action = [
      '#type'   => 'submit',
      '#name'   => $context_name,
      '#value'  => $title,
      '#submit' => [self::class . '::' . $method],
      '#ajax'   => [
        'callback' => [$this, 'arrayActionButtonCallback'],
        'wrapper'  => self::buildWrapperIdentifier($parent),
      ],
      '#attributes' => [
        'data-parent'  => $parent,
      ],
      '#limit_validation_errors' => [],
    ];
    return $action;
  }

  /**
   * Build the remove/reorder actions for a single element.
   *
   * @param string $parent
   *   Parent element name.
   * @param string $context_name
   *   Data context.
   *
   * @return array
   *   Actions render array.
   */
  protected function buildElementActions(string $parent, string $context_name):array {
    return [
      '#type' => 'actions',
      'remove' => $this->buildAction($this->t('Remove'), 'remove', $parent, $context_name),
      'move_up' => $this->buildAction($this->t('Move Up'), 'moveUp', $parent, $context_name),
      'move_down' => $this->buildAction($this->t('Move Down'), 'moveDown', $parent, $context_name),
    ];
  }

  /**
   * Submit function for element "remove" button.
   *
   * @param array $form
   *   Form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public static function remove(array &$form, FormStateInterface $form_state) {
    $button_element = $form_state->getTriggeringElement();
    $parent = $button_element['#attributes']['data-parent'];
    $parents = $button_element['#parents'];
    $element_index = str_replace("{$parent}-", '', $button_element['#name']);
    $count_property = self::buildStateProperty(self::STATE_PROP_COUNT, $parent);
    $user_input = $form_state->getUserInput();

    // Update the user input to remove the specific element.
    $key_exists = NULL;
    static::trimParents($parents, $element_index);
    $input_values = &NestedArray::getValue($user_input, $parents, $key_exists);
    if ($key_exists) {
      unset($input_values[$element_index]);
      // Re-index the array to maintain proper keys.
      $input_values = \array_values($input_values);
    }

    $form_state->setUserInput($user_input);

    // Modify stored item count. The form rebuilds before the alter, so it needs
    // to be one more than the current item count to avoid removing twice.
    $item_count = count($input_values);
    $form_state->set($count_property, $item_count);

    $form_state->setRebuild();
  }

  /**
   * Submit function for element "move up" button.
   *
   * @param array $form
   *   Form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public static function moveUp(array &$form, FormStateInterface $form_state) {
    return static::moveElement($form_state, -1);
  }

  /**
   * Submit function for element "move down" button.
   *
   * @param array $form
   *   Form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public static function moveDown(array &$form, FormStateInterface $form_state) {
    return static::moveElement($form_state, 1);
  }

  /**
   * Common function to move element within array by the given offset.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param int $offset
   *   Offset to move the element by.
   */
  protected static function moveElement(FormStateInterface $form_state, int $offset) {
    $button_element = $form_state->getTriggeringElement();
    $parent = $button_element['#attributes']['data-parent'];
    $parents = $button_element['#parents'];
    $element_index = str_replace("{$parent}-", '', $button_element['#name']);
    $user_input = $form_state->getUserInput();

    // Update the user input to change the order.
    $key_exists = NULL;
    static::trimParents($parents, $element_index);
    $input_values = &NestedArray::getValue($user_input, $parents, $key_exists);
    if ($key_exists) {
      $moved_element = array_splice($input_values, $element_index, 1);
      array_splice($input_values, $element_index + $offset, 0, $moved_element);
      // Re-index the array to maintain proper keys.
      $input_values = \array_values($input_values);
    }

    $form_state->setUserInput($user_input);
    $form_state->setRebuild();
  }

  /**
   * Submit function for array "add one" button.
   *
   * @param array $form
   *   Form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public static function addOne(array &$form, FormStateInterface $form_state) {
    $button_element = $form_state->getTriggeringElement();
    $count_property = static::buildStateProperty(self::STATE_PROP_COUNT, $button_element['#name']);
    // Modify stored item count.
    $item_count = $form_state->get($count_property) ?? 0;
    $item_count++;
    $form_state->set($count_property, $item_count);

    $add_property = static::buildStateProperty(self::STATE_PROP_ADD, $button_element['#name']);
    $form_state->set($add_property, TRUE);

    $form_state->setRebuild();
  }

  /**
   * Utility function to trim the triggering element's parents array.
   *
   * Used to get the correct position in the user input array for modifications.
   *
   * @param array $parents
   *   Parents array.
   * @param int $element_index
   *   Element index.
   */
  public static function trimParents(array &$parents, $element_index): void {
    for ($i = count($parents) - 1; $i >= 0; $i--) {
      if ($parents[$i] == $element_index) {
        $ei_position = $i;
        break;
      }
    }
    $offset = 0 - (count($parents) - $ei_position);
    \array_splice($parents, $offset);
  }

}
