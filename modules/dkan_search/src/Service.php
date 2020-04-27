<?php

namespace Drupal\dkan_search;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\dkan_metastore\Service as Metastore;
use Drupal\search_api\Query\ResultSet;
use Drupal\search_api\Utility\QueryHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Dkan search service class.
 *
 * @package Drupal\dkan_search
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
   * @var \Drupal\dkan_metastore\Service
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
   * @var \Drupal\search_api\Utility\QueryHelper
   */
  private $queryHelper;

  /**
   * Service constructor.
   *
   * @param \Drupal\dkan_metastore\Service $metastoreService
   *   Metastore service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\search_api\Utility\QueryHelper $queryHelper
   *   Query helper.
   */
  public function __construct(
    Metastore $metastoreService,
    EntityTypeManager $entityTypeManager,
    QueryHelper $queryHelper
  ) {
    $this->metastoreService = $metastoreService;
    $this->entityTypeManager = $entityTypeManager;
    $this->queryHelper = $queryHelper;

    $this->setSearchIndex("dkan");
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get("dkan_metastore.service"),
      $container->get("entity_type.manager"),
      $container->get("search_api.query_helper")
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
      ->getStorage("search_api_index");
    $this->index = $storage->load($id);

    if (!$this->index) {
      throw new \Exception("An index named [{$id}] does not exist.");
    }
  }

  /**
   * Search.
   *
   * @param array $params
   *
   * @return object
   *   StdClass containing total, results and facets.
   */
  public function search(array $params) {
    $fields = array_keys($this->index->getFields());
    $this->query = $this->queryHelper->createQuery($this->index);

    $this->setFullText($params);
    $this->setFieldConditions($fields, $params);

    $facets = $this->getFacets($fields);

    $this->setSort($params, $fields);
    $this->setRange($params);

    $result = $this->query->execute();

    $count = $result->getResultCount();

    $data = $this->getData($result);

    return (object) [
      "total" => $count,
      "results" => $data,
      "facets" => $facets,
    ];
  }

  /**
   * Search filtered by an index field.
   *
   * @param string $id
   *   Index field identifier.
   * @param string $value
   *   Index field value.
   *
   * @return array
   *   Result array.
   */
  public function searchByIndexField(string $id, string $value) {
    return ["bar"];
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
   * @param string $conjuction
   */
  private function createConditionGroup($array, $conjuction = 'AND') {
    $cg = $this->query->createConditionGroup($conjuction);
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
   *
   * @return array
   */
  private function getValuesFromCommaSeparatedString(string $string) {
    $values = [];
    foreach (explode(",", $string) as $value) {
      $values[] = trim($value);
    }
    return $values;
  }

  /**
   * Private.
   *
   * @param $fields
   *
   * @return array
   */
  private function getFacets($fields) {
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
   *
   * @return array
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
   * @param string $facet_name
   *
   * @return array
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
   * @param array $fields
   */
  private function setSort(array $params, array $fields) {
    if (isset($params['sort']) && in_array($params['sort'], $fields)) {
      $this->query->sort($params['sort'], $this->getSortOrder($this->query, $params));
      return;
    }

    $this->query->sort('search_api_relevance', $this->query::SORT_DESC);
  }

  /**
   * Private.
   *
   * @param array $params
   *
   * @return mixed
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
   *
   * @return array
   */
  private function getData(ResultSet $result) {
    /* @var  $metastore Metastore */
    $metastore = $this->metastoreService;

    return array_filter(array_map(
      function ($item) use ($metastore) {
        $id = $item->getId();
        $id = str_replace("dkan_dataset/", "", $id);
        try {
          return json_decode($metastore->get("dataset", $id));
        }
        catch (\Exception $e) {
          return NULL;
        }
      },
      $result->getResultItems()
    ));
  }

}
