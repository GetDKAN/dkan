<?php

namespace Drupal\Tests\metastore\Unit\Storage;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\metastore\Storage\NodeData;
use Drupal\node\Entity\Node;
use Drupal\node\NodeStorage;
use MockChain\Chain;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @group dkan
 * @group metastore
 * @group unit
 */
class DataTest extends TestCase {

  /**
   * List html allowed schema properties properties.
   *
   * @var string[]
   */
  public const HTML_ALLOWED_PROPERTIES = [
    'dataset_description' => 'dataset_description',
    'distribution_description' => 'distribution_description',
    'dataset_title' => 0,
    'dataset_identifier' => 0,
  ];

  public function testGetStorageNode() {
    $immutableConfig = (new Chain($this))
      ->add(ImmutableConfig::class, 'get', self::HTML_ALLOWED_PROPERTIES)
      ->getMock();
    $configFactoryMock = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', $immutableConfig)
      ->getMock();

    $data = new NodeData('dataset', $this->getEtmChain()->getMock(), $configFactoryMock, $this->createStub(LoggerInterface::class));
    $this->assertInstanceOf(NodeStorage::class, $data->getEntityStorage());
  }

  public function testPublishDatasetNotFound() {

    $etmMock = $this->getEtmChain()
      ->add(QueryInterface::class, 'execute', [])
      ->getMock();
    $immutableConfig = (new Chain($this))
      ->add(ImmutableConfig::class, 'get', self::HTML_ALLOWED_PROPERTIES)
      ->getMock();
    $configFactoryMock = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', $immutableConfig)
      ->getMock();

    $this->expectExceptionMessage('Error: 1 not found.');
    $nodeData = new NodeData('dataset', $etmMock, $configFactoryMock, $this->createStub(LoggerInterface::class));
    $nodeData->publish('1');
  }

  public function testPublishDraftDataset() {

    $etmMock = $this->getEtmChain()
      ->add(Node::class, 'get', FieldItemListInterface::class)
      ->add(FieldItemListInterface::class, 'getString', 'draft')
      ->add(Node::class, 'set')
      ->add(Node::class, 'save')
      ->getMock();
    $immutableConfig = (new Chain($this))
      ->add(ImmutableConfig::class, 'get', self::HTML_ALLOWED_PROPERTIES)
      ->getMock();
    $configFactoryMock = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', $immutableConfig)
      ->getMock();

    $nodeData = new NodeData('dataset', $etmMock, $configFactoryMock, $this->createStub(LoggerInterface::class));
    $result = $nodeData->publish('1');
    $this->assertEquals(TRUE, $result);
  }

  public function testPublishDatasetAlreadyPublished() {

    $etmMock = $this->getEtmChain()
      ->add(Node::class, 'get', FieldItemListInterface::class)
      ->add(FieldItemListInterface::class, 'getString', 'published')
      ->getMock();
    $immutableConfig = (new Chain($this))
      ->add(ImmutableConfig::class, 'get', self::HTML_ALLOWED_PROPERTIES)
      ->getMock();
    $configFactoryMock = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', $immutableConfig)
      ->getMock();

    $nodeData = new NodeData('dataset', $etmMock, $configFactoryMock, $this->createStub(LoggerInterface::class));
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
    $immutableConfig = (new Chain($this))
      ->add(ImmutableConfig::class, 'get', self::HTML_ALLOWED_PROPERTIES)
      ->getMock();
    $configFactoryMock = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', $immutableConfig)
      ->getMock();

    // Create Data object.
    $nodeData = new NodeData('dataset', $etmMock, $configFactoryMock, $this->createStub(LoggerInterface::class));
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
          return $this->uuid ?? ($this->uuid = uniqid());
        }
      };
      $uuids[$i] = $nodes[$i]->uuid();
    }

    // Create mock chain for testing ::retrieveIds() method.
    $etmMock = $this->getEtmChain()
      ->add(NodeStorage::class, 'loadMultiple', $nodes)
      ->getMock();
    $immutableConfig = (new Chain($this))
      ->add(ImmutableConfig::class, 'get', self::HTML_ALLOWED_PROPERTIES)
      ->getMock();
    $configFactoryMock = (new Chain($this))
      ->add(ConfigFactoryInterface::class, 'get', $immutableConfig)
      ->getMock();

    // Create Data object.
    $nodeData = new NodeData('dataset', $etmMock, $configFactoryMock, $this->createStub(LoggerInterface::class));
    // Ensure the returned uuids match those belonging to the generated nodes.
    $this->assertEquals($uuids, $nodeData->retrieveIds(1, 5));
  }

}
