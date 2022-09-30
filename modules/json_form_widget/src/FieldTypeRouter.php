<?php

namespace Drupal\json_form_widget;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormState;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class FormBuilder.
 */
class FieldTypeRouter implements ContainerInjectionInterface {

  /**
   * Schema.
   *
   * @var object
   */
  protected $schema;

  /**
   * String Helper.
   *
   * @var \Drupal\json_form_widget\StringHelper
   */
  protected $stringHelper;

  /**
   * Object Helper.
   *
   * @var \Drupal\json_form_widget\ObjectHelper
   */
  protected $objectHelper;

  /**
   * Array Helper.
   *
   * @var \Drupal\json_form_widget\ArrayHelper
   */
  protected $arrayHelper;

  /**
   * Integer Helper.
   *
   * @var \Drupal\json_form_widget\IntegerHelper
   */
  protected $integerHelper;

  /**
   * Inherited.
   *
   * @{inheritdocs}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('json_form.string_helper'),
      $container->get('json_form.object_helper'),
      $container->get('json_form.array_helper'),
      $container->get('json_form.integer_helper'),
    );
  }

  /**
   * Constructor.
   */
  public function __construct(StringHelper $string_helper, ObjectHelper $object_helper, ArrayHelper $array_helper, IntegerHelper $integer_helper) {
    $this->stringHelper = $string_helper;
    $this->objectHelper = $object_helper;
    $this->arrayHelper = $array_helper;
    $this->integerHelper = $integer_helper;

    $this->stringHelper->setBuilder($this);
    $this->objectHelper->setBuilder($this);
    $this->arrayHelper->setBuilder($this);
    $this->integerHelper->setBuilder($this);
  }

  /**
   * Set schema.
   *
   * @codeCoverageIgnore
   */
  public function setSchema($schema) {
    $this->schema = $schema;
  }

  /**
   * Get schema.
   */
  public function getSchema() {
    return $this->schema;
  }

  /**
   * Get form element based on property type.
   */
  public function getFormElement($type, $definition, $data, $object_schema = NULL, $form_state = NULL, array $context = []) {
    $context[] = $definition['name'];
    $form_state ??= new FormState();

    switch ($type) {
      case 'object':
        return $this->objectHelper->handleObjectElement($definition, $data, $form_state, $context, $this);

      case 'array':
        return $this->arrayHelper->handleArrayElement($definition, $data, $form_state, $context);

      case 'string':
        return $this->stringHelper->handleStringElement($definition, $data, $object_schema);

      case 'integer':
        return $this->integerHelper->handleIntegerElement($definition, $data, $object_schema);
    }
  }

}
