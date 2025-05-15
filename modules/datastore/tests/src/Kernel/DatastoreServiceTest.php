<?php

declare(strict_types=1);

namespace Drupal\Tests\datastore\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\common\Storage\DatabaseTableInterface;
use Drupal\datastore\DatastoreService;
use Drupal\datastore\Service\ResourceLocalizer;

/**
 * @coversDefaultClass \Drupal\datastore\DatastoreService
 *
 * @group dkan
 * @group datastore
 * @group kernel
 */
class DatastoreServiceTest extends KernelTestBase {

  protected $strictConfigSchema = FALSE;

  protected static $modules = [
    'common',
    'datastore',
    'metastore',
  ];

  /**
   * @covers ::drop
   *
   * It's possible for drop() to receive an id and version that yield a null
   * resource object from the resource localizer, even though there is a valid
   * storage object. Therefore, we have to test that drop() can deal with that
   * situation.
   */
  public function testNullResource() {
    $this->installEntitySchema('resource_mapping');

    // Mock the resource localizer service so it returns NULL.
    $resource_localizer = $this->getMockBuilder(ResourceLocalizer::class)
      ->setConstructorArgs([
        $this->container->get('dkan.metastore.resource_mapper'),
        $this->container->get('dkan.common.file_fetcher'),
        $this->container->get('dkan.common.drupal_files'),
        $this->container->get('dkan.common.filefetcher_job_store_factory'),
        $this->container->get('queue'),
        $this->container->get('event_dispatcher')
      ])
      ->onlyMethods(['get'])
      ->getMock();
    $resource_localizer->expects($this->any())
      ->method('get')
      // We always return NULL.
      ->willReturn(NULL);

    $this->container->set('dkan.datastore.service.resource_localizer', $resource_localizer);

    // Mock the datastore service so we can create a stub storage object.
    $datastore_service = $this->getMockBuilder(DatastoreService::class)
      ->setConstructorArgs([
        $this->container->get('dkan.datastore.service.resource_localizer'),
        $this->container->get('dkan.datastore.service.factory.import'),
        $this->container->get('queue'),
        $this->container->get('dkan.datastore.import_job_store_factory'),
        $this->container->get('dkan.datastore.service.resource_processor.dictionary_enforcer'),
        $this->container->get('dkan.metastore.resource_mapper'),
        $this->container->get('event_dispatcher'),
        $this->container->get('dkan.metastore.reference_lookup'),
      ])
      ->onlyMethods(['getStorage', 'invalidateCacheTags'])
      ->getMock();
    $datastore_service->expects($this->once())
      ->method('getStorage')
      ->willReturn($this->createStub(DatabaseTableInterface::class));
    // Mock invalidateCacheTags() to reduce dependencies.
    $datastore_service->expects($this->once())
      ->method('invalidateCacheTags');

    $datastore_service->drop('id', 'version');
  }

}
