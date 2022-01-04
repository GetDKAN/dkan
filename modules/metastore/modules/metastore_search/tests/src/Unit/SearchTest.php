<?php

namespace Drupal\Tests\metastore_search\Unit;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\metastore_search\Search;
use Drupal\Tests\common\Traits\ServiceCheckTrait;
use Drupal\metastore\Service as Metastore;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Item\Item;
use Drupal\search_api\Query\ConditionGroup;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\Query\ResultSet;
use Drupal\search_api\Utility\QueryHelperInterface;
use Drupal\Tests\metastore\Unit\ServiceTest;
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
    $container = $this->getCommonMockChain($this)
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

    $container = $this->getCommonMockChain($this)->getMock();

    \Drupal::setContainer($container);

    $service = Search::create($container);

    $this->assertEquals($expect, $service->search([
      'page' => 1,
      'page-size' => 10,
      'fulltext' => 'hello',
      'description' => 'goodbye',
      'sort' => 'description',
      'sort-order' => 'asc',
    ]));
  }

  public function testSearchParameterWithComma() {
    $options = (new Options())
      ->add('dkan.metastore.service', Metastore::class)
      ->add('entity_type.manager', EntityTypeManager::class)
      ->add('search_api.query_helper', QueryHelperInterface::class)
      ->add('event_dispatcher', ContainerAwareEventDispatcher::class)
      ->index(0);

    $container = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(EntityTypeManager::class, 'getStorage', EntityStorageInterface::class)
      ->add(EntityStorageInterface::class, 'load', IndexInterface::class)
      ->add(IndexInterface::class, 'getFields', ['description' => 'blah', 'publisher__name' => 'blah'])
      ->add(IndexInterface::class, 'getFulltextFields', ['title'])

      ->add(QueryHelperInterface::class, 'createQuery', QueryInterface::class)
      ->add(QueryInterface::class, 'execute', ResultSet::class)
      ->add(QueryInterface::class, 'createConditionGroup', ConditionGroup::class)
      ->add(ConditionGroup::class, 'addCondition', null, 'condition_group');

    \Drupal::setContainer($container->getMock());

    $service = Search::create($container->getMock());

    $service->search([
      'publisher__name' => 'Normal Param, "Steve, and someone else"',
    ]);

    $this->assertEquals(
      'Steve, and someone else',
      $container->getStoredInput('condition_group')[1]
    );
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

    $expect2 = [
      (object) [
        'type' => 'publisher__name',
        'name' => 'Steve',
        'total' => 0,
      ],
    ];

    $container = $this->getCommonMockChain($this)->getMock();

    \Drupal::setContainer($container);

    $service = Search::create($container);

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
   *
   */
  public static function getCommonMockChain(TestCase $case, Options $services = NULL, $collection = NULL) {
    if (!$services) {
      $services = new Options();
    }

    $myServices = [
      'dkan.metastore.service' => Metastore::class,
      'entity_type.manager' => EntityTypeManager::class,
      'search_api.query_helper' => QueryHelperInterface::class,
      'event_dispatcher' => ContainerAwareEventDispatcher::class,
    ];

    foreach ($myServices as $serviceName => $class) {
      $serviceClass = $services->return($serviceName);
      if (!isset($serviceClass)) {
        $services->add($serviceName, $class);
      }
    }

    $services->index(0);

    $item = (new Chain($case))
      ->add(Item::class, 'getId', 1)
      ->getMock();

    if (!isset($collection)) {
      $collection = [
        'title' => 'hello',
        'description' => 'goodbye',
        'publisher__name' => 'Steve',
      ];
    }

    $facet = ['data' => ['name' => 'Steve']];

    $getAllOptions = (new Options())
      ->add('keyword', [])
      ->add('theme', [])
      ->add('publisher', [ServiceTest::getValidMetadataFactory($case)->get(json_encode($facet), 'publisher')])
      ->index(0);

    $getData = ServiceTest::getValidMetadataFactory($case)->get(json_encode($collection), 'dummy_schema_id');

    return (new Chain($case))
      ->add(Container::class, 'get', $services)
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

}
