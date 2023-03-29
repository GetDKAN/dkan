<?php

namespace Drupal\metastore_search\Plugin\search_api\datasource;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\metastore\Exception\MissingObjectException;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore_search\ComplexData\Dataset;
use Drupal\node\Entity\Node;
use Drupal\search_api\Datasource\DatasourcePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * Node query service.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected QueryInterface $nodeQueryService;

  /**
   * Metastore storage service.
   *
   * @var \Drupal\metastore\Storage\DataFactory
   */
  protected DataFactory $metastoreStorageService;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $dkan_dataset = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $dkan_dataset->nodeQueryService = $container->get('entity_type.manager')
      ->getStorage('node')
      ->getQuery('AND');
    $dkan_dataset->metastoreStorageService = $container->get('dkan.metastore.storage');
    return $dkan_dataset;
  }

  /**
   * {@inheritDoc}
   */
  public function getPropertyDefinitions() {
    return Dataset::definition();
  }

  /**
   * {@inheritDoc}
   */
  public function getItemIds($page = NULL) {
    $ids_query = $this->nodeQueryService
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
   * {@inheritDoc}
   */
  public function loadMultiple(array $ids) {
    /** @var \Drupal\metastore\Storage\Data $dataStorage */
    $dataStorage = $this->metastoreStorageService->getInstance('dataset');

    $items = [];

    foreach (array_combine($ids, $ids) as $id) {
      try {
        // Only index published revisions.
        $items[$id] = new Dataset($dataStorage->retrieve($id, TRUE));
      }
      catch (MissingObjectException $missingObjectException) {
        // This is thrown if there is no published revision.
        continue;
      }
    }

    return $items;
  }

  /**
   * {@inheritDoc}
   */
  public function getItemId(ComplexDataInterface $item) {
    return $item->get('identifier');
  }

}
