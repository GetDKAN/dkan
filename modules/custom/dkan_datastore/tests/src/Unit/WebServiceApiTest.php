<?php

use MockChain\Options;
use Drupal\dkan_datastore\Service;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use MockChain\Chain;
use Drupal\dkan_datastore\WebServiceApi;
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
  public function testMultipleDrops() {
    $container = $this->getContainer();

    $webServiceApi = WebServiceApi::create($container);
    $result = $webServiceApi->deleteMultiple(["1", "2"]);

    $this->assertTrue($result instanceof JsonResponse);
  }

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
   *
   */
  private function getContainer() {
    $options = (new Options())
      ->add("dkan_datastore.service", Service::class)
      ->add("request_stack", RequestStack::class);

    return (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(Service::class, "drop", NULL)
      ->add(Service::class, "import", [])
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'getContent', json_encode((object) ['resource_ids' => ["1", "2"]]))
      ->getMock();
  }

}
