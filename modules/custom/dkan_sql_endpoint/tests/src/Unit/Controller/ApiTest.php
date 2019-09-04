<?php

namespace Drupal\Tests\dkan_datastore\Unit\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\StorableConfigBase;
use Drupal\Core\Database\Connection;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_datastore\Service\Datastore;
use Drupal\dkan_sql_endpoint\Controller\Api;
use Drupal\Core\Database\Schema;
use Drupal\Core\Database\Query\Select;
use Drupal\Core\Database\StatementInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\dkan_datastore\Controller\Api
 * @group dkan
 */
class ApiTest extends DkanTestBase {

  /**
   *
   */
  private function getContainer() {

    $container = $this->getMockBuilder(ContainerInterface::class)
      ->setMethods(['get'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $container->method('get')
      ->with(
        $this->logicalOr(
          $this->equalTo('database'),
          $this->equalTo('dkan_datastore.service'),
          $this->equalTo('config.factory')
        )
      )
      ->will($this->returnCallback(function ($input) {
        switch ($input) {
          case 'database':
            return $this->getDatabaseMock();

          case 'dkan_datastore.service':
            return $this->getDatastoreMock();

          case 'config.factory':
            return $this->getConfigMock();
        }
      }));

    return $container;
  }

  /**
   *
   */
  public function test() {
    $controller = Api::create($this->getContainer());
    $response = $controller->runQuery('[SELECT * FROM abc][WHERE abc = \'blah\'][ORDER BY abc DESC][LIMIT 1 OFFSET 3];');
    $this->assertEquals("[]", $response->getContent());
  }

  /**
   *
   */
  public function test2() {
    $controller = Api::create($this->getContainer());
    $response = $controller->runQuery('[SELECT abc FROM abc][WHERE abc = \'blah\'][ORDER BY abc ASC][LIMIT 1 OFFSET 3];');
    $this->assertEquals("[]", $response->getContent());
  }

  /**
   *
   */
  public function test3() {
    $controller = Api::create($this->getContainer());
    $response = $controller->runQuery('[ELECT abc FROM]');
    $this->assertEquals('"Invalid query string."', $response->getContent());
  }

  /**
   *
   */
  private function getDatabaseMock() {
    $mock = $this->getMockBuilder(Connection::class)
      ->disableOriginalConstructor()
      ->setMethods(['schema', 'select'])
      ->getMockForAbstractClass();

    $mock->method('schema')->willReturn($this->getSchemaMock());
    $mock->method('select')->willReturn($this->getSelectMock());

    return $mock;
  }

  /**
   *
   */
  private function getSelectMock() {
    $mock = $this->getMockBuilder(Select::class)
      ->disableOriginalConstructor()
      ->setMethods(['fields', 'execute', 'condition'])
      ->getMockForAbstractClass();

    $mock->method('fields')->willReturn($mock);
    $mock->method('condition')->willReturn($mock);
    $mock->method('execute')->willReturn($this->getResultMock());

    return $mock;
  }

  /**
   *
   */
  private function getResultMock() {
    $mock = $this->getMockBuilder(StatementInterface::class)
      ->disableOriginalConstructor()
      ->setMethods(['fetchAll'])
      ->getMockForAbstractClass();

    $mock->method('fetchAll')->willReturn([]);

    return $mock;
  }

  /**
   *
   */
  private function getSchemaMock() {
    return $this->createMock(Schema::class);
  }

  /**
   *
   */
  private function getDatastoreMock() {
    return $this->createMock(Datastore::class);
  }

  private function getConfigMock() {
    $mock = $this->getMockBuilder(ConfigFactoryInterface::class)
      ->disableOriginalConstructor()
      ->setMethods(['get'])
      ->getMockForAbstractClass();

    $mock->method('get')->willReturn($this->getConfigResultMock());

    return $mock;
  }

  private function getConfigResultMock() {
    $mock = $this->getMockBuilder(StorableConfigBase::class)
      ->disableOriginalConstructor()
      ->setMethods(['get'])
      ->getMockForAbstractClass();

    $mock->method('get')->willReturn(10);

    return $mock;
  }

}
