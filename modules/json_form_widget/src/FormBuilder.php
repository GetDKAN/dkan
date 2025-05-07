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
   */
  public object $schema;

  /**
   * Schema UI handler.
   */
  public SchemaUiHandler $schemaUiHandler;

  /**
   * Field types router.
   */
  protected FieldTypeRouter $router;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('json_form.router'),
      $container->get('json_form.schema_ui_handler'),
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\json_form_widget\FieldTypeRouter $router
   *   Field type router service.
   * @param \Drupal\json_form_widget\SchemaUiHandler $schema_ui_handler
   *   Schema UI handler service.
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
   * @param object|null $ui_schema
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
    if (!$this->schema || !isset($this->schema->properties)) {
      return [];
    }

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
    return $this->schemaUiHandler->getSchemaUi() ? $this->schemaUiHandler->applySchemaUi($form) : $form;
  }

}
