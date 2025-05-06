<?php

namespace Drupal\json_form_widget;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form builder service.
 */
class FormBuilder implements ContainerInjectionInterface {

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
   * Inherited.
   *
   * @{inheritdocs}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('json_form.router'),
      $container->get('json_form.schema_ui_handler'),
    );
  }

  /**
   * Constructor.
   */
  public function __construct(
    FieldTypeRouter $router,
    SchemaUiHandler $schema_ui_handler,
  ) {
    $this->router = $router;
    $this->schemaUiHandler = $schema_ui_handler;
  }

  /**
   * Set schema and optionally UI schema.
   *
   * @param object $schema
   *   JSON Schema.
   * @param ?object $ui_schema
   *   JSON UI Schema.
   */
  public function setSchema(object $schema, ?object $ui_schema = NULL): void {
    $this->schema = $schema;
    $this->schemaUiHandler->setSchemaUi($ui_schema);
    $this->router->setSchema($schema);
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
    if ($this->schema && isset($this->schema->properties)) {
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
        return $this->schemaUiHandler->applySchemaUi($form);
      }
      return $form;
    }
    return [];
  }

}
