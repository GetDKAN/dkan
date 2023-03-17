<?php

namespace Drupal\metastore\Plugin\MetastoreReferenceType;

use Contracts\FactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Reference\ReferenceTypeBase;
use Drupal\metastore\ResourceMapper;
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
   * Resource mapper service.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  protected ResourceMapper $resourceMapper;

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
   * @param \Drupal\metastore\ResourceMapper $resourceMapper
   *   Resource mapper service.
   */
  public function __construct(
    array $config,
    $pluginId,
    $pluginDefinition,
    LoggerChannelFactoryInterface $loggerFactory,
    FactoryInterface $storageFactory,
    ResourceMapper $resourceMapper
  ) {
    parent::__construct($config, $pluginDefinition, $pluginId, $loggerFactory);
    $this->storage = $storageFactory->getInstance($this->schemaId());
    $this->resourceMapper = $resourceMapper;
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
    $resourceMapper = $container->get('dkan.metastore.resource_mapper');
    return new static($config, $pluginId, $pluginDefinition, $loggerFactory, $storageFactory, $resourceMapper);
  }

  /**
   * {@inheritdoc}
   */
  public function reference($value): string {
    // First see if there is an existing item that matches the value.
    $identifier = $this->checkExistingReference($value);
    // In some cases, we always want to create and save a new referenced item.
    if (!$identifier || $this->newRevision()) {
      $identifier = $this->createPropertyReference($value);
    }

    return $identifier;
  }

  /**
   * Should a new revision of this item be saved, even if it exists already?
   *
   * @return bool
   *   True if a new revision should be created regardless.
   *
   * @todo Refactor; this logic should be absracted and not distribution/resource-specific.
   */
  protected function newRevision() {
    if ($this->property() == 'distribution' && $this->resourceMapper->newRevision()) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function dereference(string $identifier, bool $showId = FALSE) {
    try {
      $value = $this->storage->retrieve($identifier);
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
    $identifier = $this->storage->retrieveByHash(Service::metadataHash($value)) ?? NULL;
    if ($identifier && !$this->storage->isPublished($identifier)) {
      $this->storage->publish($identifier);
    }

    return $identifier;
  }

  /**
   * Creates a new value reference for that property id in a data node.
   *
   * @param string|object $value
   *   The property's value.
   *
   * @return string
   *   The new reference's uuid, or NULL.
   *
   * @todo Replace identifier/data structure.
   */
  protected function createPropertyReference($value): string {
    // Create json metadata for the reference.
    $data = new \stdClass();
    $data->identifier = (new Uuid5())->generate($this->schemaId, $value);
    $data->data = $value;
    $json = json_encode($data);

    // Create node to store this reference.
    $identifier = $this->storage->store($json, $data->identifier);
    return $identifier;
  }

}
