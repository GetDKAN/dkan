<?php

namespace Drupal\metastore_search;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\metastore\Service as Metastore;
use Drupal\search_api\Query\ResultSet;
use Drupal\search_api\Utility\QueryHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Search.
 *
 * Provides search results and facets information from a SearchAPI index.
 *
 * @package Drupal\metastore_search
 */
class Search implements ContainerInjectionInterface {
  use QueryBuilderTrait;
  use FacetsFromIndexTrait;
  // @todo Use real classes to get proper encapsulation.
  use FacetsFromContentTrait;

  const EVENT_SEARCH = 'dkan_metastore_search_search';
  const EVENT_SEARCH_PARAMS = 'dkan_metastore_search_search_params';

  // @todo this constant is used by QueryBuilder, maybe we need a class.
  const EVENT_SEARCH_QUERY_BUILDER_CONDITION = 'dkan_metastore_search_query_builder_condition';

  /**
   * The search index.
   *
   * @var \Drupal\search_api\IndexInterface
   */
  private $index;

  /**
   * Metastore service.
   *
   * @var \Drupal\metastore\Service
   */
  private $metastoreService;

  /**
   * Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  private $entityTypeManager;

  /**
   * Query helper.
   *
   * @var \Drupal\search_api\Utility\QueryHelperInterface
   */
  private $queryHelper;

  /**
   * Constructor.
   *
   * @param \Drupal\metastore\Service $metastoreService
   *   Metastore service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\search_api\Utility\QueryHelperInterface $queryHelper
   *   Query helper.
   */
  public function __construct(
    Metastore $metastoreService,
    EntityTypeManager $entityTypeManager,
    QueryHelperInterface $queryHelper
  ) {
    $this->metastoreService = $metastoreService;
    $this->entityTypeManager = $entityTypeManager;
    $this->queryHelper = $queryHelper;

    $this->setSearchIndex('dkan');
  }

  /**
   * Create.
   *
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('dkan.metastore.service'),
      $container->get('entity_type.manager'),
      $container->get('search_api.query_helper')
    );
  }

  /**
   * Search.
   *
   * Returns an object with 2 properties: total (the total number of records
   * without limits), and results (An array with the result objects).
   *
   * @param array $params
   *   Search parameters.
   */
  public function search(array $params = []) {
    $params = $this->dispatchEvent(self::EVENT_SEARCH_PARAMS, $params);
    $query = $this->getQuery($params, $this->index, $this->queryHelper)[0];
    $result = $query->execute();

    $count = $result->getResultCount();
    $data = $this->getData($result);

    $data = $this->dispatchEvent(self::EVENT_SEARCH, $data);

    return (object) [
      'total' => $count,
      'results' => $data,
    ];
  }

  /**
   * Facets.
   *
   * @param array $params
   *   Array of search parameters.
   *
   * @return array
   *   Array of facets, each containing type, name, total.
   */
  public function facets(array $params = []) : array {
    $params['page-size'] = PHP_INT_MAX;
    $params['page'] = 1;

    list($query, $activeConditions) = $this->getQuery($params, $this->index, $this->queryHelper);

    if ($activeConditions) {
      $facets = $this->getFacetsFromContent($params, $query);
    }
    else {
      $facets = $this->getFacetsFromIndex($params, $this->index, $query);
    }

    return $facets;
  }

  /**
   * Private.
   *
   * @param \Drupal\search_api\Query\ResultSet $result
   *   Result set.
   *
   * @return array
   *   Filtered results.
   */
  private function getData(ResultSet $result) {
    $metastore = $this->metastoreService;

    return array_filter(array_map(
      function ($item) use ($metastore) {
        $id = $item->getId();
        $id = str_replace('dkan_dataset/', '', $id);
        try {
          return json_decode($metastore->get('dataset', $id));
        }
        catch (\Exception $e) {
          return NULL;
        }
      },
      $result->getResultItems()
    ));
  }

  /**
   * Set the dkan search index.
   *
   * @param string $id
   *   Search index identifier.
   */
  private function setSearchIndex(string $id) {
    $storage = $this->entityTypeManager
      ->getStorage('search_api_index');
    $this->index = $storage->load($id);

    if (!$this->index) {
      throw new \Exception("An index named [{$id}] does not exist.");
    }
  }

  /**
   * Get the current search index.
   *
   * @return \Drupal\search_api\IndexInterface|null
   *   If available, return the search index.
   */
  public function getSearchIndex() {
    if (isset($this->index)) {
      return $this->index;
    }
    return NULL;
  }

}
