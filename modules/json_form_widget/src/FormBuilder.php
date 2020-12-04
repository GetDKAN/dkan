<?php

namespace Drupal\json_form_widget;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\metastore\SchemaRetriever;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Class FormBuilder.
 */
class FormBuilder implements ContainerInjectionInterface {
  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * SchemaRetriever.
   *
   * @var \Drupal\metastore\SchemaRetriever
   */
  protected $schemaRetriever;

  /**
   * Schema.
   *
   * @var object
   */
  public $schema;

  /**
   * Schema UI handler.
   *
   * @var object
   */
  public $schemaUiHandler;

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
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $loggerFactory;

  /**
   * Inherited.
   *
   * @{inheritdocs}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.metastore.schema_retriever'),
      $container->get('json_form.string_helper'),
      $container->get('json_form.object_helper'),
      $container->get('json_form.array_helper'),
      $container->get('json_form.schema_ui_handler'),
      $container->get('logger.factory')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(SchemaRetriever $schema_retriever, StringHelper $string_helper, ObjectHelper $object_helper, ArrayHelper $array_helper, SchemaUiHandler $schema_ui_handler, LoggerChannelFactory $logger_factory) {
    $this->schemaRetriever = $schema_retriever;
    $this->stringHelper = $string_helper;
    $this->objectHelper = $object_helper;
    $this->arrayHelper = $array_helper;
    $this->schemaUiHandler = $schema_ui_handler;
    $this->loggerFactory = $logger_factory;

    $this->arrayHelper->setBuilder($this);
    $this->stringHelper->setBuilder($this);
  }

  /**
   * Set schema.
   *
   * @codeCoverageIgnore
   */
  public function setSchema($schema_name) {
    try {
      $schema = $this->schemaRetriever->retrieve($schema_name);
      $this->schema = json_decode($schema);
      $this->schemaUiHandler->setSchemaUi($schema_name);
    }
    catch (\Exception $exception) {
      $this->loggerFactory->get('json_form_widget')->notice("The JSON Schema for $schema_name does not exist.");
    }
  }

  /**
   * Get schema.
   */
  public function getSchema() {
    return $this->schema;
  }

  /**
   * Build form based on schema.
   */
  public function getJsonForm($data, $form_state = NULL) {
    if ($this->schema) {
      $properties = array_keys((array) $this->schema->properties);

      foreach ($properties as $property) {
        $type = $this->schema->properties->{$property}->type ?? "string";
        $value = $data->{$property} ?? NULL;
        $form[$property] = $this->getFormElement($type, $property, $this->schema->properties->{$property}, $value, FALSE, $form_state);
      }
      if ($this->schemaUiHandler->getSchemaUi()) {
        $form_updated = $this->schemaUiHandler->applySchemaUi($form);
        return $form_updated;
      }
      return $form;
    }
    return [];
  }

  /**
   * Get form element based on property type.
   */
  public function getFormElement($type, $property_name, $property, $data, $object_schema = FALSE, $form_state = NULL) {
    switch ($type) {
      case 'object':
        return $this->objectHelper->handleObjectElement($property, $property_name, $data, $form_state, $this);

      case 'array':
        return $this->arrayHelper->handleArrayElement($property, $property_name, $data, $form_state);

      case 'string':
        return $this->stringHelper->handleStringElement($property, $property_name, $data, $object_schema);
    }
  }

}
