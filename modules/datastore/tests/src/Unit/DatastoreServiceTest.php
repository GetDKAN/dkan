<?php

namespace Drupal\Tests\datastore\Unit;

use Drupal\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Drupal\Core\Queue\QueueFactory;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\datastore\Storage\ImportJobStoreFactory;
use Drupal\Tests\common\Traits\ServiceCheckTrait;
use Drupal\common\DataResource;
use Drupal\common\Storage\JobStore;
use Drupal\common\Storage\JobStoreFactory;
use Drupal\datastore\DatastoreService;
use Drupal\datastore\Service\Factory\ImportServiceFactory;
use Drupal\datastore\Service\ImportService;
use Drupal\datastore\Service\Info\ImportInfoList;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\metastore\Reference\ReferenceLookup;
use Drupal\datastore\Service\ResourceProcessor\DictionaryEnforcer;
use Drupal\datastore\Storage\DatabaseTable;
use Drupal\metastore\ResourceMapper;
use FileFetcher\FileFetcher;
use MockChain\Chain;
use MockChain\Options;
use PHPUnit\Framework\TestCase;
use Procrastinator\Result;
use Symfony\Component\DependencyInjection\Container;

/**
 * @covers \Drupal\datastore\DatastoreService
 * @coversDefaultClass \Drupal\datastore\DatastoreService
 *
 * @group dkan
 * @group datastore
 * @group unit
 */
class DatastoreServiceTest extends TestCase {

  use ServiceCheckTrait;

  /**
   *
   */
  public function testImport() {
    $resource = new DataResource('http://example.org', 'text/csv');
    $chain = $this->getContainerChainForService('dkan.datastore.service')
      ->add(ResourceLocalizer::class, 'get', $resource)
      ->add(FileFetcher::class, 'run', Result::class)
      ->add(ResourceMapper::class, 'get', $resource)
      ->add(ImportServiceFactory::class, "getInstance", ImportService::class)
      ->add(ImportService::class, "import", NULL)
      ->add(ImportService::class, 'getImporter', ImportJob::class)
      ->add(ImportJob::class, 'getResult', new Result())
      ->add(QueueFactory::class, "get", NULL)
      ->add(ContainerAwareEventDispatcher::class, "dispatch", NULL);

    $service = DatastoreService::create($chain->getMock());
    $result = $service->import("1");

    $this->assertTrue(is_array($result));
  }

  public function testDrop() {
    $resource = new DataResource('http://example.org', 'text/csv');
    $mockChain = $this->getCommonChain()
      ->add(ImportServiceFactory::class, 'getInstance', ImportService::class)
      ->add(ImportService::class, 'getStorage', DatabaseTable::class)
      ->add(DatabaseTable::class, 'destruct')
      ->add(ResourceLocalizer::class, 'remove')
      ->add(ResourceLocalizer::class, 'get', $resource)
      ->add(ResourceMapper::class, 'get', $resource)
      ->add(JobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(JobStore::class, 'remove', TRUE)
      ->add(ImportJobStoreFactory::class, 'getInstance', JobStore::class)
      ->add(ReferenceLookup::class, 'getReferencers', [$resource->getIdentifier()])
      ->add(ReferenceLookup::class, 'invalidateReferencerCacheTags');

    $service = DatastoreService::create($mockChain->getMock());
    // These are all valid ways to call drop().
    $this->assertNull($service->drop('foo'));
    $this->assertNull($service->drop('foo', '123152'));
    $this->assertNull($service->drop('foo', NULL, FALSE));
    // Should throw a TypeError on the NULL third argument.
    $this->expectException(\TypeError::class);
    $service->drop('foo', NULL, NULL);
  }

  /**
   * Testing Get Data Dictionary Fields.
   */
  public function testGetDataDictionaryFields() {
    $chain = $this->getCommonChain()
      ->add(DictionaryEnforcer::class, 'returnDataDictionaryFields', ['data' => ['fields' => []]]);

    $service = DatastoreService::create($chain->getMock());
    $result = $service->getDataDictionaryFields();

    $this->assertTrue(is_array($result));
  }

  private function getCommonChain() {
    $options = (new Options())
      ->add('event_dispatcher', ContainerAwareEventDispatcher::class)
      ->add('dkan.metastore.resource_mapper', ResourceMapper::class)
      ->add('dkan.datastore.service.resource_localizer', ResourceLocalizer::class)
      ->add('dkan.datastore.service.factory.import', ImportServiceFactory::class)
      ->add('queue', QueueFactory::class)
      ->add('dkan.common.job_store', JobStoreFactory::class)
      ->add('dkan.datastore.import_job_store_factory', ImportJobStoreFactory::class)
      ->add('dkan.datastore.import_info_list', ImportInfoList::class)
      ->add('dkan.datastore.service.resource_processor.dictionary_enforcer', DictionaryEnforcer::class)
      ->add('dkan.metastore.reference_lookup', ReferenceLookup::class)
      ->index(0);

    return (new Chain($this))
      ->add(Container::class, 'get', $options);
  }

}
