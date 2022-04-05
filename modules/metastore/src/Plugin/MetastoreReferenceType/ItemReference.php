<?php

namespace Drupal\metastore\Plugin\MetastoreReferenceType;

use Contracts\FactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Reference\ReferenceTypeBase;
use Drupal\metastore\Service;
use Drupal\metastore\Service\Uuid5;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * API Docs common base.
 *
 * @MetastoreReferenceType(
 *  id = "item",
 *  description = @Translation("Metastore item reference definition.")
 * )
 */
class ItemReference extends ReferenceTypeBase {

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
   * @param \Contracts\FactoryInterface $storageFactory
   *   Metastore storage factory.
   */
  public function __construct(
    array $config,
    $pluginId,
    $pluginDefinition,
    LoggerChannelFactoryInterface $loggerFactory,
    FactoryInterface $storageFactory
  ) {
    $this->storageFactory = $storageFactory;
    parent::__construct($config, $pluginDefinition, $pluginId, $loggerFactory);
  }

  /**
   * Container injection.
   *
   * @param \Drupal\common\Plugin\ContainerInterface $container
   *   The service container.
   * @param array $config
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
    array $config,
    $pluginId,
    $pluginDefinition
  ) {
    $loggerFactory = $container->get('logger.factory');
    $storageFactory = $container->get('dkan.metastore.storage');
    return new static($config, $pluginId, $pluginDefinition, $loggerFactory, $storageFactory);
  }

  /**
   * {@inheritdoc}
   */
  public function reference($value): string {
    $identifier = $this->checkExistingReference($value);
    if (!$identifier) {
      $identifier = $this->createPropertyReference($value);
    }
    if ($identifier) {
      return $identifier;
    }
    $this->logger->error(
      'Neither found an existing nor could create a new reference for property_id: @property_id with value: @value',
      [
        '@property_id' => $this->property(),
        '@value' => var_export($value, TRUE),
      ]
    );
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function dereference(string $identifier, bool $showId = FALSE) {
    $storage = $this->storageFactory->getInstance($this->schemaId());
    try {
      $value = $storage->retrieve($identifier);
    }
    catch (MissingObjectException $exception) {
      $value = FALSE;
    }

    if (!$value) {
      // If a property node was not found, it most likely means it was deleted
      // while still being referenced.
      $this->logger->error(
        'Property @property_id reference @identifier not found',
        [
          '@property_id' => $this->property(),
          '@identifier' => var_export($identifier, TRUE),
        ]
      );
  
      return NULL;
    }
    $metadata = json_decode($value);
    // Just return the contents of "data" unless we're requesting to show IDs.
    return $showId ? $metadata : $metadata->data;

  }

  /**
   * Checks for an existing value reference for that property id.
   *
   * @param string|object $value
   *   The property's value used to find an existing reference.
   *
   * @return string|null
   *   The existing reference's uuid, or NULL if not found.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function checkExistingReference($value) {
    $storage = $this->storageFactory->getInstance($this->schemaId());
    $identifier = $storage->retrieveByHash(Service::metadataHash($value)) ?? NULL;
    if ($identifier && !$storage->isPublished($identifier)) {
      $storage->publish($identifier);
    }

    return $identifier;
  }

  /**
   * Creates a new value reference for that property id in a data node.
   *
   * @param string|object $value
   *   The property's value.
   *
   * @return string|null
   *   The new reference's uuid, or NULL.
   *
   * @todo Replace identifier/data structure.
   */
  protected function createPropertyReference($value) {
    // Create json metadata for the reference.
    $data = new \stdClass();
    $data->identifier = (new Uuid5())->generate($this->schemaId, $value);
    $data->data = $value;
    $json = json_encode($data);

    // Create node to store this reference.
    $storage = $this->storageFactory->getInstance($this->schemaId());
    $identifier = $storage->store($json, $data->identifier);
    return $identifier;
  }

}
