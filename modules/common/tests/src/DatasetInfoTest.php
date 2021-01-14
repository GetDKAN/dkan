<?php

namespace Drupal\Tests\common;

use Drupal\common\DatasetInfo;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\datastore\Service as Datastore;
use Drupal\metastore\ResourceMapper;
use Drupal\metastore\Service as Metastore;
use Drupal\metastore\Storage\Data;
use Drupal\metastore\Storage\DataFactory;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;

class DatasetInfoTest extends TestCase {

  public function testAllModulesEnabled() {
    $datasetInfo = DatasetInfo::create($this->getCommonChain()->getMock());
    $datasetInfo->setMetastore($this->getMockMetastore()->getMock());
    $datasetInfo->setStorage($this->getMockStorage()->getMock());
    $datasetInfo->setDatastore($this->getMockDatastore()->getMock());
    $datasetInfo->setResourceMapper($this->getMockResourceMapper()->getMock());

    $expected = [
      'uuid' => 'foo',
    ];
    $result = $datasetInfo->gather('foo');

    $this->assertEquals($expected, $result);
  }

  public function testMetastoreNotEnabled() {
    $datasetInfo = DatasetInfo::create($this->getCommonChain()->getMock());

    $expected = [
      'uuid' => 'foo',
      'notice' => 'The DKAN Metastore module is not enabled, reducing the available information.',
    ];
    $result = $datasetInfo->gather('foo');

    $this->assertEquals($expected, $result);
  }

  public function testDatastoreNotEnabled() {
    $datasetInfo = DatasetInfo::create($this->getCommonChain()->getMock());
    $datasetInfo->setMetastore($this->getMockMetastore()->getMock());
    $datasetInfo->setStorage($this->getMockStorage()->getMock());

    $expected = [
      'uuid' => 'foo',
      'notice' => 'The DKAN Datastore module is not enabled, reducing the available information.',
    ];
    $result = $datasetInfo->gather('foo');

    $this->assertEquals($expected, $result);
  }

  private function getMockMetastore() {
    return (new Chain($this))
      ->add(Metastore::class);
  }

  private function getMockStorage() {
    return (new Chain($this))
      ->add(DataFactory::class, 'getInstance', Data::class);
  }

  private function getMockDatastore() {
    return (new Chain($this))
      ->add(Datastore::class);
  }

  private function getMockResourceMapper() {
    return (new Chain($this))
      ->add(ResourceMapper::class);
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
