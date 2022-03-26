<?php

namespace Drupal\metastore\Plugin;

use Drupal\metastore\Reference\ReferenceDefinitionInterface;

/**
 * Simple container for reference information.
 */
abstract class ReferenceDefinitionBase implements ReferenceDefinitionInterface {

  /**
   * The property name.
   *
   * @var string
   */
  private $property;

  /**
   * Reference type.
   *
   * @var mixed
   */
  private $type;

  /**
   * DKAN schema ID.
   *
   * @var string|null
   */
  private $schemaId;

  /**
   * Constructs a ReferenceDefinition object.
   *
   * @param array $configuration
   *   Details for reference definition. Possible keys:
   *   - schemaId: For some reference definitions, a schemaId must be specified
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * Container injection.
   *
   * @param \Drupal\common\Plugin\ContainerInterface $container
   *   The service container.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   *
   * @return static
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $pluginId,
    $pluginDefinition
  ) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition
    );
  }

  /**
   * Retrieve the @description property from the annotation and return it.
   *
   * @return string
   *   Description.
   */
  public function description() {
    return $this->pluginDefinition['description'];
  }


  /**
   * Get the property name.
   *
   * @return string
   *   Property name for reference.
   */
  public function property(): string {
    return $this->property;
  }

  /**
   * Get the property type.
   *
   * @return string
   *   Current options: schema, id, resource.
   */
  public function type(): string {
    return $this->type;
  }

  /**
   * Get the schema ID referenced.
   *
   * @return string|null
   *   A valid schema ID, or NULL if n/a.
   */
  public function schemaId(): ?string {
    return $this->schemaId;
  }

}
