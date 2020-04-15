<?php

use Drupal\search_api\Item\Item;
use Drupal\search_api\Query\ConditionGroup;
use Drupal\search_api\Query\ResultSet;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\search_api\IndexInterface;
use Drupal\search_api\Query\QueryInterface;
use Drupal\search_api\Utility\QueryHelperInterface;
use Drupal\dkan_metastore\Service;
use Drupal\dkan_search\WebServiceApi;

/**
 *
 */
class WebServiceApi2Test extends TestCase {

  /**
   *
   */
  public function test() {

    $paramsBag = (new Chain($this))
      ->add(ParameterBag::class, 'all',
        [
          'page-size' => 500,
          'fulltext' => 'hello',
          'description' => 'goodbye',
          'sort' => 'description',
          'sort-order' => 'asc',
        ])
      ->getMock();

    $request = (new Chain($this))
      ->add(Request::class, 'blah', NULL)
      ->getMock();

    $reflection = new ReflectionClass($request);
    $reflection_property = $reflection->getProperty('query');
    $reflection_property->setAccessible(TRUE);
    $reflection_property->setValue($request, $paramsBag);

    $options = (new Options())
      ->add('entity_type.manager', EntityManager::class)
      ->add('request_stack', RequestStack::class)
      ->add('search_api.query_helper', QueryHelperInterface::class)
      ->add('dkan_metastore.service', Service::class)
      ->index(0);

    $item = (new Chain($this))
      ->add(Item::class, 'getId', 1)
      ->getMock();

    $thing = (object) ['title' => 'hello', 'description' => 'goodbye', 'publisher__name' => 'Steve'];
    $facet = (object) ['data' => (object) ['name' => 'Steve']];
    $expect = (object) ['type' => 'publisher__name', 'name' => 'Steve', 'total' => 1];

    $container = (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(EntityManager::class, 'getStorage', EntityStorageInterface::class)
      ->add(EntityStorageInterface::class, 'load', IndexInterface::class)
      ->add(IndexInterface::class, 'getFields', ['description' => 'blah', 'publisher__name' => 'blah'])
      ->add(IndexInterface::class, 'getFulltextFields', ['title'])
      ->add(RequestStack::class, 'getCurrentRequest', $request)
      ->add(QueryHelperInterface::class, 'createQuery', QueryInterface::class)
      ->add(QueryInterface::class, 'execute', ResultSet::class)
      ->add(QueryInterface::class, 'createConditionGroup', ConditionGroup::class)
      ->add(ResultSet::class, 'getResultCount', 1)
      ->add(ResultSet::class, 'getResultItems', [$item])
      ->add(Service::class, 'get', json_encode($thing))
      ->add(Service::class, 'getAll', [$facet])
      ->getMock();

    \Drupal::setContainer($container);

    $controller = new WebServiceApi();

    /* @var $response \Symfony\Component\HttpFoundation\JsonResponse */
    $response = $controller->search();
    $this->assertEquals(
      json_encode((object) ['total' => 1, 'results' => [$thing], 'facets' => [$expect]]),
      $response->getContent());
  }

  /**
   *
   */
  public function testNoIndex() {
    $container = (new Chain($this))
      ->add(Container::class, "get", EntityManager::class)
      ->add(EntityManager::class, 'getStorage', EntityStorageInterface::class)
      ->getMock();

    \Drupal::setContainer($container);

    $controller = new WebServiceApi();

    /* @var $response \Symfony\Component\HttpFoundation\JsonResponse */
    $response = $controller->search();
    $this->assertEquals(json_encode((object) ['message' => "An index named [dkan] does not exist."]), $response->getContent());
  }

}
