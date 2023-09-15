<?php

namespace Drupal\json_form_widget;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\metastore\SchemaRetriever;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * Form builder service.
 */
class FormBuilder implements ContainerInjectionInterface {

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
   * Field types router.
   *
   * @var \Drupal\json_form_widget\FieldTypeRouter
   */
  protected $router;

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
      $container->get('json_form.router'),
      $container->get('json_form.schema_ui_handler'),
      $container->get('logger.factory')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(SchemaRetriever $schema_retriever, FieldTypeRouter $router, SchemaUiHandler $schema_ui_handler, LoggerChannelFactory $logger_factory) {
    $this->schemaRetriever = $schema_retriever;
    $this->router = $router;
    $this->schemaUiHandler = $schema_ui_handler;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * Set schema.
   *
   * @param string $schema_name
   *   Metadata schema name.
   */
  public function setSchema(string $schema_name): void {
    try {
      $schema = $this->schemaRetriever->retrieve($schema_name);
      $this->schema = json_decode($schema);
      $this->schemaUiHandler->setSchemaUi($schema_name);
      $this->router->setSchema($this->schema);
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
        $definition = [
          'name' => $property,
          'schema' => $this->schema->properties->{$property},
        ];
        $form[$property] = $this->router->getFormElement($type, $definition, $value, NULL, $form_state, []);
      }
      if ($this->schemaUiHandler->getSchemaUi()) {
        $form_updated = $this->schemaUiHandler->applySchemaUi($form);
        return $form_updated;
      }
      return $form;
    }
    return [];
  }

}
