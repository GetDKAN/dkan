<?php

namespace Drupal\Tests\metastore_search\Unit;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\metastore\FileSchemaRetriever;
use Drupal\metastore\RootedJsonDataWrapper;
use Drupal\metastore_search\Search;
use Drupal\Tests\common\Traits\ServiceCheckTrait;
use Drupal\metastore\Service as Metastore;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Item\Item;
use Drupal\search_api\Query\ConditionGroup;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\Query\ResultSet;
use Drupal\search_api\Utility\QueryHelperInterface;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

/**
 * Class SearchTest.
 *
 * @package Drupal\Tests\metastore_search\Unit
 * @group metastore_search
 */
class SearchTest extends TestCase {
  use ServiceCheckTrait;

  /**
   * Test for missing search index.
   */
  public function testNoSearchIndex() {
    $container = $this->getCommonMockChain()
      ->add(EntityStorageInterface::class, 'load', NULL);

    $this->expectExceptionMessage('An index named [dkan] does not exist.');
    Search::create($container->getMock());
  }

  /**
   * Test for search().
   */
  public function testSearch() {
    $expect = (object) [
      'total' => 1,
      'results' => [(object) [
        'title' => 'hello',
        'description' => 'goodbye',
        'publisher__name' => 'Steve',
      ],
      ],
    ];

    $service = Search::create($this->getCommonMockChain()->getMock());

    $this->assertEquals($expect, $service->search([
      'page' => 1,
      'page-size' => 10,
      'fulltext' => 'hello',
      'description' => 'goodbye',
      'sort' => 'description',
      'sort-order' => 'asc',
    ]));
  }

  /**
   * Test for facets().
   */
  public function testFacets() {
    $expect1 = [(object) [
      'type' => 'publisher__name',
      'name' => 'Steve',
      'total' => 1,
    ],
    ];

    $expect2 = [(object) [
      'type' => 'publisher__name',
      'name' => 'Steve',
      'total' => 0,
    ],
    ];

    $service = Search::create($this->getCommonMockChain()->getMock());

    $this->assertEquals($expect1, $service->facets([]));

    $this->assertEquals($expect2, $service->facets([
      'page' => 1,
      'page-size' => 10,
      'fulltext' => 'hello',
      'description' => 'goodbye',
      'sort' => 'description',
      'sort-order' => 'asc',
    ]));
  }

  /**
   * Common mock chain.
   */
  public function getCommonMockChain() {
    $this->checkService('dkan.metastore.service', 'metastore');

    $options = (new Options())
      ->add('dkan.metastore.service', Metastore::class)
      ->add('entity_type.manager', EntityTypeManager::class)
      ->add('search_api.query_helper', QueryHelperInterface::class)
      ->index(0);

    $item = (new Chain($this))
      ->add(Item::class, 'getId', 1)
      ->getMock();

    $collection = [
      'title' => 'hello',
      'description' => 'goodbye',
      'publisher__name' => 'Steve',
    ];

    $facet = ['data' => ['name' => 'Steve']];

    $getAllOptions = (new Options())
      ->add('keyword', [])
      ->add('theme', [])
      ->add('publisher', [$this->getJsonWrapper()->createRootedJsonData('publisher', json_encode($facet))]);

    $getData = $this->getJsonWrapper()->createRootedJsonData('dummy_schema_id', json_encode($collection));

    return (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(EntityTypeManager::class, 'getStorage', EntityStorageInterface::class)
      ->add(EntityStorageInterface::class, 'load', IndexInterface::class)
      ->add(IndexInterface::class, 'getFields', ['description' => 'blah', 'publisher__name' => 'blah'])
      ->add(IndexInterface::class, 'getFulltextFields', ['title'])
      ->add(QueryHelperInterface::class, 'createQuery', QueryInterface::class)
      ->add(QueryInterface::class, 'execute', ResultSet::class)
      ->add(QueryInterface::class, 'createConditionGroup', ConditionGroup::class)
      ->add(ResultSet::class, 'getResultCount', 1)
      ->add(ResultSet::class, 'getResultItems', [$item])
      ->add(Metastore::class, 'get', $getData)
      ->add(Metastore::class, 'getAll', $getAllOptions);
  }

  /**
   * @return \Drupal\metastore\RootedJsonDataWrapper
   */
  public function getJsonWrapper() {
    $options = (new Options())
      ->add('metastore.schema_retriever', FileSchemaRetriever::class)
      ->index(0);

    $container = (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(FileSchemaRetriever::class, "retrieve", json_encode(['foo' => 'bar']));

    return RootedJsonDataWrapper::create($container->getMock());
  }

}
