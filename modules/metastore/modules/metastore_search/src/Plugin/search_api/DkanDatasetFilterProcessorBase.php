<?php

namespace Drupal\metastore_search\Plugin\search_api;

use Drupal\metastore_search\ComplexData\Dataset;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Utility\Utility;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Excludes datasets from dkan dataset indexes based on condition.
 */
abstract class DkanDatasetFilterProcessorBase extends ProcessorPluginBase implements DkanDatasetFilterProcessorInterface {

  /**
   * Dataset data storage instance.
   *
   * @var \Drupal\metastore\Storage\Data
   */
  protected $dataStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    $this->dataStorage = $configuration['dkan_data_storage'];
    unset($configuration['dkan_data_storage']);

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $dataStorageFactory = $container->get('dkan.metastore.storage');
    $configuration['dkan_data_storage'] = $dataStorageFactory->getInstance('dataset');

    return parent::create($container, $configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function supportsIndex(IndexInterface $index) {
    foreach ($index->getDatasources() as $datasource) {
      $datasource_id = $datasource->getPluginId();
      // We only support indexes with the dkan_dataset datasource.
      if ($datasource_id === 'dkan_dataset') {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function alterIndexedItems(array &$items) {
    foreach (array_keys($items) as $item_id) {
      // Retrieve item object.
      $item_object = $items[$item_id]->getOriginalObject();
      // Extract dataset ID.
      $id_parts = Utility::splitCombinedId($item_id);
      $dataset_id = $id_parts[1];

      // Filter out invalid datasets.
      if ($item_object instanceof Dataset && !$this->isValid($dataset_id)) {
        unset($items[$item_id]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  abstract public function isValid(string $dataset_id): bool;

}
