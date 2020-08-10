<?php

namespace Drupal\metastore_search;

use Drupal\Component\DependencyInjection\Container;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Dkan search web service api tests.
 */
class WebServiceApiTest extends TestCase {

  /**
   * Test for search.
   */
  public function testSearch() {
    $paramsBag = (new Chain($this))
      ->add(ParameterBag::class, 'all', ['page-size' => 500])
      ->getMock();

    $request = (new Chain($this))
      ->add(Request::class)
      ->getMock();

    $reflection = new \ReflectionClass($request);
    $reflection_property = $reflection->getProperty('query');
    $reflection_property->setAccessible(TRUE);
    $reflection_property->setValue($request, $paramsBag);

    $expected = (object) [
      'total' => 1,
      'results' => [],
    ];
    $facetsExpected = [];

    $options = (new Options())
      ->add('metastore_search.service', Service::class)
      ->add('request_stack', RequestStack::class)
      ->index(0);

    $container = (new Chain($this))
      ->add(Container::class, 'get', $options)
      ->add(RequestStack::class, 'getCurrentRequest', $request)
      ->add(Service::class, 'search', $expected)
      ->add(Service::class, 'facets', $facetsExpected);

    $controller = WebServiceApi::create($container->getMock());

    $expected->facets = $facetsExpected;

    $response = $controller->search();
    $this->assertEquals(json_encode($expected), $response->getContent());
  }

}
