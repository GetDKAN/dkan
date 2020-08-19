<?php

namespace Drupal\metastore_search\Plugin\search_api\datasource;

use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\metastore_search\ComplexData\Dataset;
use Drupal\node\Entity\Node;
use Drupal\search_api\Datasource\DatasourcePluginBase;

/**
 * Represents a datasource which exposes DKAN data.
 *
 * @SearchApiDatasource(
 *   id = "dkan_dataset",
 *   label = "DKAN Dataset",
 * )
 */
class DkanDataset extends DatasourcePluginBase {

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getPropertyDefinitions() {
    return Dataset::definition();
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getItemIds($page = NULL) {
    $pageSize = 250;
    $ids = [];
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'data')
      ->condition('field_data_type', 'dataset');

    $total = $query->count()->execute();
    $pages = floor($total / $pageSize);

    if ($page <= $pages) {

      $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('type', 'data')
        ->condition('field_data_type', 'dataset')
        ->range($page * $pageSize, $pageSize);
      $nids = $query->execute();

      foreach ($nids as $id) {
        $node = Node::load($id);
        $ids[] = $node->uuid();
      }

      return $ids;
    }
    return NULL;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function loadMultiple(array $ids) {
    /* @var  $dataStorageFactory  \Drupal\metastore\Storage\DataFactory */
    $dataStorageFactory = \Drupal::service("metastore.storage");

    /* @var $dataStorage \Drupal\metastore\Storage\Data */
    $dataStorage = $dataStorageFactory->getInstance('dataset');

    $items = [];
    foreach ($ids as $id) {
      $items[$id] = new Dataset($dataStorage->retrieve($id));
    }

    return $items;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function getItemId(ComplexDataInterface $item) {
    return $item->get('identifier');
  }

}
