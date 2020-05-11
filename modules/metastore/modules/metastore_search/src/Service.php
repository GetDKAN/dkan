<?php

namespace Drupal\metastore_search;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\metastore\Service as Metastore;
use Drupal\search_api\Query\ResultSet;
use Drupal\search_api\Utility\QueryHelperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Dkan search service class.
 *
 * @package Drupal\metastore_search
 */
class Service implements ContainerInjectionInterface {

  /**
   * The search index.
   *
   * @var \Drupal\search_api\IndexInterface
   */
  private $index;

  /**
   * The query being built then executed.
   *
   * @var \Drupal\search_api\Query\QueryInterface
   */
  private $query;

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
   * Service constructor.
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
   * Search.
   *
   * @param array $params
   *   Search parameters.
   *
   * @return object
   *   StdClass containing total, results and facets.
   */
  public function search(array $params) {
    $fields = array_keys($this->index->getFields());
    $this->query = $this->queryHelper->createQuery($this->index);

    $this->setFullText($params);
    $this->setFieldConditions($fields, $params);

    $this->setSort($params, $fields);
    $this->setRange($params);

    $result = $this->query->execute();

    $count = $result->getResultCount();

    $data = $this->getData($result);

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
  public function facets(array $params) : array {
    $fields = array_keys($this->index->getFields());
    $this->query = $this->queryHelper->createQuery($this->index);

    $this->setFullText($params);
    $this->setFieldConditions($fields, $params);

    return $this->getFacets($fields);
  }

  /**
   * Private.
   *
   * @param array $params
   *   Array of search parameters.
   */
  private function setFullText(array $params) {
    if (!isset($params['fulltext'])) {
      return;
    }

    $fulltextFields = $this->index->getFulltextFields();
    if (empty($fulltextFields)) {
      return;
    }

    $values = [];
    foreach ($fulltextFields as $field) {
      $values[$field][] = $params['fulltext'];
    }

    $this->createConditionGroup($values, 'OR');
  }

  /**
   * Private.
   *
   * @param array $array
   *   Array of values.
   * @param string $conjunction
   *   'OR' or 'AND'.
   */
  private function createConditionGroup(array $array, string $conjunction = 'AND') {
    $cg = $this->query->createConditionGroup($conjunction);
    foreach ($array as $field => $values) {
      foreach ($values as $value) {
        $cg->addCondition($field, $value);
      }
    }
    $this->query->addConditionGroup($cg);
  }

  /**
   * Private.
   */
  private function setFieldConditions($fields, $params) {
    foreach ($fields as $field) {
      if (isset($params[$field])) {
        $values[$field] = $this->getValuesFromCommaSeparatedString($params[$field]);
        $this->createConditionGroup($values);
      }
    }
  }

  /**
   * Private.
   *
   * @param string $string
   *   Comma-separated string.
   *
   * @return array
   *   Values.
   */
  private function getValuesFromCommaSeparatedString(string $string) {
    $values = [];
    foreach (explode(',', $string) as $value) {
      $values[] = trim($value);
    }
    return $values;
  }

  /**
   * Private.
   *
   * @param array $fields
   *   Fields.
   *
   * @return array
   *   Array containing the facets.
   */
  private function getFacets(array $fields) {
    $facetsTypes = ['theme', 'keyword', 'publisher__name'];
    $facets = [];

    foreach ($facetsTypes as $type) {
      if (in_array($type, $fields)) {
        $facets = array_merge($facets, $this->getFacetsForType($type));
      }
    }

    return $facets;
  }

  /**
   * Private.
   *
   * @param string $type
   *   String describing type.
   *
   * @return array
   *   Array containing the facets.
   */
  private function getFacetsForType(string $type) {
    $facets = [];
    $field = '';

    // Prepare facets for fields that correspond to objects.
    $matches = [];
    if (preg_match('/(.*)__(.*)/', $type, $matches)) {
      $schema = $matches[1];
      $field = $matches[2];
    }
    else {
      $schema = $type;
    }
    foreach ($this->metastoreService->getAll($schema) as $thing) {
      $facet_name = empty($field) ? $thing->data : $thing->data->{$field};
      $facets[] = $this->getFacetHelper($type, $facet_name);
    }

    return $facets;
  }

  /**
   * Private.
   *
   * @param string $type
   *   Type.
   * @param string $facet_name
   *   Face name.
   *
   * @return array
   *   Results for a facet.
   */
  private function getFacetHelper(string $type, string $facet_name) {
    $cloneQuery = clone $this->query;
    $cloneQuery->addCondition($type, $facet_name);
    $result = $cloneQuery->execute();
    return [
      'type' => $type,
      'name' => $facet_name,
      'total' => $result->getResultCount(),
    ];
  }

  /**
   * Private.
   *
   * @param array $params
   *   Parameters.
   * @param array $fields
   *   Fields.
   */
  private function setSort(array $params, array $fields) {
    if (isset($params['sort']) && in_array($params['sort'], $fields)) {
      $this->query->sort($params['sort'], $this->getSortOrder($params));
      return;
    }

    $this->query->sort('search_api_relevance', $this->query::SORT_DESC);
  }

  /**
   * Private.
   *
   * @param array $params
   *   Search parameters.
   *
   * @return mixed
   *   String describing sort order as ascending or descending.
   */
  private function getSortOrder(array $params) {
    $default = $this->query::SORT_ASC;
    if (!isset($params['sort-order'])) {
      return $default;
    }
    if ($params['sort-order'] != 'asc' && $params['sort-order'] != 'desc') {
      return $default;
    }
    return ($params['sort-order'] == 'asc') ? $this->query::SORT_ASC : $this->query::SORT_DESC;
  }

  /**
   * Private.
   *
   * @param array $params
   *   Search parameters.
   */
  private function setRange(array $params) {
    $end = ($params['page'] * $params['page-size']);
    $start = $end - $params['page-size'];
    $this->query->range($start, $params['page-size']);
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

}
