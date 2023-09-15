<?php

namespace Drupal\Tests\datastore\Unit\SqlEndpoint;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Cache\Context\CacheContextsManager;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Tests\datastore\Traits\TestHelperTrait;
use MockChain\Chain;
use MockChain\Options;
use Drupal\datastore\SqlEndpoint\WebServiceApi;
use Drupal\datastore\SqlEndpoint\DatastoreSqlEndpointService;
use Drupal\metastore\MetastoreApiResponse;
use Drupal\metastore\NodeWrapper\Data;
use Drupal\metastore\NodeWrapper\NodeDataFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @coversDefaultClass \Drupal\datastore\WebServiceApi
 * @group dkan
 */
class WebServiceApiTest extends TestCase {
  use TestHelperTrait;

  protected function setUp(): void {
    parent::setUp();
    // Set cache services
    $options = (new Options)
      ->add('cache_contexts_manager', CacheContextsManager::class)
      ->add('event_dispatcher', ContainerAwareEventDispatcher::class)
      ->index(0);
    $chain = (new Chain($this))
      ->add(ContainerInterface::class, 'get', $options)
      ->add(CacheContextsManager::class, 'assertValidTokens', TRUE);
    \Drupal::setContainer($chain->getMock());
  }

  /**
   *
   */
  public function testGet() {
    $container = $this->getCommonMockChain()->getMock();
    // \Drupal::setContainer($container);
    $controller = WebServiceApi::create($container);
    $response = $controller->runQueryGet();
    $this->assertEquals("[{\"column_1\":\"hello\",\"column_2\":\"goodbye\"}]", $response->getContent());
  }

  /**
   *
   */
  public function testNoQueryString() {
    $message = "Missing 'query' query parameter";
    $expectedResponse = new JsonResponse($message);

    $controller = WebServiceApi::create($this->getCommonMockChain("")->getMock());
    $response = $controller->runQueryGet();
    $this->assertStringContainsString($expectedResponse->getContent(), $response->getContent());
  }

  /**
   *
   */
  public function testPost() {
    $container = $this->getCommonMockChain()->getMock();
    // \Drupal::setContainer($container);
    $controller = WebServiceApi::create($container);
    $response = $controller->runQueryPost();
    $this->assertEquals("[{\"column_1\":\"hello\",\"column_2\":\"goodbye\"}]", $response->getContent());
  }

  /**
   *
   */
  public function testNoQueryPayload() {
    $message = "Missing 'query' property in the request's body.";
    $expectedResponse = new JsonResponse($message);

    $controller = WebServiceApi::create($this->getCommonMockChain("")->getMock());
    $response = $controller->runQueryPost();
    $this->assertStringContainsString($expectedResponse->getContent(), $response->getContent());
  }

  /**
   * Getter.
   */
  public function getCommonMockChain($query = "[SELECT * FROM 123__456][WHERE abc = \"blah\"][ORDER BY abc DESC][LIMIT 1 OFFSET 3];") {

    $options = (new Options())
      ->add('dkan.datastore.sql_endpoint.service', DatastoreSqlEndpointService::class)
      ->add('dkan.metastore.metastore_item_factory', NodeDataFactory::class)
      ->add('dkan.metastore.api_response', MetastoreApiResponse::class)
      ->add("database", Connection::class)
      ->add('request_stack', RequestStack::class)
      ->add('event_dispatcher', ContainerAwareEventDispatcher::class)
      ->add('cache_contexts_manager', CacheContextsManager::class)
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
      ->add(DatastoreSqlEndpointService::class, 'runQuery', [$row])
      ->add(DatastoreSqlEndpointService::class, 'getTableNameFromSelect', '465s')
      ->add(MetastoreApiResponse::class, 'getMetastoreItemFactory', NodeDataFactory::class)
      ->add(MetastoreApiResponse::class, 'addReferenceDependencies', NULL)
      ->add(CacheContextsManager::class, 'assertValidTokens', TRUE)
      ->add(NodeDataFactory::class, 'getInstance', Data::class)
      ->add(Data::class, 'getCacheContexts', ['url'])
      ->add(Data::class, 'getCacheTags', ['node:1'])
      ->add(Data::class, 'getCacheMaxAge', 0);
  }

}
