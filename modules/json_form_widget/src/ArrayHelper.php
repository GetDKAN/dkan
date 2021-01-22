<?php

namespace Drupal\json_form_widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ArrayHelper.
 */
class ArrayHelper implements ContainerInjectionInterface {
  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * Object Helper.
   *
   * @var \Drupal\json_form_widget\ObjectHelper
   */
  protected $objectHelper;

  /**
   * Builder object.
   *
   * @var \Drupal\json_form_widget\FieldTypeRouter
   */
  public $builder;

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
  public function setBuilder($builder) {
    $this->builder = $builder;
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   *
   * @codeCoverageIgnore
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
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
  public function handleArrayElement($definition, $data, $form_state) {
    $property_schema = $definition['schema'];
    $field_name = $definition['name'];
    // Save info about the arrays.
    $widget_array_info = $form_state->get('json_form_widget_array');
    $form_state->set('json_form_widget_schema', $this->builder->schema);
    // Get amount of items to print.
    $amount = $this->getItemsNumber($form_state, $widget_array_info, $field_name, $data);

    $element = [
      '#type' => 'fieldset',
      '#title' => $field_name,
      '#prefix' => '<div id="' . $field_name . '-fieldset-wrapper">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    ];

    if (isset($property_schema->title)) {
      $element['#title'] = $property_schema->title;
    }

    if (isset($property_schema->description)) {
      $element['#description'] = $property_schema->description;
    }

    for ($i = 0; $i < $amount; $i++) {
      $element[$field_name][$i] = $this->getSingleArrayElement($field_name, $i, $property_schema, $data, $form_state);
    }
    $element['actions'] = $this->addArrayActions($amount, $field_name);

    return $element;
  }

  /**
   * Get amount of items to print.
   */
  private function getItemsNumber($form_state, $widget_array_info, $field_name, $data) {
    $amount = 1;
    if (!isset($widget_array_info[$field_name])) {
      $widget_array_info[$field_name]['amount'] = 1;
      $form_state->set('json_form_widget_array', $widget_array_info);
    }
    else {
      $amount = $widget_array_info[$field_name]['amount'];
    }

    if (
      !isset($widget_array_info[$field_name]['removing'])
      && !isset($widget_array_info[$field_name]['adding'])
      && is_array($data)
    ) {
      $count = count($data);
      $amount = ($count > $amount) ? $count : $amount;
      $widget_array_info[$field_name]['amount'] = $count;
      $form_state->set('json_form_widget_array', $widget_array_info);
    }
    return $amount;
  }

  /**
   * Helper function to add actions to array.
   */
  private function addArrayActions($amount, $field_name) {
    $actions['#type'] = ['actions'];
    $title = $this->t('Add one more');
    $actions['actions']['add'] = $this->getAction($title, 'json_form_widget_add_one', $field_name);

    // If there is more than one name, add the remove button.
    if ($amount > 1) {
      $title = $this->t('Remove one');
      $actions['actions']['remove_name'] = $this->getAction($title, 'json_form_widget_remove_one', $field_name);
    }
    return $actions;
  }

  /**
   * Helper function to get action.
   */
  private function getAction($title, $function, $field_name) {
    return [
      '#type' => 'submit',
      '#value' => $title,
      '#submit' => [$function],
      '#name' => $field_name,
      '#ajax' => [
        'callback' => [$this, 'addmoreCallback'],
        'wrapper' => $field_name . '-fieldset-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];
  }

  /**
   * Handle single element from array.
   *
   * Chooses whether element is simple or complex.
   */
  public function getSingleArrayElement($field_name, $i, $property_schema, $data, $form_state) {
    if (isset($property_schema->items->properties)) {
      // Return complex.
      return $this->getSingleComplexArrayElement($field_name, $i, $property_schema, $data, $form_state);
    }
    else {
      // Return simple.
      return $this->getSingleSimpleArrayElement($field_name, $i, $property_schema, $data);
    }
  }

  /**
   * Returns single simple element from array.
   */
  public function getSingleSimpleArrayElement($field_name, $i, $property_schema, $data) {
    $element = [
      '#type' => 'textfield',
    ];
    if (isset($property_schema->items->title)) {
      $element['#title'] = $property_schema->items->title;
    }
    if (is_array($data) && isset($data[$i])) {
      $element['#default_value'] = $data[$i];
    }
    return $element;
  }

  /**
   * Returns single complex element from array.
   */
  public function getSingleComplexArrayElement($field_name, $i, $property_schema, $data, $form_state) {
    $value = isset($data[$i]) ? $data[$i] : '';
    $definition = [
      'name' => $field_name,
      'schema' => $property_schema->items,
    ];
    $element = $this->objectHelper->handleObjectElement($definition, $value, $form_state, $this->builder);
    return $element;
  }

}
