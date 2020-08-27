<?php

use MockChain\Options;
use Drupal\datastore\Service;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use MockChain\Chain;
use Drupal\datastore\WebServiceApi;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 */
class WebServiceApiTest extends TestCase {

  /**
   *
   */
  public function testMultipleImports() {
    $container = $this->getContainer();

    $webServiceApi = WebServiceApi::create($container);
    $result = $webServiceApi->import();

    $this->assertTrue($result instanceof JsonResponse);
  }

  /**
   * Private.
   */
  private function getContainer() {
    $options = (new Options())
      ->add("datastore.service", Service::class)
      ->add("request_stack", RequestStack::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(Service::class, "drop", NULL)
      ->add(Service::class, "import", [])
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getContent', json_encode((object) ['resource_ids' => ["1", "2"]]))
      ->getMock();
  }

}
