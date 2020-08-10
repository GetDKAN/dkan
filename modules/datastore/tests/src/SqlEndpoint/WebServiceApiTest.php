<?php

namespace Drupal\Tests\datastore\SqlEndpoint;

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
    $controller = WebServiceApi::create($this->getCommonMockChain()->getMock());
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
    $controller = WebServiceApi::create($this->getCommonMockChain()->getMock());
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
   *
   */
  public function testWithDataModifierPlugin() {
    $pluginMessage = "Resource hidden since dataset access level is non-public.";
    $pluginCode = 401;

    $container = $this->getCommonMockChain("[SELECT * FROM 123456][WHERE abc = \"blah\"][ORDER BY abc DESC][LIMIT 1 OFFSET 3];")
      ->add(DataModifierManager::class, 'getDefinitions', [['id' => 'foobar']])
      ->add(DataModifierManager::class, 'createInstance', DataModifierBase::class)
      ->add(DataModifierBase::class, 'requiresModification', TRUE)
      ->add(DataModifierBase::class, 'message', $pluginMessage)
      ->add(DataModifierBase::class, 'httpCode', $pluginCode);

    $controller = WebServiceApi::create($container->getMock());
    $response = $controller->runQueryGet();
    $expected = '{"message":"Resource hidden since dataset access level is non-public."}';
    $this->assertEquals($expected, $response->getContent());
  }

  /**
   * Getter.
   */
  public function getCommonMockChain($query = "[SELECT * FROM 123__456][WHERE abc = \"blah\"][ORDER BY abc DESC][LIMIT 1 OFFSET 3];") {

    $options = (new Options())
      ->add('datastore.sql_endpoint.service', Service::class)
      ->add("database", Connection::class)
      ->add('request_stack', RequestStack::class)
      ->add('plugin.manager.common.data_modifier', DataModifierManager::class)
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
      ->add(DataModifierManager::class, 'getDefinitions', [])
      ->add(DataModifierManager::class, 'createInstance', DataModifierBase::class)
      ->add(DataModifierBase::class, 'requiresModification', FALSE)
      ->add(Service::class, 'runQuery', [$row]);
  }

}
