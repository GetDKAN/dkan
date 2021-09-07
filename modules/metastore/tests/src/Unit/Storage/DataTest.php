<?php

namespace Drupal\Tests\metastore\Unit\Storage;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryInterface;
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
    $this->assertEquals('field_json_metadata', $data->getMetadataField());
  }

  public function testPublishDatasetNotFound() {

    $etmMock = $this->getEtmChain()
      ->add(QueryInterface::class, 'execute', [])
      ->getMock();

    $this->expectExceptionMessage('Error publishing dataset: 1 not found.');
    $nodeData = new NodeData('dataset', $etmMock);
    $nodeData->publish('1');
  }

  public function testPublishDraftDataset() {

    $etmMock = $this->getEtmChain()
      ->add(Node::class, 'get', 'draft')
      ->add(Node::class, 'set')
      ->add(Node::class, 'save')
      ->getMock();

    $nodeData = new NodeData('dataset', $etmMock);
    $result = $nodeData->publish('1');
    $this->assertEquals(TRUE, $result);
  }

  public function testPublishDatasetAlreadyPublished() {

    $etmMock = $this->getEtmChain()
      ->add(Node::class, 'get', 'published')
      ->getMock();

    $nodeData = new NodeData('dataset', $etmMock);
    $result = $nodeData->publish('1');
    $this->assertEquals(FALSE, $result);
  }

  private function getEtmChain() {

    return (new Chain($this))
      ->add(EntityTypeManager::class, 'getStorage', NodeStorage::class)
      ->add(NodeStorage::class, 'getQuery', QueryInterface::class)
      ->add(QueryInterface::class, 'condition', QueryInterface::class)
      ->add(QueryInterface::class, 'execute', ['1'])
      ->add(NodeStorage::class, 'getLatestRevisionId', '2')
      ->addd('loadRevision', Node::class);
  }

}
