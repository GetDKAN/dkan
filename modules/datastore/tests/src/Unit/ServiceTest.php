<?php

namespace Drupal\Tests\datastore\Unit;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Tests\common\Traits\ServiceCheckTrait;
use Drupal\common\Resource;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\Service;
use Drupal\datastore\Service\Factory\Import as ImportServiceFactory;
use Drupal\datastore\Service\Import as ImportService;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\metastore\ResourceMapper;
use FileFetcher\FileFetcher;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;
use Symfony\Component\DependencyInjection\Container;

/**
 *
 */
class ServiceTest extends TestCase {
  use ServiceCheckTrait;

  /**
   *
   */
  public function testImport() {

    $chain = $this->getContainerChainForService('dkan.datastore.service')
      ->add(ResourceLocalizer::class, 'get', Resource::class)
      ->add(ResourceLocalizer::class, 'getResult', Result::class)
      ->add(FileFetcher::class, 'run', Result::class)
      ->add(ResourceMapper::class, 'get', Resource::class)
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
    $mockChain = $this->getCommonChain()
      ->add(ResourceLocalizer::class, 'get', Resource::class)
      ->add(ImportServiceFactory::class, 'getInstance', ImportService::class)
      ->add(ImportService::class, 'getStorage', DatabaseTable::class)
      ->add(DatabaseTable::class, 'destroy')
      ->add(ResourceLocalizer::class, 'remove');

    $service = Service::create($mockChain->getMock());
    $actual = $service->drop('foo');

    // Function returns nothing.
    $this->assertNull($actual);
  }

  private function getCommonChain() {
    $options = (new Options())
      ->add('dkan.datastore.service.resource_localizer', ResourceLocalizer::class)
      ->add('dkan.datastore.service.factory.import', ImportServiceFactory::class)
      ->add('queue', QueueFactory::class)
      ->add('dkan.common.job_store', JobStoreFactory::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, 'get', $options);
  }

}
