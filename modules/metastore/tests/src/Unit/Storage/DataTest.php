<?php

namespace Drupal\Tests\metastore\Unit\Storage;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\metastore\Storage\NodeData;
use Drupal\node\Entity\Node;
use Drupal\node\NodeStorage;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;

/**
 * Class DataTest
 *
 * @package Drupal\Tests\metastore\Storage
 */
class DataTest extends TestCase {

  public function testGetStorageNode() {

    $data = new NodeData('dataset', $this->getEtmChain()->getMock());
    $this->assertInstanceOf(NodeStorage::class, $data->getEntityStorage());
  }

  public function testPublishDatasetNotFound() {

    $etmMock = $this->getEtmChain()
      ->add(QueryInterface::class, 'execute', [])
      ->getMock();

    $this->expectExceptionMessage('Error: 1 not found.');
    $nodeData = new NodeData('dataset', $etmMock);
    $nodeData->publish('1');
  }

  public function testPublishDraftDataset() {

    $etmMock = $this->getEtmChain()
      ->add(Node::class, 'get', FieldItemListInterface::class)
      ->add(FieldItemListInterface::class, 'getString', 'draft')
      ->add(Node::class, 'set')
      ->add(Node::class, 'save')
      ->getMock();

    $nodeData = new NodeData('dataset', $etmMock);
    $result = $nodeData->publish('1');
    $this->assertEquals(TRUE, $result);
  }

  public function testPublishDatasetAlreadyPublished() {

    $etmMock = $this->getEtmChain()
      ->add(Node::class, 'get', FieldItemListInterface::class)
      ->add(FieldItemListInterface::class, 'getString', 'published')
      ->getMock();

    $nodeData = new NodeData('dataset', $etmMock);
    $result = $nodeData->publish('1');
    $this->assertEquals(FALSE, $result);
  }

  private function getEtmChain() {

    return (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', NodeStorage::class)
      ->add(NodeStorage::class, 'getQuery', QueryInterface::class)
      ->add(QueryInterface::class, 'accessCheck', QueryInterface::class)
      ->add(QueryInterface::class, 'condition', QueryInterface::class)
      ->add(QueryInterface::class, 'count', QueryInterface::class)
      ->add(QueryInterface::class, 'range', QueryInterface::class)
      ->add(QueryInterface::class, 'execute', ['1'])
      ->add(NodeStorage::class, 'getLatestRevisionId', '2')
      ->addd('loadRevision', Node::class);
  }

  /**
   * Test \Drupal\metastore\Storage\Data::count() method.
   */
  public function testCount(): void {
    // Set constant which should be returned by the ::count() method.
    $count = 5;

    // Create mock chain for testing ::count() method.
    $etmMock = $this->getEtmChain()
      ->add(QueryInterface::class, 'execute', $count)
      ->getMock();

    // Create Data object.
    $nodeData = new NodeData('dataset', $etmMock);
    // Ensure count matches return value.
    $this->assertEquals($count, $nodeData->count());
  }

  /**
   * Test \Drupal\metastore\Storage\Data::retrieveIds() method.
   */
  public function testRetrieveRangeUuids(): void {
    // Generate dataset nodes for testing ::retrieveIds().
    $nodes = [];
    $uuids = [];

    for ($i = 0; $i < 5; $i ++) {
      $nodes[$i] = new class {
        private $uuid;
        public function uuid() {
          return isset($this->uuid) ? $this->uuid : $this->uuid = uniqid();
        }
      };
      $uuids[$i] = $nodes[$i]->uuid();
    }

    // Create mock chain for testing ::retrieveIds() method.
    $etmMock = $this->getEtmChain()
      ->add(NodeStorage::class, 'loadMultiple', $nodes)
      ->getMock();

    // Create Data object.
    $nodeData = new NodeData('dataset', $etmMock);
    // Ensure the returned uuids match those belonging to the generated nodes.
    $this->assertEquals($uuids, $nodeData->retrieveIds(1, 5));
  }

}
