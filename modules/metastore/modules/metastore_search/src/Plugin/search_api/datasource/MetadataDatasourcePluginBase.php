<?php

namespace Drupal\metastore_search\Plugin\search_api\datasource;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\TypedData\ComplexDataInterface;

use Drupal\metastore_search\ComplexData\Dataset;
use Drupal\metastore_search\ComplexData\DkanMetadataFacade;
use Drupal\metastore_search\MetadataStorageDefinition;
use Drupal\metastore\Storage\DataFactory;
use Drupal\node\Entity\Node;
use Drupal\search_api\Datasource\DatasourcePluginBase;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Represents a datasource which exposes DKAN data.
 *
 * @todo We should rely more in the metastore instead of direct
 * entity queries and direct connections to storage classes.
 */
abstract class MetadataDatasourcePluginBase extends DatasourcePluginBase {

  /**
   * Metadata field storage definition.
   *
   * @var \Drupal\metastore_search\MetadataStorageDefinitionInterface
   */
  protected $metadataStorageDefinition;

  /**
   * Page size for getItemIds pager.
   *
   * @var int
   */
  protected $pageSize;

  /**
   * Get the data type for this datasource.
   *
   * @return string
   *   Metadata data type.
   */
  abstract protected function getDataType(): string;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    DataFactory $datastorage_factory,
    EntityTypeManagerInterface $entity_type_manager,
    int $page_size = 250
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dataStorage = $datastorage_factory->getInstance($this->getDataType());
    $this->metadataStorageDefinition = new MetadataStorageDefinition($this->getDataType());
    $this->nodeQuery = $entity_type_manager->getStorage('node')->getQuery();
    $this->pageSize = $page_size;
  }

  /**
   * Container injection.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
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
      $pluginDefinition,
      $container->get('dkan.metastore.storage'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getItemIds($page = NULL) {
    $nids = $this->nodeQuery
      ->condition('status', 1)
      ->condition('type', 'data')
      ->condition('field_data_type', $this->getDataType())
      ->range($page * $this->pageSize, $this->pageSize)
      ->execute();

    $uuids = array_map(function ($id) {
      return Node::load($id)->uuid();
    }, $nids);

    return $uuids ?: NULL;
  }

  /**
   * {@inheritDoc}
   */
  public function load($id) {
    try {
      return new DkanMetadataFacade($this->metadataStorageDefinition, $this->dataStorage->retrievePublished($id));
    }
    catch (\Exception $e) {
      return NULL;
    }
  }

  /**
   * {@inheritDoc}
   */
  public function loadMultiple($ids) {
    return array_filter(array_map([$this, 'load'], array_combine($ids, $ids)));
  }

  /**
   * {@inheritDoc}
   */
  public function getItemId(ComplexDataInterface $item): ?string {
    return $item->get('identifier');
  }

  /**
   * {@inheritDoc}
   */
  public function getPropertyNames(): array {
    $this->metadataStorageDefintion->getPropertyNames();
  }
}
