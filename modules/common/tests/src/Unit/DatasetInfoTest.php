<?php

namespace Drupal\Tests\common\Unit;

use Drupal\common\DatasetInfo;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\datastore\Service as Datastore;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Storage\DataFactory;
use Drupal\metastore\Storage\NodeData;
use Drupal\node\Entity\Node;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

/**
 *
 */
class DatasetInfoTest extends TestCase {

  /**
   *
   */
  public function testMetastoreNotEnabled() {
    $datasetInfo = DatasetInfo::create($this->getCommonChain()->getMock());

    $expected = [
      'notice' => 'The DKAN Metastore module is not enabled.',
    ];
    $result = $datasetInfo->gather('foo');

    $this->assertEquals($expected, $result);
  }

  /**
   *
   */
  public function testUuidNotFound() {
    $mockStorage = (new Chain($this))
      ->add(DataFactory::class, 'getInstance', NodeData::class)
      ->add(NodeData::class, 'getEntityLatestRevision', FALSE);
    $mockDatastore = (new Chain($this))
      ->add(Datastore::class);
    $mockResourceMapper = (new Chain($this))
      ->add(ResourceMapper::class);

    $datasetInfo = DatasetInfo::create($this->getCommonChain()->getMock());
    $datasetInfo->setStorage($mockStorage->getMock());
    $datasetInfo->setDatastore($mockDatastore->getMock());
    $datasetInfo->setResourceMapper($mockResourceMapper->getMock());

    $expected = [
      'notice' => 'Not found',
    ];
    $result = $datasetInfo->gather('foo');

    $this->assertEquals($expected, $result);
  }

  public function testDistributionNoResource() {

    $metadata = [
      'title' => 'no resources',
      'identifier' => 'dataset-id',
      'modified' => '2020-06-11T00:30:45+00:00',
      '%modified' => '2021-07-12',
      'distribution' =>
        [
          [
            '@type' => 'dcat:Distribution',
            'title' => 'bo resources distribution',
          ],
        ],
      '%Ref:distribution' =>
        [
          [
            'identifier' => 'distribution-id',
            'data' =>
              [
                '@type' => 'dcat:Distribution',
                'title' => 'no resources distribution',
              ],
          ],
        ]
    ];

    $mockFieldJsonMetadata = (new Chain($this))
      ->add(FieldItemListInterface::class, 'getString', json_encode($metadata));
    $mockModerationState = (new Chain($this))
      ->add(FieldItemListInterface::class, 'getString', 'published');

    $nodeGetOptions = (new Options())
      ->add('field_json_metadata', $mockFieldJsonMetadata->getMock())
      ->add('moderation_state', $mockModerationState->getMock());

    $mockNode = (new Chain($this))
      ->add(Node::class, 'uuid', 'dataset-id')
      ->add(Node::class, 'id', '1')
      ->add(Node::class, 'getRevisionId', '1')
      ->add(Node::class, 'get', $nodeGetOptions);

    $mockStorage = (new Chain($this))
      ->add(DataFactory::class, 'getInstance', NodeData::class)
      ->add(NodeData::class, 'getEntityLatestRevision', $mockNode->getMock())
      ->add(NodeData::class, 'getEntityPublishedRevision', $mockNode->getMock());

    $datasetInfo = DatasetInfo::create($this->getCommonChain()->getMock());
    $datasetInfo->setStorage($mockStorage->getMock());

    $result = $datasetInfo->gather('foo');
    $this->assertEquals('No resource found', $result["latest_revision"]["distributions"][0][0]);
  }

  /**
   *
   */
  private function getCommonChain() {
    $options = (new Options())
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, 'get', $options);
  }

}
