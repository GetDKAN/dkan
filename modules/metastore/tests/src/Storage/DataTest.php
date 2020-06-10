<?php

namespace Drupal\Tests\common\Unit\Storage;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\metastore\Storage\Data;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests Drupal\metastore\Storage\Data.
 *
 * @coversDefaultClass \Drupal\metastore\Storage\Data
 * @group common
 */
class DataTest extends TestCase {
  private $node;

  /**
   *
   */
  public function testRetrieveAll() {
    $store = new Data($this->getNodeStorageMock(), $this->getConfigFactoryMock());
    $store->setSchema('dataset');
    $all = $store->retrieveAll();

    $object = '{"name":"blah"}';

    $this->assertEquals([$object, $object], $all);
  }

  /**
   *
   */
  public function testRetrieve() {
    $this->node = $this->getNodeMock();
    $store = new Data($this->getNodeStorageMock(), $this->getConfigFactoryMock());
    $store->setSchema('dataset');
    $obj = $store->retrieve(1);

    $object = '{"name":"blah"}';

    $this->assertEquals($object, $obj);
  }

  /**
   *
   */
  public function testStoreExisting() {
    $this->node = $this->getNodeMock();
    $object = '{"name":"blah"}';

    $store = new Data($this->getNodeStorageMock(), $this->getConfigFactoryMock());
    $store->setSchema('dataset');
    $id = $store->store($object, 1);

    $this->assertEquals(1, $id);
  }

  /**
   *
   */
  public function testStoreNew() {
    $this->node = NULL;
    $object = '{"name":"blah"}';

    $store = new Data($this->getNodeStorageMock(), $this->getConfigFactoryMock());
    $store->setSchema('dataset');
    $id = $store->store($object, 1);

    $this->assertEquals(1, $id);
  }

  /**
   *
   */
  public function testRemove() {
    $this->node = $this->getNodeMock();
    $store = new Data($this->getNodeStorageMock(), $this->getConfigFactoryMock());
    $store->setSchema('dataset');
    $removed = $store->remove(1);

    $this->assertEquals(NULL, $removed);
  }

  /**
   *
   */
  public function testRetrieveAllException() {
    $this->expectExceptionMessage("Data schema id not set.");
    $store = new Data($this->getNodeStorageMock(), $this->getConfigFactoryMock());
    $store->retrieveAll();
  }

  /**
   *
   */
  public function testRetrieveException() {
    $this->expectExceptionMessage("Data schema id not set.");
    $store = new Data($this->getNodeStorageMock(), $this->getConfigFactoryMock());
    $store->retrieve(1);
  }

  /**
   *
   */
  public function testStoreException() {
    $this->expectExceptionMessage("Data schema id not set.");
    $object = '{"name":"blah"}';
    $store = new Data($this->getNodeStorageMock(), $this->getConfigFactoryMock());
    $store->store($object, 1);
  }

  /**
   *
   */
  private function getNodeStorageMock() {
    $nodeStorage = $this->getMockBuilder(NodeStorageInterface::class)
      ->disableOriginalConstructor()
      ->setMethods(['getQuery', 'load', 'loadByProperties', 'create'])
      ->getMockForAbstractClass();

    $nodeStorage->method('getQuery')
      ->willReturn($this->getQueryMock());

    $nodeStorage->method('load')
      ->willReturn($this->getNodeMock());

    $nodeStorage->method('loadByProperties')
      ->willReturn([$this->node]);

    $nodeStorage->method('create')
      ->willReturn($this->getNodeMock());

    return $nodeStorage;
  }

  /**
   *
   */
  private function getConfigFactoryMock() {
    $configFactory = $this->getMockBuilder(ConfigFactoryInterface::class)
      ->disableOriginalConstructor()
      ->setMethods(['get'])
      ->getMockForAbstractClass();

    $configFactory->method('get')
      ->willReturn($this->getImmutableConfigMock());

    return $configFactory;
  }

  /**
   *
   */
  private function getImmutableConfigMock() {
    $immutableConfig = $this->getMockBuilder(ImmutableConfig::class)
      ->disableOriginalConstructor()
      ->setMethods(['get'])
      ->getMock();

    $immutableConfig->method('get')
      ->willReturn(Data::PUBLISH_NOW);

    return $immutableConfig;
  }

  /**
   *
   */
  private function getNodeMock() {
    $node = $this->getMockBuilder(NodeInterface::class)
      ->disableOriginalConstructor()
      ->setMethods(['get', 'uuid', 'save'])
      ->getMockForAbstractClass();

    $node->method('get')
      ->willReturn($this->getFieldItemListMock());

    $node->method('uuid')
      ->willReturn(1);

    $node->method('save')
      ->willReturn(1);

    return $node;
  }

  /**
   *
   */
  private function getFieldItemListMock() {
    $list = $this->getMockBuilder(FieldItemListInterface::class)
      ->disableOriginalConstructor()
      ->setMethods(['get'])
      ->getMockForAbstractClass();

    $list->method('get')->willReturn($this->getFieldItemMock());

    return $list;
  }

  /**
   *
   */
  private function getFieldItemMock() {
    $item = $this->getMockBuilder(FieldItemInterface::class)
      ->disableOriginalConstructor()
      ->setMethods(['getValue'])
      ->getMockForAbstractClass();

    $item->method('getValue')->willReturn(['value' => json_encode(["name" => "blah"])]);

    return $item;
  }

  /**
   *
   */
  private function getQueryMock() {
    $query = $this->getMockBuilder(QueryInterface::class)
      ->disableOriginalConstructor()
      ->setMethods(['condition', 'execute'])
      ->getMockForAbstractClass();

    $query->method('condition')
      ->willReturn($query);

    $query->method('execute')->willReturn([1, 2]);

    return $query;
  }

}
