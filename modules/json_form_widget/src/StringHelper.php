<?php

namespace Drupal\json_form_widget;

use Drupal\Component\Utility\EmailValidator;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * JSON form widget string helper service.
 */
class StringHelper implements ContainerInjectionInterface {
  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * Builder object.
   *
   * @var \Drupal\json_form_widget\FieldTypeRouter
   */
  public $builder;

  /**
   * Email validator service.
   *
   * @var \Drupal\Component\Utility\EmailValidator
   */
  public $emailValidator;

  /**
   * Inherited.
   *
   * @{inheritdocs}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('email.validator')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(EmailValidator $email_validator) {
    $this->emailValidator = $email_validator;
  }

  /**
   * Set builder.
   */
  public function setBuilder($builder) {
    $this->builder = $builder;
  }

  /**
   * Handle form element for a string.
   */
  public function handleStringElement($definition, $data, $object_schema = FALSE) {
    $property = $definition['schema'];
    $field_name = $definition['name'];
    // Basic definition.
    $element = [
      '#type' => $this->getElementType($property),
    ];
    $element['#title'] = isset($property->title) ? $property->title : '';
    $element['#description'] = isset($property->description) ? $property->description : '';
    $element['#description_display'] = 'before';
    $element['#default_value'] = $this->getDefaultValue($data, $property);

    // Check if the field is required.
    $element_schema = $object_schema ? $object_schema : $this->builder->getSchema();
    $element['#required'] = $this->checkIfRequired($field_name, $element_schema);

    // Add options if element type is select.
    if ($element['#type'] === 'select') {
      $element['#options'] = $this->getSelectOptions($property);
    }

    // Add extra validate if element type is email.
    if ($element['#type'] === 'email') {
      $element['#element_validate'][] = [$this, 'validateEmail'];
      $element['#default_value'] = ltrim($element['#default_value'] ?? '', 'mailto:');
    }

    return $element;
  }

  /**
   * Get type of element.
   */
  public function getElementType($property) {
    if (isset($property->format) && $property->format == 'uri') {
      return 'url';
    }
    elseif (isset($property->enum)) {
      return 'select';
    }
    elseif (isset($property->pattern) && preg_match('/\^mailto:/', $property->pattern) > 0) {
      return 'email';
    }
    return 'textfield';
  }

  /**
   * Get default value for element.
   */
  public function getDefaultValue($data, $property) {
    if ($data) {
      return $data;
    }
    elseif (isset($property->default)) {
      return $property->default;
    }
  }

  /**
   * Check if field is required based on its schema.
   */
  public function checkIfRequired($name, $element_schema) {
    if (isset($element_schema->required) && in_array($name, $element_schema->required)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Get array of options for a property.
   */
  public function getSelectOptions($property) {
    $options = [];
    if (isset($property->enumNames)) {
      $options = array_combine($property->enum, $property->enumNames);
    }
    else {
      $options = array_combine($property->enum, $property->enum);
    }
    return $options;
  }

  /**
   * Validate email field.
   */
  public function validateEmail(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = trim($element['#value']);
    $form_state->setValueForElement($element, $value);

    if (empty($value)) {
      return;
    }

    if ($value !== '' && !$this->emailValidator->isValid($value) || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
      $form_state->setError($element, $this->t('The email address %mail is not valid.', ['%mail' => $value]));
    }
  }

}
