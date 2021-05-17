<?php

namespace Drupal\Tests\datastore\Unit\Service\Info;

use Drupal\common\Storage\JobStore;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\Service\Info\ImportInfo;
use Drupal\datastore\Service\Info\ImportInfoList;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

class ImportInfoListTest extends TestCase {
  public function test() {

    $services = (new Options())
      ->add('dkan.common.job_store', JobStoreFactory::class)
      ->add('dkan.datastore.import_info', ImportInfo::class)
      ->index(0);

    $container = (new Chain($this))
      ->add(Container::class, 'get', $services)
      ->add(JobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(JobStore::class, 'retrieveAll', [])
      ->getMock();

    $list = ImportInfoList::create($container);
    $list->buildList();
    $this->assertTrue(true);
  }
}
