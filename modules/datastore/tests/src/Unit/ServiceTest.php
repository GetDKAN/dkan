<?php

namespace Drupal\Tests\datastore\Unit;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Tests\common\Traits\ServiceCheckTrait;
use Drupal\common\DataResource;
use Drupal\common\Storage\JobStore;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\Service;
use Drupal\datastore\Service\Factory\Import as ImportServiceFactory;
use Drupal\datastore\Service\Import as ImportService;
use Drupal\datastore\Service\Info\ImportInfoList;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\metastore\ResourceMapper;
use Drupal\datastore\Plugin\QueueWorker\FileFetcherJob;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;
use Symfony\Component\DependencyInjection\Container;
use TypeError;

/**
 *
 */
class ServiceTest extends TestCase {
  use ServiceCheckTrait;

  /**
   *
   */
  public function testImport() {
    $resource = new DataResource('http://example.org', 'text/csv');
    $chain = $this->getContainerChainForService('dkan.datastore.service')
      ->add(ResourceLocalizer::class, 'get', $resource)
      ->add(ResourceLocalizer::class, 'getResult', Result::class)
      ->add(FileFetcherJob::class, 'run', Result::class)
      ->add(ResourceMapper::class, 'get', $resource)
      ->add(ImportServiceFactory::class, "getInstance", ImportService::class)
      ->add(ImportService::class, "import", NULL)
      ->add(ImportService::class, "getResult", new Result())
      ->add(QueueFactory::class, "get", NULL)
      ->add(ContainerAwareEventDispatcher::class, "dispatch", NULL);

    $service = Service::create($chain->getMock());
    $result = $service->import("1");

    $this->assertTrue(is_array($result));
  }

  public function testDrop() {
    $resource = new DataResource('http://example.org', 'text/csv');
    $mockChain = $this->getCommonChain()
      ->add(ResourceLocalizer::class, 'get', $resource)
      ->add(ImportServiceFactory::class, 'getInstance', ImportService::class)
      ->add(ImportService::class, 'getStorage', DatabaseTable::class)
      ->add(DatabaseTable::class, 'destruct')
      ->add(ResourceLocalizer::class, 'remove')
      ->add(JobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(JobStore::class, 'remove', TRUE);

    $service = Service::create($mockChain->getMock());
    // Ensure variations on drop return nothing.
    $actual = $service->drop('foo');
    $this->assertNull($actual);
    $actual = $service->drop('foo', '123152');
    $this->assertNull($actual);
    $actual = $service->drop('foo', NULL, FALSE);
    $this->assertNull($actual);
    $this->expectException(TypeError::class);
    $actual = $service->drop('foo', NULL, NULL);
  }

  private function getCommonChain() {
    $options = (new Options())
      ->add('dkan.datastore.service.resource_localizer', ResourceLocalizer::class)
      ->add('dkan.datastore.service.factory.import', ImportServiceFactory::class)
      ->add('queue', QueueFactory::class)
      ->add('dkan.common.job_store', JobStoreFactory::class)
      ->add('dkan.datastore.import_info_list', ImportInfoList::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, 'get', $options);
  }

}
