<?php

namespace Drupal\Tests\common\Unit\Storage;

use Drupal\Core\Entity\EntityTypeManager;
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
    $store = new Data($this->getEntityTypeManagerMock());
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
    $store = new Data($this->getEntityTypeManagerMock());
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

    $store = new Data($this->getEntityTypeManagerMock());
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

    $store = new Data($this->getEntityTypeManagerMock());
    $store->setSchema('dataset');
    $id = $store->store($object, 1);

    $this->assertEquals(1, $id);
  }

  /**
   *
   */
  public function testRemove() {
    $this->node = $this->getNodeMock();
    $store = new Data($this->getEntityTypeManagerMock());
    $store->setSchema('dataset');
    $removed = $store->remove(1);

    $this->assertEquals(NULL, $removed);
  }

  /**
   *
   */
  public function testRetrieveAllException() {
    $this->expectExceptionMessage("Data schema id not set.");
    $store = new Data($this->getEntityTypeManagerMock());
    $store->retrieveAll();
  }

  /**
   *
   */
  public function testRetrieveException() {
    $this->expectExceptionMessage("Data schema id not set.");
    $store = new Data($this->getEntityTypeManagerMock());
    $store->retrieve(1);
  }

  /**
   *
   */
  public function testStoreException() {
    $this->expectExceptionMessage("Data schema id not set.");
    $object = '{"name":"blah"}';
    $store = new Data($this->getEntityTypeManagerMock());
    $store->store($object, 1);
  }

  /**
   *
   */
  /**
   *
   */
  private function getEntityTypeManagerMock() {
    $entityTypeManager = $this->getMockBuilder(EntityTypeManager::class)
      ->disableOriginalConstructor()
      ->setMethods(['getStorage'])
      ->getMockForAbstractClass();

    $entityTypeManager->method('getStorage')
      ->willReturn($this->getNodeStorageMock());

    return $entityTypeManager;
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
  private function getNodeMock() {
    $node = $this->getMockBuilder(NodeInterface::class)
      ->disableOriginalConstructor()
      ->setMethods(['get', 'uuid', 'save'])
      ->getMockForAbstractClass();

    $node->method('get')
      ->willReturn($this->getFieldItemMock());

    $node->method('uuid')
      ->willReturn(1);

    $node->method('save')
      ->willReturn(1);

    return $node;
  }

  /**
   *
   */
  private function getFieldItemMock() {
    $item = $this->getMockBuilder(FieldItemInterface::class)
      ->disableOriginalConstructor()
      ->setMethods(['getString'])
      ->getMockForAbstractClass();

    $item->method('getString')->willReturn(json_encode(["name" => "blah"]));

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
