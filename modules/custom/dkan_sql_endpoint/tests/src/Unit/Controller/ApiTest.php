<?php

namespace Drupal\Tests\dkan_sql_endpoint\Unit\Controller;

use Dkan\Datastore\Resource;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\Container;
use Drupal\dkan_common\Tests\Mock\Chain;
use Drupal\dkan_common\Tests\Mock\Options;
use Drupal\dkan_datastore\Storage\DatabaseTable;
use Drupal\dkan_datastore\Storage\DatabaseTableFactory;
use Drupal\dkan_sql_endpoint\Controller\Api;
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
    $response = $controller->runQuery();
    $this->assertEquals("[]", $response->getContent());
  }

  /**
   *
   */
  private function getContainer() {
    $options = (new Options())
      ->add("database", Connection::class)
      ->add('dkan_datastore.service.factory.resource', ResourceServiceFactory::class)
      ->add('config.factory', ConfigFactory::class)
      ->add('request_stack', RequestStack::class)
      ->add('dkan_datastore.database_table_factory', DatabaseTableFactory::class);

    $container = (new Chain($this))
      ->add(Container::class, "get", $options)
      ->add(RequestStack::class, 'getCurrentRequest', Request::class)
      ->add(Request::class, 'get', '[SELECT * FROM abc][WHERE abc = \'blah\'][ORDER BY abc DESC][LIMIT 1 OFFSET 3];')
      ->add(ConfigFactory::class, 'get', Config::class)
      ->add(Config::class, 'get', 1000)
      ->add(ResourceServiceFactory::class, 'getInstance', ResourceService::class)
      ->add(ResourceService::class, 'get', Resource::class)
      ->add(DatabaseTableFactory::class, 'getInstance', DatabaseTable::class)
      ->add(DatabaseTable::class, 'query', [])
      ->getMock();

    return $container;
  }

}
