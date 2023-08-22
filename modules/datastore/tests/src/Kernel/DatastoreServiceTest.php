<?php

namespace Drupal\Tests\datastore\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\common\DataResource;

/**
 * @covers \Drupal\datastore\DatastoreService
 * @coversDefaultClass \Drupal\datastore\DatastoreService
 *
 * @group dkan
 * @group datastore
 * @group kernel
 */
class DatastoreServiceTest extends KernelTestBase {

  protected static $modules = [
    'common',
    'datastore',
    'metastore',
    'node',
  ];

  /**
   * use ServiceCheckTrait;
   */

  public function testDrop() {
    $resource = new DataResource('http://example.org', 'text/csv');

    /** @var \Drupal\metastore\ResourceMapper $resource_mapper */
    $resource_mapper = $this->container->get('dkan.metastore.resource_mapper');
    $resource_mapper->register($resource);

    /** @var \Drupal\datastore\DatastoreService $service */
    $service = $this->container->get('dkan.datastore.service');

    $resource = $resource_mapper->get($resource->getIdentifier());

    // Ensure variations on drop return nothing.
    $actual = $service->drop($resource->getIdentifier(), $resource->getVersion());
    $this->assertNull($actual);
    $actual = $service->drop('foo', '123152');
    $this->assertNull($actual);
    $actual = $service->drop('foo', NULL, FALSE);
    $this->assertNull($actual);
    $this->expectException(\TypeError::class);
    $actual = $service->drop('foo', NULL, NULL);
  }

  // private function getCommonChain() {
  //    $options = (new Options())
  //      ->add('dkan.metastore.resource_mapper', ResourceMapper::class)
  //      ->add('dkan.datastore.service.resource_localizer', ResourceLocalizer::class)
  //      ->add('dkan.datastore.service.factory.import', ImportServiceFactory::class)
  //      ->add('queue', QueueFactory::class)
  //      ->add('dkan.common.job_store', JobStoreFactory::class)
  //      ->add('dkan.datastore.import_info_list', ImportInfoList::class)
  //      ->add('dkan.datastore.service.resource_processor.dictionary_enforcer', DictionaryEnforcer::class)
  //      ->index(0);
  //
  //    return (new Chain($this))
  //      ->add(Container::class, 'get', $options);
  //  }

}
