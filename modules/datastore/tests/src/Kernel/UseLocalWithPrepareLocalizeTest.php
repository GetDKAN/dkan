<?php

namespace Drupal\Tests\datastore\Kernel;

use Drupal\common\DataResource;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test dataset import when using existing localized files.
 *
 * @group datastore
 * @group btb
 * @group kernel
 *
 * @see \Drupal\Tests\datastore\Functional\UseLocalWithPrepareLocalizeTest
 */
class UseLocalWithPrepareLocalizeTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'user',
    'common',
    'datastore',
    'metastore',
  ];

  protected $defaultTheme = 'stark';

  protected const SOURCE_URL = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv';

  protected function setUp() : void {
    parent::setUp();
    $this->installEntitySchema('resource_mapping');
  }

  public function test() {
    $this->installConfig(['common']);

    // Create dataset.
    $source_resource = new DataResource(
      self::SOURCE_URL,
      'text/csv',
      DataResource::DEFAULT_SOURCE_PERSPECTIVE
    );
    // Add our source data resource to the mapper.
    /** @var \Drupal\metastore\ResourceMapper $mapper */
    $mapper = $this->container->get('dkan.metastore.resource_mapper');
    $mapper->register($source_resource);
    $this->assertInstanceOf(
      DataResource::class,
      $source_resource = $mapper->get($source_resource->getIdentifier())
    );

    // Set always_use_existing_local_perspective to true.
    $this->config('common.settings')
      ->set('always_use_existing_local_perspective', TRUE)
      ->save();
    $this->assertTrue(
      $this->config('common.settings')->get('always_use_existing_local_perspective')
    );

    // Run prepare-localized, emulating the Drush command.
    $identifier = $source_resource->getIdentifier();
    /** @var \Drupal\datastore\Service\ResourceLocalizer $resource_localizer */
    $resource_localizer = $this->container->get('dkan.datastore.service.resource_localizer');
    $info = $resource_localizer->prepareLocalized($identifier);
    $this->assertArrayHasKey('file', $info);

    // 'Pre-download' the file. We'll just write an arbitrary file here.
    $preexisting_file_contents = '1234,5678';
    file_put_contents($info['file'], $preexisting_file_contents);
    $this->assertStringEqualsFile($info['file'], $preexisting_file_contents);

    // Run localize_import queue item.
    /** @var \Drupal\Core\Queue\QueueWorkerManager $queue_worker_manager */
    $queue_worker_manager = \Drupal::service('plugin.manager.queue_worker');
    $worker = $queue_worker_manager->createInstance('localize_import');
    $worker->processItem([
      'identifier' => $source_resource->getIdentifier(),
      'version' => $source_resource->getVersion(),
    ]);

    // Assert that the file wasn't changed.
    $this->assertStringEqualsFile($info['file'], $preexisting_file_contents);

    // Get the resource again.
    /** @var \Drupal\metastore\ResourceMapper $resource_mapper */
    $resource_mapper = $this->container->get('dkan.metastore.resource_mapper');
    $localized_resource = $resource_mapper->get(
      $source_resource->getIdentifier(),
      ResourceLocalizer::LOCAL_FILE_PERSPECTIVE,
      $source_resource->getVersion()
    );
    // Is the final file the same as the derived one from prepare-localize?
    /** @var \Drupal\Core\File\FileSystem $file_system */
    $file_system = $this->container->get('file_system');
    $localize_file = $file_system->realpath($localized_resource->getFilePath());
    $this->assertEquals($info['file'], $localize_file);
    $this->assertStringEqualsFile($localize_file, $preexisting_file_contents);
  }

}
