<?php

namespace Drupal\metastore_search\Plugin\search_api\datasource;

use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\metastore\Exception\MissingObjectException;
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
 *
 * @todo We should rely more in the metastore instead of direct
 * entity queries and direct connections to storage classes.
 */
class DkanDataset extends DatasourcePluginBase {

  /**
   * Item IDs query pager size.
   *
   * @var int
   */
  protected const PAGE_SIZE = 250;

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
    $ids_query = \Drupal::entityQuery('node')
      ->addTag('search_api_datasource_dkan')
      ->accessCheck(FALSE)
      ->condition('type', 'data')
      ->condition('field_data_type', 'dataset');

    if (isset($page)) {
      $ids_query->range($page * self::PAGE_SIZE, self::PAGE_SIZE);
    }

    $uuids = array_map(function ($node) {
      return $node->uuid();
    }, Node::loadMultiple($ids_query->execute()));

    return $uuids ?: NULL;
  }

  /**
   * Inherited.
   *
   * @inheritdoc
   */
  public function loadMultiple(array $ids) {
    /* @var   \Drupal\metastore\Storage\DataFactory $dataStorageFactory */
    $dataStorageFactory = \Drupal::service("dkan.metastore.storage");
    /* @var \Drupal\metastore\Storage\Data $dataStorage */
    $dataStorage = $dataStorageFactory->getInstance('dataset');

    $items = [];

    foreach (array_combine($ids, $ids) as $id) {
      try {
        $items[$id] = new Dataset($dataStorage->retrieve($id, TRUE));
      }
      catch (MissingObjectException $missingObjectException) {
        continue;
      }
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
