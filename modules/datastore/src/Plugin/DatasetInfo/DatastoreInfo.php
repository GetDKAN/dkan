<?php

declare(strict_types=1);

namespace Drupal\common\Plugin\DatasetInfo;

use Drupal\common\DatasetInfoPluginBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\datastore\DatastoreService;
use Drupal\datastore\Service\Info\ImportInfo;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\metastore\ResourceMapper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the dataset_info.
 *
 * @DatasetInfo(
 *   id = "datastore_info",
 *   label = @Translation("Datastore info"),
 *   description = @Translation("Datastore info for dataset.")
 * )
 */
class DatastoreInfo extends DatasetInfoPluginBase {

  /**
   * Import info service.
   */
  protected ImportInfo $importInfo;

  /**
   * Datastore service.
   */
  protected DatastoreService $datastore;

  /**
   * Resource mapper service.
   */
  protected ResourceMapper $resourceMapper;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\datastore\Service\Info\ImportInfo $importInfo
   *   Import info datastoer service.
   * @param \Drupal\datastore\DatastoreService $datastore
   *   Datastore service.
   * @param \Drupal\metastore\ResourceMapper $resourceMapper
   *   Resource mapper service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    ModuleHandlerInterface $moduleHandler,
    ImportInfo $importInfo,
    DatastoreService $datastore,
    ResourceMapper $resourceMapper,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition, $moduleHandler);
    $this->importInfo = $importInfo;
    $this->datastore = $datastore;
    $this->resourceMapper = $resourceMapper;
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
    $pluginDefinition,
  ) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('module_handler'),
      $container->get('dkan.datastore.import_info'),
      $container->get('dkan.datastore.service'),
      $container->get('metastore.resource_mapper'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function addDatasetInfo(array $info): array {
    foreach ($info['latest_revision']['distributions'] as &$distribution) {
      $this->addDistributionInfo($distribution);
    }
    return $info;
  }

  /**
   * Alter the distribution info.
   *
   * @param array $distribution
   *   The distribution info array from a dataset info array.
   */
  protected function addDistributionInfo(&$distribution): void {
    $identifier = $distribution['resource_id'];
    $version = $distribution['resource_version'];
    $import_info = $this->importInfo->getItem($identifier, $version);
    $fileMapper = $this->resourceMapper->get($identifier, ResourceLocalizer::LOCAL_FILE_PERSPECTIVE, $version);

    $distribution += [
      'fetcher_status' => $import_info->fileFetcherStatus,
      'fetcher_percent_done' => $import_info->fileFetcherPercentDone ?? 0,
      'file_path' => isset($fileMapper) ? $fileMapper->getFilePath() : 'not found',
      'importer_percent_done' => $import_info->importerPercentDone ?? 0,
      'importer_status' => $import_info->importerStatus,
      'importer_error' => $import_info->importerError,
      'table_name' => ($storage = $this->getStorage($identifier, $version)) ? $storage->getTableName() : NULL,
    ];
  }

  /**
   * Get the storage object for a resource.
   *
   * @param string $identifier
   *   Resource identifier.
   * @param string $version
   *   Resource version timestamp.
   *
   * @return null|\Drupal\datastore\Storage\DatabaseTable
   *   The Database table object, or NULL.
   */
  protected function getStorage(string $identifier, string $version) {
    try {
      $storage = $this->datastore->getStorage($identifier, $version);
    }
    catch (\Exception) {
      $storage = NULL;
    }
    return $storage;
  }

}
