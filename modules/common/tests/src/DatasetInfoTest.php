<?php

namespace Drupal\Tests\common;

use Drupal\common\DatasetInfo;
use Drupal\Core\DependencyInjection\Container;
use Drupal\datastore\Service as Datastore;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Storage\Data;
use Drupal\metastore\Storage\DataFactory;
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
      ->add(DataFactory::class, 'getInstance', Data::class)
      ->add(Data::class, 'getNodeLatestRevision', FALSE);
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
