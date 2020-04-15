<?php

namespace Drupal\Tests\dkan_sql_endpoint;

use Drupal\Component\DependencyInjection\Container;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Config\ImmutableConfig;
use MockChain\Chain;
use Drupal\dkan_sql_endpoint\Service;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class ServiceTest extends TestCase {

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
