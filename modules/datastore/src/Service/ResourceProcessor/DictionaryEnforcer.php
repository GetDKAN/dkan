<?php

namespace Drupal\datastore\Service\ResourceProcessor;

use Drupal\common\DataResource;
use Drupal\datastore\DataDictionary\AlterTableQueryBuilderInterface;
use Drupal\datastore\Service\ResourceProcessorInterface;
use Drupal\datastore\Storage\DatabaseTableFactory;
use Drupal\metastore\MetastoreService;
use Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface;

use RootedData\RootedJsonData;

/**
 * Apply specified data-dictionary to datastore belonging to specified dataset.
 */
class DictionaryEnforcer implements ResourceProcessorInterface {

  /**
   * Alter table query builder service.
   *
   * @var \Drupal\datastore\DataDictionary\AlterTableQueryBuilderInterface
   */
  protected $alterTableQueryBuilder;

  /**
   * Data dictionary discovery service.
   *
   * @var \Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface
   */
  protected $dataDictionaryDiscovery;

  /**
   * The metastore service.
   *
   * @var \Drupal\metastore\MetastoreService
   */
  protected $metastore;

  /**
   * The metastore resource mapper service.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  protected $resourceMapper;

  /**
   * Database table factory service.
   *
   * @var \Drupal\datastore\Storage\DatabaseTableFactory
   */
  protected $databaseTableFactory;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param \Drupal\datastore\DataDictionary\AlterTableQueryBuilderInterface $alter_table_query_builder
   *   The alter table query factory service.
   * @param \Drupal\metastore\MetastoreService $metastore
   *   The metastore service.
   * @param \Drupal\metastore\DataDictionary\DataDictionaryDiscoveryInterface $data_dictionary_discovery
   *   The data-dictionary discovery service.
   * @param \Drupal\datastore\Storage\DatabaseTableFactory $table_factory
   *   The datastore database table factory service.
   */
  public function __construct(
    AlterTableQueryBuilderInterface $alter_table_query_builder,
    MetastoreService $metastore,
    DataDictionaryDiscoveryInterface $data_dictionary_discovery,
    DatabaseTableFactory $table_factory,
  ) {
    $this->metastore = $metastore;
    $this->dataDictionaryDiscovery = $data_dictionary_discovery;
    $this->alterTableQueryBuilder = $alter_table_query_builder;
    $this->databaseTableFactory = $table_factory;
  }

  /**
   * Retrieve dictionary and datastore table details; apply dictionary to table.
   *
   * @param \Drupal\common\DataResource $resource
   *   DKAN Resource.
   */
  public function process(DataResource $resource): void {
    // Ensure data-dictionaries are enabled before attempting to process item.
    if (DataDictionaryDiscoveryInterface::MODE_NONE === $this->dataDictionaryDiscovery->getDataDictionaryMode()) {
      return;
    }

    // Get data-dictionary for the given resource.
    $dictionary = $this->getDataDictionaryForResource($resource);
    // Retrieve name of datastore table for resource.
    $table = $this->databaseTableFactory->getInstance('', ['resource' => $resource]);
    $datastore_table = $table->getTableName();

    $this->applyDictionary($dictionary, $datastore_table);
  }

  /**
   * Retrieve the data-dictionary metadata object for the given resource.
   *
   * @param \Drupal\common\DataResource $resource
   *   DKAN Resource.
   *
   * @return \RootedData\RootedJsonData
   *   Data-dictionary metadata.
   *
   * @throws \Drupal\datastore\Service\ResourceProcessor\ResourceDoesNotHaveDictionary
   *   Thrown when the resource does not have an associated data dictionary.
   */
  protected function getDataDictionaryForResource(DataResource $resource): RootedJsonData {
    $resource_id = $resource->getIdentifier();
    $resource_version = $resource->getVersion();
    $dictionary_id = $this->dataDictionaryDiscovery->dictionaryIdFromResource($resource_id, $resource_version);

    if (!isset($dictionary_id)) {
      throw new ResourceDoesNotHaveDictionary($resource_id, $resource_version);
    }
    return $this->metastore->get('data-dictionary', $dictionary_id);
  }

  /**
   * Apply data types in the given dictionary fields to the given datastore.
   *
   * @param \RootedData\RootedJsonData $dictionary
   *   Data-dictionary.
   * @param string $datastore_table
   *   SQL datastore table name.
   */
  public function applyDictionary(RootedJsonData $dictionary, string $datastore_table): void {
    $this->alterTableQueryBuilder
      ->setTable($datastore_table)
      ->addDataDictionary($dictionary)
      ->getQuery()
      ->execute();
  }

  /**
   * Returning data dictionary fields from schema.
   *
   * @param string|null $identifier
   *   A resource's identifier. Used when in reference mode.
   *
   * @return array|null
   *   An array of dictionary fields or null if no dictionary is in use.
   */
  public function returnDataDictionaryFields(?string $identifier = NULL): ?array {
    // Get data dictionary mode.
    $dd_mode = $this->dataDictionaryDiscovery->getDataDictionaryMode();
    // Get data dictionary info.
    switch ($dd_mode) {
      case "sitewide":
        $dictionary_id = $this->dataDictionaryDiscovery->getSitewideDictionaryId();
        break;

      case "reference":
        $resource = DataResource::getIdentifierAndVersion($identifier);
        $dictionary_id = $this->dataDictionaryDiscovery->dictionaryIdFromResource($resource[0], $resource[1]);
        break;

      default:
        return NULL;
    }

    return $dictionary_id ? $this->metastore->get('data-dictionary', $dictionary_id)->{"$.data.fields"} : NULL;
  }

}
