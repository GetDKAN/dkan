<?php

namespace Drupal\Tests\dkan_sql_endpoint;

use Dkan\Datastore\Resource;
use Drupal\Core\Database\Connection;
use \Drupal\dkan_datastore\Service\Resource as ResourceService;
use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\dkan_datastore\Service\Factory\Resource as ResourceServiceFactory;
use Drupal\dkan_datastore\Storage\DatabaseTableFactory;
use MockChain\Chain;
use Drupal\dkan_sql_endpoint\Service;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class ServiceTest extends TestCase {

  public function testHappyPath() {
    $services = (new Options())
      ->add('config.factory', ConfigFactory::class)
      ->add('dkan_datastore.database_table_factory', DatabaseTableFactory::class)
      ->add('dkan_datastore.service.factory.resource', ResourceServiceFactory::class)
      ->add('database', Connection::class)
      ->index(0);

    $container = (new Chain($this))
      ->add(Container::class, "get", $services)
      ->add(ConfigFactory::class, "get", ImmutableConfig::class)
      ->add(ImmutableConfig::class, "get", "100")
      ->add(ResourceServiceFactory::class, 'getInstance', ResourceService::class)
      ->add(ResourceService::class, 'get', Resource::class)
      ->add(Resource::class, 'getId', '123')
      ->getMock();

    $expectedData = (object) [
      'First Name' => "Felix",
      'lAST nAME' => "The Cat"
    ];

    $service = Service::create($container);
    $data = $service->runQuery('[SELECT * FROM 123];');
    $this->assertEquals($expectedData, json_decode($data[0]));
  }

  /**
   *
   */
  public function testAutoLimitOnSqlStatements() {
    $container = (new Chain($this))
      ->add(Container::class, "get", ConfigFactory::class)
      ->add(ConfigFactory::class, "get", ImmutableConfig::class)
      ->add(ImmutableConfig::class, "get", "100")
      ->getMock();

    $service = Service::create($container);
    $query = $service->getQueryObject("[SELECT * FROM blah];");
    $this->assertTrue(isset($query->limit));
    $this->assertEquals($query->limit, 100);
  }

  /**
   *
   */
  public function testNoAutoLimitOnCountSqlStatements() {
    $container = (new Chain($this))
      ->add(Container::class, "get", ConfigFactory::class)
      ->add(ConfigFactory::class, "get", ImmutableConfig::class)
      ->add(ImmutableConfig::class, "get", "100")
      ->getMock();

    $service = Service::create($container);
    $query = $service->getQueryObject("[SELECT COUNT(*) FROM blah];");
    $this->assertFalse(isset($query->limit));
  }

}
