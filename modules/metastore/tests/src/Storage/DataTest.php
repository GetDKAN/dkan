<?php

namespace Drupal\Tests\common\Unit\Storage;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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
    $store = new Data($this->getEntityTypeManagerMock(), $this->getConfigFactoryMock());
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
    $store = new Data($this->getEntityTypeManagerMock(), $this->getConfigFactoryMock());
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

    $store = new Data($this->getEntityTypeManagerMock(), $this->getConfigFactoryMock());
    $store->setSchema('dataset');
    $id = $store->store($object, 1);

    $this->assertEquals(1, $id);
  }

  /**
   *
   */
  public function testStoreNew() {
    $this->node = NULL;
    $object = '{"name":"blah", "digit":1}';

    $store = new Data($this->getEntityTypeManagerMock(), $this->getConfigFactoryMock());
    $store->setSchema('dataset');
    $id = $store->store($object, 1);

    $this->assertEquals(1, $id);
  }

  /**
   *
   */
  public function testPublishExceptionSchemaNotSet() {
    \Drupal::unsetContainer();
    $container = new ContainerBuilder();
    $container->set('entity_type.manager', $this->getEntityTypeManagerMock());
    $container->set('config.factory', $this->getConfigFactoryMock());
    \Drupal::setContainer($container);

    $store = Data::create($container);
    $this->expectExceptionMessage("Data schemaId not set.");
    $store->publish(1);
  }

  /**
   *
   */
  public function testPublishExceptionSchemaNotDataset() {
    $store = new Data($this->getEntityTypeManagerMock(), $this->getConfigFactoryMock());
    $store->setSchema('foobar');
    $this->expectExceptionMessage("Publishing currently only implemented for datasets.");
    $store->publish(1);
  }

  /**
   *
   */
  public function testPublishExceptionDataNotFound() {
    $this->node = $this->getNodeMock();
    $store = new Data($this->getEntityTypeManagerMock(), $this->getConfigFactoryMock());
    $store->setSchema('dataset');
    $this->expectExceptionMessage("No data with that identifier was found.");
    $store->publish(2);
  }

  /**
   *
   */
  public function testPublish() {
    $this->node = $this->getNodeMock();
    $store = new Data($this->getEntityTypeManagerMock(), $this->getConfigFactoryMock());
    $store->setSchema('dataset');
    $publish = $store->publish(1);
    $this->assertEquals($publish, 1);
  }

  /**
   *
   */
  public function testRemove() {
    $this->node = $this->getNodeMock();
    $store = new Data($this->getEntityTypeManagerMock(), $this->getConfigFactoryMock());
    $store->setSchema('dataset');
    $removed = $store->remove(1);

    $this->assertEquals(TRUE, $removed);
  }

  /**
   *
   */
  public function testRemoveFailure() {
    $store = new Data($this->getEntityTypeManagerMock(), $this->getConfigFactoryMock());
    $store->setSchema('dataset');
    $removed = $store->remove(2);

    $this->assertEquals(FALSE, $removed);
  }

  /**
   *
   */
  public function testRetrieveAllException() {
    $this->expectExceptionMessage("Data schemaId not set.");
    $store = new Data($this->getEntityTypeManagerMock(), $this->getConfigFactoryMock());
    $store->retrieveAll();
  }

  /**
   *
   */
  public function testRetrieveExceptionSchemaNotSet() {
    $this->expectExceptionMessage("Data schemaId not set.");
    $store = new Data($this->getEntityTypeManagerMock(), $this->getConfigFactoryMock());
    $store->retrieve(1);
  }

  /**
   *
   */
  public function testRetrieveExceptionDataNotFound() {
    $this->expectExceptionMessage("No data with that identifier was found.");
    $store = new Data($this->getEntityTypeManagerMock(), $this->getConfigFactoryMock());
    $store->setSchema('dataset');
    $store->retrieve(2);
  }

  /**
   *
   */
  public function testStoreException() {
    $this->expectExceptionMessage("Data schemaId not set.");
    $object = '{"name":"blah"}';
    $store = new Data($this->getEntityTypeManagerMock(), $this->getConfigFactoryMock());
    $store->store($object, 1);
  }

  /**
   *
   */
  private function getEntityTypeManagerMock() {
    $entityTypeManager = $this->getMockBuilder(EntityTypeManagerInterface::class)
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
      ->willReturn(Data::PUBLISH_IMMEDIATELY);

    return $immutableConfig;
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
      ->willReturnMap([
        [['type' => 'data', 'uuid' => "1"], [$this->node]],
        [['type' => 'data', 'uuid' => "2"], FALSE],
      ]);

    $nodeStorage->method('create')
      ->willReturn($this->getNodeMock());

    $nodeStorage->method('getLatestRevisionId')
      ->willReturn(1);

    $nodeStorage->method('loadRevision')
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
      ->willReturn($this->getFieldItemListMock());

    $node->method('uuid')
      ->willReturn(1);

    $node->method('save')
      ->willReturn(1);

    $node->method('isDefaultRevision')
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
