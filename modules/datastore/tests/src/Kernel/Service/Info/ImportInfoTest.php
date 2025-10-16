<?php

namespace Drupal\Tests\datastore\Kernel\Service;

use Drupal\common\DataResource;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\KernelTests\KernelTestBase;
use Procrastinator\Result;

/**
 * @covers \Drupal\datastore\Service\Info\ImportInfo
 * @coversDefaultClass \Drupal\datastore\Service\Info\ImportInfo
 *
 * @group dkan
 * @group datastore
 * @group kernel
 */
class ImportInfoTest extends KernelTestBase {

  protected static $modules = [
    'node',
    'user',
    'common',
    'datastore',
    'metastore',
  ];

  /**
   * HTTP host protocol and domain for testing download URL.
   *
   * @var string
   */
  const HOST = 'http://example.com';

  protected const SOURCE_URL = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv';

  protected function setUp() : void {
    parent::setUp();
    $this->installEntitySchema('resource_mapping');
  }

  public function testFileSize() {
    $source_resource = new DataResource(
      self::SOURCE_URL,
      'text/csv',
      DataResource::DEFAULT_SOURCE_PERSPECTIVE
    );
    // Add our data resource to the mapper.
    /** @var \Drupal\metastore\ResourceMapper $mapper */
    $mapper = $this->container->get('dkan.metastore.resource_mapper');
    $mapper->register($source_resource);
    $this->assertInstanceOf(
      DataResource::class,
      $source_resource = $mapper->get($source_resource->getIdentifier())
    );
    // Our localized perspective does not yet exist.
    $this->assertNull(
      $mapper->get($source_resource->getIdentifier(), ResourceLocalizer::LOCAL_FILE_PERSPECTIVE)
    );

    /** @var \Drupal\datastore\Service\Info\ImportInfo $import_info */
    $import_info = $this->container->get('dkan.datastore.import_info');

    // Gather the item info before localization.
    $import_info_item = $import_info->getItem(
      $source_resource->getIdentifier(),
      $source_resource->getVersion()
    );
    // Not done, and bytes are zero.
    $this->assertEquals(Result::WAITING, $import_info_item->fileFetcherStatus);
    // Fair to say there are no bytes yet since we haven't localized.
    $this->assertEquals(0, $import_info_item->fileFetcherBytes);

    // OK, let's localize it.
    /** @var \Drupal\datastore\Service\ResourceLocalizer $resource_localizer */
    $resource_localizer = $this->container->get('dkan.datastore.service.resource_localizer');
    // Try to localize.
    $this->assertInstanceOf(
      Result::class,
      $result = $resource_localizer->localizeTask($source_resource->getIdentifier(), NULL, FALSE)
    );
    $this->assertEquals(Result::DONE, $result->getStatus(), $result->getData());

    // What about our local perspective?
    $this->assertInstanceOf(
      DataResource::class,
      $local_resource = $resource_localizer->get(
        $source_resource->getIdentifier(),
        $source_resource->getVersion(),
        ResourceLocalizer::LOCAL_FILE_PERSPECTIVE
      )
    );
    // Does the localized file actually exist?
    $this->assertFileExists($local_resource->getFilePath());
    $this->assertNotEmpty(file_get_contents($local_resource->getFilePath()));

    // Get a file fetcher job from the resource localizer. Does it report the
    // correct file size?
    $ff = $resource_localizer->getFileFetcher($source_resource);
    $ff_state = $ff->getState();
    $this->assertEquals(
      filesize($local_resource->getFilePath()),
      $ff_state['total_bytes']
    );
    $this->assertEquals(
      $ff_state['total_bytes'],
      $ff_state['total_bytes_copied']
    );

    // Let's now examine the import job. It should be waiting, since we haven't
    // performed the import yet.
    /** @var \Drupal\datastore\DatastoreService $datastore_service */
    $datastore_service = $this->container->get('dkan.datastore.service');
    /** @var \Drupal\datastore\Service\ImportService $import_service */
    $import_service = $datastore_service->getImportService($local_resource);
    /** @var \Drupal\datastore\Plugin\QueueWorker\ImportJob $import_job */
    $import_job = $import_service->getImporter();
    $this->assertEquals(
      Result::WAITING,
      $import_job->getResult()->getStatus()
    );

    // ImportInfo::getItem() ultimately calls
    // ResourceLocalizer::getFileFetcher() just like we did above. Do its
    // results match?
    $import_info_item = $import_info->getItem(
      $source_resource->getIdentifier(),
      $source_resource->getVersion()
    );
    // Is it done?
    $this->assertEquals(Result::DONE, $import_info_item->fileFetcherStatus);
    // Do we report the correct number of bytes?
    $this->assertEquals(
      filesize($local_resource->getFilePath()),
      $import_info_item->fileFetcherBytes
    );
  }

}
