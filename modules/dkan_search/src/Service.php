<?php

namespace Drupal\dkan_search;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\dkan_metastore\Service as Metastore;
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
  private $searchIndex;

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
    $this->searchIndex = $storage->load($id);

    if (!$this->searchIndex) {
      throw new \Exception("An index named [{$id}] does not exist.");
    }
  }

  /**
   * Search.
   *
   * @return array
   *   Result array.
   */
  public function search() {
    return ["foo"];
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

}
