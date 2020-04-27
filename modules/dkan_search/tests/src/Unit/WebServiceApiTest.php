<?php

namespace Drupal\dkan_search;

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
      ->add(Request::class, 'blah', NULL)
      ->getMock();

    $reflection = new \ReflectionClass($request);
    $reflection_property = $reflection->getProperty('query');
    $reflection_property->setAccessible(TRUE);
    $reflection_property->setValue($request, $paramsBag);

    $expected = (object) [
      'total' => 1,
      'results' => [],
      'facets' => [],
    ];

    $container = $this->getCommonMockChain()
      ->add(RequestStack::class, 'getCurrentRequest', $request)
      ->add(Service::class, 'search', $expected);

    $controller = WebServiceApi::create($container->getMock());

    $response = $controller->search();
    $this->assertEquals(json_encode($expected), $response->getContent());
  }

  public function getCommonMockChain() {
    $options = (new Options())
      ->add('dkan_search.service', Service::class)
      ->add('request_stack', RequestStack::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, 'get', $options);
  }

}
