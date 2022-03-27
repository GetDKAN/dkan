<?php

namespace Drupal\metastore\Reference;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Simple container for reference information.
 */
abstract class ReferenceTypeBase extends PluginBase implements ReferenceTypeInterface {

  /**
   * DKAN schema ID.
   *
   * @var string|null
   */
  protected $schemaId;

  /**
   * Metadata context (the current metadata object).
   *
   * @var mixed
   */
  protected $context;

  /**
   * Constructs a ReferenceType object.
   *
   * @param array $config
   *   Details for reference definition. Possible keys:
   *   - schemaId: For some reference definitions, a schemaId must be specified.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   Logger factory service.
   */
  public function __construct(
      array $config,
      $pluginId,
      $pluginDefinition,
      LoggerChannelFactoryInterface $loggerFactory
    ) {
    $this->property = $config['property'];
    $this->schemaId = $config['schemaId'] ?? NULL;
    $this->logger = $loggerFactory->get('metastore');
    parent::__construct($config, $pluginId, $pluginDefinition);
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
   * {@inheritdoc}
   */
  public function property(): string {
    return $this->property;
  }

  /**
   * {@inheritdoc}
   */
  public function type(): string {
    return $this->pluginDefinition['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function schemaId(): ?string {
    return $this->schemaId;
  }

  /**
   * {@inheritdoc}
   */
  public function setContext($context): self {
    $this->context = $context;
    return $this;
  }

}
