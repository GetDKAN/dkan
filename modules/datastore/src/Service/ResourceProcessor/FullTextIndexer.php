<?php

namespace Drupal\datastore\Service\ResourceProcessor;

use Drupal\common\Resource;
use Drupal\datastore\FullText\AlterTableQueryFactoryInterface;
use Drupal\datastore\Service\ResourceProcessorInterface;
use Drupal\metastore\Service as MetastoreService;

/**
 * Apply specified data-dictionary to datastore belonging to specified dataset.
 */
class FullTextIndexer implements ResourceProcessorInterface {

  /**
   * Datastore table query service.
   *
   * @var \Drupal\datastore\FullText\AlterTableQueryFactoryInterface
   */
  protected $alterTableQueryFactory;

  /**
   * The metastore service.
   *
   * @var \Drupal\metastore\Service
   */
  protected $metastore;

  /**
   * The metastore resource mapper service.
   *
   * @var \Drupal\metastore\ResourceMapper
   */
  protected $resourceMapper;

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param \Drupal\datastore\FullText\AlterTableQueryFactoryInterface $alter_table_query_factory
   *   The alter table query factory service.
   * @param \Drupal\metastore\Service $metastore
   *   The metastore service.
   */
  public function __construct(
    AlterTableQueryFactoryInterface $alter_table_query_factory,
    MetastoreService $metastore
  ) {
    $this->metastore = $metastore;
    $this->alterTableQueryFactory = $alter_table_query_factory;
  }

  /**
   * Retrieve dictionary and datastore table details; apply dictionary to table.
   *
   * @param \Drupal\common\Resource $resource
   *   DKAN Resource.
   */
  public function process(Resource $resource): void {
    // @Todo: create process for defining indexes.
    $indexes = [
      'keyword' => ['decisision_rationale', 'coverage_rules'],
      'condition' => ['_condition'],
      'drug' => ['drug']
    ];
    // Retrieve name of datastore table for resource.
    $datastore_table = $resource->getTableName();

    $this->createIndexes($indexes, $datastore_table);
  }

  /**
   * Apply fulltext indexes to the given datastore.
   *
   * @param array $indexes
   *   Fulltext indexes to apply to the table.
   * @param string $datastore_table
   *   Mysql table name.
   */
  public function createIndexes(array $indexes, string $datastore_table): void {
    $this->alterTableQueryFactory
      ->getQuery($datastore_table, $indexes)
      ->applyFullTextIndexes();
  }

}
