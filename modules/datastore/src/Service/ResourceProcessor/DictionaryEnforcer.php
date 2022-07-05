<?php

namespace Drupal\datastore\Service\ResourceProcessor;

use Drupal\common\Resource;
use Drupal\datastore\DataDictionary\AlterTableQueryFactoryInterface;
use Drupal\datastore\Service\ResourceProcessorInterface;
use Drupal\metastore\Service as MetastoreService;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;

use RootedData\RootedJsonData;

/**
 * Apply specified data-dictionary to datastore belonging to specified dataset.
 */
class DictionaryEnforcer implements ResourceProcessorInterface {

  /**
   * Datastore table query service.
   *
   * @var \Drupal\datastore\DataDictionary\AlterTableQueryFactoryInterface
   */
  protected $alterTableQueryFactory;

  /**
   * Data dictionary discovery service.
   *
   * @var \Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface
   */
  protected $dataDictionaryDiscovery;

  /**
   * The metastore service.
   *
   * @var \Drupal\metastore\Service
   */
  protected $metastore;

  /**
   * The metastore resource mapper service.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  protected $resourceMapper;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\datastore\DataDictionary\AlterTableQueryFactoryInterface $alter_table_query_factory
   *   The alter table query factory service.
   * @param \Drupal\metastore\Service $metastore
   *   The metastore service.
   * @param \Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface $data_dictionary_discovery
   *   The data-dictionary discovery service.
   */
  public function __construct(
    AlterTableQueryFactoryInterface $alter_table_query_factory,
    MetastoreService $metastore,
    DataDictionaryDiscoveryInterface $data_dictionary_discovery
  ) {
    $this->metastore = $metastore;
    $this->dataDictionaryDiscovery = $data_dictionary_discovery;
    $this->alterTableQueryFactory = $alter_table_query_factory;
  }

  /**
   * Retrieve dictionary and datastore table details; apply dictionary to table.
   *
   * @param \Drupal\common\Resource $resource
   *   DKAN Resource.
   */
  public function process(Resource $resource): void {
    // Ensure a valid date-dictionary mode has been selected before proceeding.
    if ($this->dataDictionaryDiscovery->getDataDictionaryMode() === DataDictionaryDiscoveryInterface::MODE_NONE) {
      return;
    }
    // Retrieve name of datastore table for resource.
    $datastore_table = $resource->getTableName();
    // Get data-dictionary for the given resource.
    $dictionary = $this->getDataDictionaryForResource($resource);
    // Extract data-dictionary field types.
    $dictionary_fields = $dictionary->{'$.data.fields'};

    $this->applyDictionary($dictionary_fields, $datastore_table);
  }

  /**
   * Retrieve the data-dictionary metadata object for the given resource.
   *
   * @param \Drupal\common\Resource $resource
   *   DKAN Resource.
   *
   * @return \RootedData\RootedJsonData
   *   Data-dictionary metadata.
   */
  protected function getDataDictionaryForResource(Resource $resource): RootedJsonData {
    $resource_id = $resource->getIdentifier();
    $resource_version = $resource->getVersion();
    $dict_id = $this->dataDictionaryDiscovery->dictionaryIdFromResource($resource_id, $resource_version);

    if (!isset($dict_id)) {
      throw new \UnexpectedValueException(sprintf('No data-dictionary found for resource with id "%s" and version "%s".', $resource_id, $resource_version));
    }
    return $this->metastore->get('data-dictionary', $dict_id);
  }

  /**
   * Apply data types in the given dictionary fields to the given datastore.
   *
   * @param array $dictionary_fields
   *   Data dictionary fields.
   * @param string $datastore_table
   *   Mysql table name.
   */
  public function applyDictionary(array $dictionary_fields, string $datastore_table): void {
    $this->alterTableQueryFactory
      ->getQuery($datastore_table, $dictionary_fields)
      ->applyDataTypes();
  }

}
