<?php

namespace Drupal\json_form_widget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class JsonFormArrayHelper.
 */
class JsonFormArrayHelper implements ContainerInjectionInterface {
  use StringTranslationTrait;

  /**
   * Object Helper.
   *
   * @var \Drupal\json_form_widget\JsonFormObjectHelper
   */
  protected $objectHelper;

  /**
   * Builder object.
   *
   * @var \Drupal\json_form_widget\JsonFormBuilder
   */
  protected $builder;

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
  public function __construct(JsonFormObjectHelper $object_helper) {
    $this->objectHelper = $object_helper;
  }

  /**
   * Callback for both ajax-enabled buttons.
   *
   * Selects and returns the fieldset with the names in it.
   */
  public function addmoreCallback(array &$form, FormStateInterface $form_state) {
    $field = $form_state->getTriggeringElement();
    $element = $form;
    foreach ($field['#array_parents'] as $key => $parent) {
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
  public function handleArrayElement($property_schema, $field_name, $data, $form_state, $builder) {
    $this->builder = $builder;
    // Save info about the arrays.
    $widget_array_info = $form_state->get('json_form_widget_array');
    $form_state->set('json_form_widget_schema', $this->schema);
    if (!isset($widget_array_info[$field_name])) {
      $widget_array_info[$field_name]['amount'] = 1;
      $form_state->set('json_form_widget_array', $widget_array_info);
      $amount = 1;
    } else {
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

    $element['actions'] = [
      '#type' => 'actions',
    ];
    $element['actions']['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add one more'),
      '#submit' => ['json_form_widget_add_one'],
      '#name' => $field_name,
      '#ajax' => [
        'callback' => [$this, 'addmoreCallback'],
        'wrapper' => $field_name . '-fieldset-wrapper',
      ],
      '#limit_validation_errors' => [],
    ];
    // If there is more than one name, add the remove button.
    if ($amount > 1) {
      $element['actions']['remove_name'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove one'),
        '#submit' => ['json_form_widget_remove_one'],
        '#name' => $field_name,
        '#ajax' => [
          'callback' => [$this, 'addmoreCallback'],
          'wrapper' => $field_name . '-fieldset-wrapper',
        ],
        '#limit_validation_errors' => [],
      ];
    }

    return $element;
  }

  /**
   * Handle single element from array.
   *
   * Chooses wether element is simple or complex.
   */
  public function getSingleArrayElement($field_name, $i, $property_schema, $data, $form_state) {
    if (isset($property_schema->items->properties)) {
      // Return complex.
      return $this->getSingleComplexArrayElement($field_name, $i, $property_schema, $data, $form_state);
    } else {
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
    $element = $this->objectHelper->handleObjectElement($property_schema->items, $field_name, $value, $form_state, $this->builder);
    return $element;
  }

}
