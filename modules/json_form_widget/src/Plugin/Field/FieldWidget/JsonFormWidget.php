<?php

namespace Drupal\json_form_widget\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\ContentEntityFormInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\json_form_widget\FormBuilder;
use Drupal\json_form_widget\ValueHandler;
use Drupal\metastore\SchemaRetriever;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Plugin implementation of the 'json_form_widget'.
 *
 * @FieldWidget(
 *   id = "json_form_widget",
 *   module = "json_form_widget",
 *   label = @Translation("DKAN JSON Form"),
 *   field_types = {
 *     "string_long"
 *   }
 * )
 */
class JsonFormWidget extends JsonFormWidgetBase {

  /**
   * Default DKAN Data Schema.
   *
   * @var string
   */
  protected const DEFAULT_SCHEMA_ID = 'dataset';

  /**
   * DKAN SchemaRetriever.
   */
  protected SchemaRetriever $schemaRetriever;

  /**
   * The current request stack.
   */
  protected RequestStack $requestStack;

  /**
   * Constructs a JsonFormWidget object.
   *
   * @param string $plugin_id
   *   The plugin_id for the widget.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\json_form_widget\FormBuilder $builder
   *   The JsonFormBuilder service.
   * @param \Drupal\json_form_widget\ValueHandler $value_handler
   *   The JsonFormValueHandler service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Drupal request context service.
   * @param \Drupal\metastore\SchemaRetriever $schema_retriever
   *   The DKAN SchemaRetriever service.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    array $third_party_settings,
    FormBuilder $builder,
    ValueHandler $value_handler,
    RequestStack $request_stack,
    SchemaRetriever $schema_retriever,
  ) {
    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $third_party_settings,
      $builder,
      $value_handler,
    );
    $this->requestStack = $request_stack;
    $this->schemaRetriever = $schema_retriever;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('json_form.builder'),
      $container->get('json_form.value_handler'),
      $container->get('request_stack'),
      $container->get('dkan.metastore.schema_retriever'),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveSchema(FormStateInterface $form_state): object {
    $schema_id = $this->resolveSchemaId($form_state);
    try {
      // Attempt to retrieve the schema with the given ID.
      $schema = $this->retrieveSchema($schema_id);
    }
    catch (\Exception $e) {
      // If the schema is not found, throw an error.
      \Drupal::logger('json_form_widget')->error($e->getMessage());
      throw new BadRequestException($e->getMessage());
    }
    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveUiSchema(FormStateInterface $form_state): ?object {
    $schema_id = $this->resolveSchemaId($form_state) . '.ui';
    try {
      return $this->retrieveSchema($schema_id);
    }
    catch (\Exception $e) {
      // If the UI schema is not found, fall back to the default schema.
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    // Set the data type in the metadata entity.
    $type = $this->resolveSchemaId($form_state);
    $form_entity = $this->getFormEntity($form_state);
    if ($form_entity ?? FALSE) {
      $form_entity->set('field_data_type', $type);
    }

    return parent::formElement($items, $delta, $element, $form, $form_state);
  }

  /**
   * Retrieves the form entity from the form state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state object.
   *
   * @return \Drupal\Core\Entity\FieldableEntityInterface
   *   The form entity, or NULL if none found.
   *
   * @throws \RuntimeException
   *   If no valid form entity is found.
   */
  protected function getFormEntity(FormStateInterface $form_state): ?FieldableEntityInterface {
    $form_object = $form_state->getFormObject();
    if ($form_object instanceof ContentEntityFormInterface) {
      $form_entity = $form_object->getEntity();
      if ($form_entity instanceof FieldableEntityInterface) {
        return $form_entity;
      }
    }
    // Throw exception if no valid form entity found.
    throw new \RuntimeException('No valid form entity found. The JsonFormWidget must be used with a fieldable content entity.');
  }

  /**
   * Resolves the schema ID from the form state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return string
   *   The schema ID.
   */
  protected function resolveSchemaId(FormStateInterface $form_state): string {
    $form_entity = $this->getFormEntity($form_state);
    if ($form_entity->hasField('field_data_type')) {
      $schema_id = $form_entity->field_data_type->value ?? NULL;
    }
    if ($schema_id ?? FALSE) {
      return $schema_id;
    }

    // If the schema ID is not set, figure out the schema ID from the request
    // or the default.
    $request = $this->requestStack->getCurrentRequest();
    return $request->query->get('schema') ?? self::DEFAULT_SCHEMA_ID;
  }

  /**
   * Retrieves and validates a schema by its ID with optional suffix (.ui).
   *
   * @param string $schema_id
   *   DKAN Schema ID.
   *
   * @return object
   *   The schema object.
   *
   * @throws \RuntimeException
   *   When no valid schema is found.
   */
  protected function retrieveSchema(string $schema_id): object {
    $schema_json = $this->schemaRetriever->retrieve($schema_id);
    $schema = json_decode($schema_json);

    if ($schema) {
      return $schema;
    }

    throw new \RuntimeException("No valid schema found for {$schema_id}.");
  }

}
