<?php

namespace Drupal\Tests\dkan_sql_endpoint\Unit\Controller;

use Dkan\Datastore\Resource;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\Container;
use Drupal\dkan_common\Tests\Mock\Chain;
use Drupal\dkan_common\Tests\Mock\Options;
use Drupal\dkan_datastore\Storage\DatabaseTable;
use Drupal\dkan_datastore\Storage\DatabaseTableFactory;
use Drupal\dkan_sql_endpoint\Controller\Api;
use Drupal\dkan_sql_endpoint\Service;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\dkan_datastore\Service\Factory\Resource as ResourceServiceFactory;
use Drupal\dkan_datastore\Service\Resource as ResourceService;

/**
 * @coversDefaultClass \Drupal\dkan_datastore\WebServiceApi
 * @group dkan
 */
class ApiTest extends TestCase {

  /**
   *
   */
  public function test() {
    $controller = Api::create($this->getContainer());
    $response = $controller->runQueryGet();
    $this->assertEquals("[]", $response->getContent());
  }

  /**
   *
   */
  public function test2() {
    $controller = Api::create($this->getContainer());
    $response = $controller->runQueryPost();
    $this->assertEquals("[]", $response->getContent());
  }

  /**
   *
   */
  private function getContainer() {
    $container = (new Chain($this))
      ->add(Container::class, "get", ConfigFactory::class)
      ->add(ConfigFactory::class, "get", ImmutableConfig::class)
      ->add(ImmutableConfig::class, "get", "100")
      ->getMock();

    $options = (new Options())
      ->add('dkan_sql_endpoint.service', Service::create($container))
      ->add("database", Connection::class)
      ->add('dkan_datastore.service.factory.resource', ResourceServiceFactory::class)
      ->add('request_stack', RequestStack::class)
      ->add('dkan_datastore.database_table_factory', DatabaseTableFactory::class);

    $query = '[SELECT * FROM abc][WHERE abc = \'blah\'][ORDER BY abc DESC][LIMIT 1 OFFSET 3];';
    $body = json_encode(["query" => $query]);

    $container = (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'get', $query)
      ->add(Request::class, 'getContent', $body)
      ->add(ConfigFactory::class, 'get', Config::class)
      ->add(Config::class, 'get', 1000)
      ->add(ResourceServiceFactory::class, 'getInstance', ResourceService::class)
      ->add(ResourceService::class, 'get', Resource::class)
      ->add(Resource::class, 'getId', "1")
      ->add(DatabaseTableFactory::class, 'getInstance', DatabaseTable::class)
      ->add(DatabaseTable::class, 'query', [])
      ->getMock();

    return $container;
  }

}
