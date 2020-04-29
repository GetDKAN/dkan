<?php

namespace Drupal\dkan_search;

use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\dkan_metastore\Service as Metastore;
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
 * Dkan search service tests.
 */
class ServiceTest extends TestCase {

  /**
   * Test for missing search index.
   */
  public function testNoSearchIndex() {
    $container = $this->getCommonMockChain()
      ->add(EntityStorageInterface::class, "load", NULL);

    $this->expectExceptionMessage("An index named [dkan] does not exist.");
    Service::create($container->getMock());
  }

  /**
   * Test for search().
   */
  public function testSearch() {

    $item = (new Chain($this))
      ->add(Item::class, 'getId', 1)
      ->getMock();

    $thing = (object) [
      'title' => 'hello',
      'description' => 'goodbye',
      'publisher__name' => 'Steve',
    ];
    $facet = (object) ['data' => (object) ['name' => 'Steve']];

    $container = $this->getCommonMockChain()
      ->add(IndexInterface::class, 'getFields', ['description' => 'blah', 'publisher__name' => 'blah'])
      ->add(IndexInterface::class, 'getFulltextFields', ['title'])
      ->add(QueryHelperInterface::class, 'createQuery', QueryInterface::class)
      ->add(QueryInterface::class, 'execute', ResultSet::class)
      ->add(QueryInterface::class, 'createConditionGroup', ConditionGroup::class)
      ->add(ResultSet::class, 'getResultCount', 1)
      ->add(ResultSet::class, 'getResultItems', [$item])
      ->add(Metastore::class, 'get', json_encode($thing))
      ->add(Metastore::class, 'getAll', [$facet]);

    $service = Service::create($container->getMock());

    $expect = (object) [
      'total' => 1,
      'results' => [$thing],
      'facets' => [$facets],
    ];

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
   * Common mock chain.
   */
  public function getCommonMockChain() {
    $options = (new Options())
      ->add("dkan_metastore.service", Metastore::class)
      ->add("entity_type.manager", EntityTypeManager::class)
      ->add("search_api.query_helper", QueryHelperInterface::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(EntityTypeManager::class, "getStorage", EntityStorageInterface::class)
      ->add(EntityStorageInterface::class, "load", IndexInterface::class);
  }

}
