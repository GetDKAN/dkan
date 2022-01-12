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
      $entity_type_id = $datasource->getPluginId();
      // We only support indexes with the dkan_dataset datasource.
      if ($entity_type_id === 'dkan_dataset') {
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
      $item_object = $items[$item_id]->getOriginalObject();
      // Only alter dataset items.
      if (!$item_object instanceof Dataset) {
        continue;
      }

      // Extract dataset ID.
      $id_parts = Utility::splitCombinedId($item_id);
      $dataset_id = $id_parts[1];
      // Ensure item is valid.
      if (!$this->isValid($dataset_id)) {
        \Drupal::logger('search_api')->notice(print_r($dataset_id, TRUE));
        unset($items[$item_id]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  abstract public function isValid(string $dataset_id): bool;

}
