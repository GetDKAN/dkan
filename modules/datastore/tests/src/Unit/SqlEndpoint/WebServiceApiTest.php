<?php

namespace Drupal\Tests\datastore\Unit\SqlEndpoint;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\Container;
use Drupal\common\Plugin\DataModifierBase;
use Drupal\common\Plugin\DataModifierManager;
use Drupal\Tests\datastore\Traits\TestHelperTrait;
use MockChain\Chain;
use MockChain\Options;
use Drupal\datastore\SqlEndpoint\WebServiceApi;
use Drupal\datastore\SqlEndpoint\Service;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @coversDefaultClass \Drupal\datastore\WebServiceApi
 * @group dkan
 */
class WebServiceApiTest extends TestCase {
  use TestHelperTrait;

  /**
   *
   */
  public function testGet() {
    $container = $this->getCommonMockChain()->getMock();
    \Drupal::setContainer($container);
    $controller = WebServiceApi::create($container);
    $response = $controller->runQueryGet();
    $this->assertEquals("[{\"column_1\":\"hello\",\"column_2\":\"goodbye\"}]", $response->getContent());
  }

  /**
   *
   */
  public function testNoQueryString() {
    $message = ["message" => "Missing 'query' query parameter"];
    $expectedResponse = new JsonResponse($message);

    $controller = WebServiceApi::create($this->getCommonMockChain("")->getMock());
    $response = $controller->runQueryGet();
    $this->assertEquals($expectedResponse->getContent(), $response->getContent());
  }

  /**
   *
   */
  public function testPost() {
    $container = $this->getCommonMockChain()->getMock();
    \Drupal::setContainer($container);
    $controller = WebServiceApi::create($container);
    $response = $controller->runQueryPost();
    $this->assertEquals("[{\"column_1\":\"hello\",\"column_2\":\"goodbye\"}]", $response->getContent());
  }

  /**
   *
   */
  public function testNoQueryPayload() {
    $message = ["message" => "Missing 'query' property in the request's body."];
    $expectedResponse = new JsonResponse($message);

    $controller = WebServiceApi::create($this->getCommonMockChain("")->getMock());
    $response = $controller->runQueryPost();
    $this->assertEquals($expectedResponse->getContent(), $response->getContent());
  }

  /**
   * Getter.
   */
  public function getCommonMockChain($query = "[SELECT * FROM 123__456][WHERE abc = \"blah\"][ORDER BY abc DESC][LIMIT 1 OFFSET 3];") {

    $options = (new Options())
      ->add('dkan.datastore.sql_endpoint.service', Service::class)
      ->add("database", Connection::class)
      ->add('request_stack', RequestStack::class)
      ->add('event_dispatcher', ContainerAwareEventDispatcher::class)
      ->index(0);

    $body = json_encode(["query" => $query]);

    $row = (object) ['column_1' => "hello", 'column_2' => "goodbye"];

    return (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'get', $query)
      ->add(Request::class, 'getContent', $body)
      ->add(ConfigFactory::class, 'get', Config::class)
      ->add(Config::class, 'get', 1000)
      ->add(Service::class, 'runQuery', [$row]);
  }

}
