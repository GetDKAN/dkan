<?php

namespace Drupal\Tests\dkan_sql_endpoint;

use Dkan\Datastore\Resource;
use Drupal\dkan_datastore\Service\Resource as ResourceService;
use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\dkan_datastore\Service\Factory\Resource as ResourceServiceFactory;
use Drupal\dkan_datastore\Storage\DatabaseTable;
use Drupal\dkan_datastore\Storage\DatabaseTableFactory;
use Drupal\Tests\dkan_sql_endpoint\Traits\TestHelperTrait;
use MockChain\Chain;
use Drupal\dkan_sql_endpoint\Service;
use PHPUnit\Framework\TestCase;
use SqlParser\SqlParser;

/**
 *
 */
class ServiceTest extends TestCase {
  use TestHelperTrait;

  /**
   *
   */
  public function testHappyPath() {

    $dbData = (object) [
      'first_name' => "Felix",
      'last_name' => "The Cat",
      'occupation' => "cat",
    ];

    $schema = [
      'fields' => [
        'first_name' => [
          'description' => 'First Name',
        ],
        'last_name' => [
          'description' => 'last_name',
        ],
        'occupation' => [],
      ],
    ];

    $container = $this->getCommonMockChain($this)
      ->add(ResourceServiceFactory::class, 'getInstance', ResourceService::class)
      ->add(ResourceService::class, 'get', Resource::class)
      ->add(Resource::class, 'getId', '123')
      ->add(DatabaseTableFactory::class, 'getInstance', DatabaseTable::class)
      ->add(DatabaseTable::class, 'query', [$dbData])
      ->add(DatabaseTable::class, 'getSchema', $schema)
      ->getMock();

    $expectedData = (object) [
      'First Name' => "Felix",
      'last_name' => "The Cat",
      'occupation' => "cat",
    ];

    $service = Service::create($container);
    $data = $service->runQuery('[SELECT * FROM 123][WHERE last_name = "Felix"][ORDER BY first_name DESC][LIMIT 1 OFFSET 1];');
    $this->assertEquals($expectedData, $data[0]);
  }

  /**
   *
   */
  public function testParserInvalidQueryString() {
    $container = $this->getCommonMockChain($this)
      ->add(SqlParser::class, 'validate', FALSE)
      ->getMock();

    $service = Service::create($container);
    $this->expectExceptionMessage("Invalid query string.");
    $service->runQuery('[SELECT FROM 123');
  }

  /**
   *
   */
  public function testGetDatabaseTableExceptionResourceNotFound() {
    $container = $this->getCommonMockChain($this)
      ->add(ResourceServiceFactory::class, 'getInstance', ResourceService::class)
      ->add(ResourceService::class, 'get', NULL)
      ->getMock();

    $service = Service::create($container);
    $this->expectExceptionMessage("Resource not found.");
    $service->runQuery('[SELECT * FROM 123][WHERE last_name = "Felix"][ORDER BY first_name DESC][LIMIT 1 OFFSET 1];');
  }

  /**
   *
   */
  public function testAutoLimitOnSqlStatements() {
    $container = $this->getCommonMockChain($this)
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
    $container = $this->getCommonMockChain($this)
      ->getMock();

    $service = Service::create($container);
    $query = $service->getQueryObject("[SELECT COUNT(*) FROM blah];");
    $this->assertFalse(isset($query->limit));
  }

  /**
   *
   */
  private function getCommonMockChain() {
    return (new Chain($this))
      ->add(Container::class, "get", $this->getServices())
      ->add(ConfigFactory::class, "get", ImmutableConfig::class)
      ->add(ImmutableConfig::class, "get", "100");
  }

}
