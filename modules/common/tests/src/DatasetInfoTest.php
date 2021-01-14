<?php

namespace Drupal\Tests\common;

use Drupal\common\Resource;
use Drupal\common\DatasetInfo;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\datastore\Service as Datastore;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Service as Metastore;
use Drupal\metastore\Storage\Data;
use Drupal\metastore\Storage\DataFactory;
use Drupal\node\Entity\Node;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

class DatasetInfoTest extends TestCase {

  public function testMetastoreNotEnabled() {
    $datasetInfo = DatasetInfo::create($this->getCommonChain()->getMock());

    $expected = [
      'uuid' => 'foo',
      'notice' => 'The DKAN Metastore module is not enabled.',
    ];
    $result = $datasetInfo->gather('foo');

    $this->assertEquals($expected, $result);
  }

  public function testUuidNotFound() {
    $mockStorage = $this->getMockStorage()
      ->add(DataFactory::class, 'getInstance', Data::class)
      ->add(Data::class, 'getNodeLatestRevision', FALSE);

    $datasetInfo = DatasetInfo::create($this->getCommonChain()->getMock());
    $datasetInfo->setMetastore($this->getMockMetastore()->getMock());
    $datasetInfo->setStorage($mockStorage->getMock());
    $datasetInfo->setDatastore($this->getMockDatastore()->getMock());
    $datasetInfo->setResourceMapper($this->getMockResourceMapper()->getMock());

    $expected = [
      'uuid' => 'foo',
      'notice' => 'Not found.',
    ];
    $result = $datasetInfo->gather('foo');

    $this->assertEquals($expected, $result);
  }

  public function testHappyPath() {
    $datasetInfo = DatasetInfo::create($this->getCommonChain()->getMock());
    $datasetInfo->setMetastore($this->getMockMetastore()->getMock());
    $datasetInfo->setStorage($this->getMockStorage()->getMock());
    $datasetInfo->setDatastore($this->getMockDatastore()->getMock());
    $datasetInfo->setResourceMapper($this->getMockResourceMapper()->getMock());

    $expected = [
      'uuid' => 'foo',
      'node id' => 1,
      'latest revision' => [
        'revision id' => 3,
        'moderation state' => 'published',
        'modified date' => 'timestamp',
        'distributions' => [
          0 => [
            0 => [
              'identifier' => 'c9c387a12a',
              'version' => '1610000000',
              'file path' => 'public://resources/identifier_version/file.csv',
              'table name' => 'bar',
            ],
          ],
        ],
      ],
    ];
    $result = $datasetInfo->gather('foo');

    $this->assertEquals($expected, $result);
  }

  private function getMockDistribution() {
    return [
      (object) [
        '%Ref:downloadURL' => [
          (object) [
            'data' => (object) [
              'identifier' => 'c9c387a12a',
              'version' => '1610000000',
              'file path' => 'public://resources/identifier_version/file.csv',
              'table name' => 'bar',
            ],
          ],
        ],
      ],
    ];
  }

  private function getMockMetastore() {
    return (new Chain($this))
      ->add(Metastore::class, 'getResources', $this->getMockDistribution());
  }

  private function getMockNode() {
    return (new Chain($this))
      ->add(Node::class, 'id', 1)
      ->add(Node::class, 'uuid', 'foo')
      ->add(Node::class, 'get', FieldItemListInterface::class)
      ->add(FieldItemListInterface::class, 'getString', 'published')
      ->add(Node::class, 'getRevisionId', 3)
      ->add(Node::class, 'getChangedTime', 'timestamp');
  }

  private function getMockStorage() {
    return (new Chain($this))
      ->add(DataFactory::class, 'getInstance', Data::class)
      ->add(Data::class, 'getNodeLatestRevision', $this->getMockNode()->getMock())
      ->add(Data::class, 'getNodePublishedRevision', $this->getMockNode()->getMock());
  }

  private function getMockDatastore() {
    return (new Chain($this))
      ->add(Datastore::class, 'getStorage', DatabaseTable::class)
      ->add(DatabaseTable::class, 'getTableName', 'bar');
  }

  private function getMockResourceMapper() {
    return (new Chain($this))
      ->add(ResourceMapper::class, 'get', Resource::class)
      ->add(Resource::class, 'getFilePath', 'public://resources/identifier_version/file.csv');
  }

  private function getCommonChain() {
    $options = (new Options())
      ->add('module_handler', ModuleHandlerInterface::class)
      ->index(0);

    $commonChain = (new Chain($this))
      ->add(Container::class, 'get', $options);

    return $commonChain;
  }

}
