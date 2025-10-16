<?php

namespace Drupal\Tests\datastore\Kernel\Service;

use Drupal\common\DataResource;
use Drupal\Core\File\FileSystemInterface;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\KernelTests\KernelTestBase;
use Procrastinator\Result;

/**
 * @covers \Drupal\common\FileFetcher\FileFetcherFactory
 * @covers \Drupal\datastore\Service\ResourceLocalizer
 * @coversDefaultClass \Drupal\datastore\Service\ResourceLocalizer
 *
 * @group dkan
 * @group datastore
 * @group kernel
 */
class ResourceLocalizerTest extends KernelTestBase {

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
    // All our tests need the resource_mapping entity.
    $this->installEntitySchema('resource_mapping');
  }

  public function testNoResourceFound() {
    $service = $this->container->get('dkan.datastore.service.resource_localizer');

    $resource = new DataResource(self::HOST . '/file.csv', 'text/csv');
    $this->assertNull($service->get($resource->getIdentifier(), $resource->getVersion()));
  }

  public function testLocalize() {
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

    // OK, let's localize it.
    /** @var \Drupal\datastore\Service\ResourceLocalizer $localizer */
    $localizer = $this->container->get('dkan.datastore.service.resource_localizer');
    // Try to localize.
    $this->assertInstanceOf(
      Result::class,
      $result = $localizer->localizeTask($source_resource->getIdentifier(), NULL, FALSE)
    );
    $this->assertEquals(Result::DONE, $result->getStatus(), $result->getError());

    // What about our local perspective?
    $this->assertInstanceOf(
      DataResource::class,
      $local_resource = $localizer->get(
        $source_resource->getIdentifier(),
        NULL,
        ResourceLocalizer::LOCAL_FILE_PERSPECTIVE
      )
    );
    $this->assertFileExists($local_resource->getFilePath());
    $this->assertNotEmpty(file_get_contents($local_resource->getFilePath()));
  }

  public static function provideUseExisting() {
    return [
      'Use existing localized file' => [TRUE],
      'Do not use existing localized file' => [FALSE],
    ];
  }

  /**
   * @dataProvider provideUseExisting
   *
   * @see \Drupal\Tests\common\Kernel\FileFetcher\FileFetcherFactoryTest::testOurRemote()
   */
  public function testLocalizeOverwriteExistingLocalFile($use_existing) {
    // Config for overwrite.
    $this->installConfig(['common']);
    $config = $this->config('common.settings');
    $config->set('always_use_existing_local_perspective', $use_existing);
    $config->save();

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

    // Set up a pre-existing localized file.
    $existing_path_uri = 'public://resources/' . $source_resource->getUniqueIdentifierNoPerspective() . '/';
    $existing_filename = 'district_centerpoints_small.csv';
    $existing_file_content = 'i,am,not,district,centerpoints,content';

    /** @var \Drupal\Core\File\FileSystem $fs */
    $fs = \Drupal::service('file_system');
    $fs->prepareDirectory($existing_path_uri, FileSystemInterface::CREATE_DIRECTORY);
    $existing_file_uri = $fs->createFilename($existing_filename, $existing_path_uri);
    file_put_contents($existing_file_uri, $existing_file_content);

    // Make sure it's not in the mapper as a localized resource yet...
    $this->assertNull(
      $mapper->get($source_resource->getIdentifier(), ResourceLocalizer::LOCAL_FILE_PERSPECTIVE)
    );

    // OK, let's localize it, without registering the local perspective in the
    // mapper.
    /** @var \Drupal\datastore\Service\ResourceLocalizer $localizer */
    $localizer = $this->container->get('dkan.datastore.service.resource_localizer');
    // Try to localize.
    $this->assertInstanceOf(
      Result::class,
      $result = $localizer->localizeTask($source_resource->getIdentifier())
    );
    $this->assertEquals(Result::DONE, $result->getStatus(), $result->getData());

    // What about our local perspective?
    $this->assertInstanceOf(
      DataResource::class,
      $localized_resource = $localizer->get(
        $source_resource->getIdentifier(),
        NULL,
        ResourceLocalizer::LOCAL_FILE_PERSPECTIVE
      )
    );
    $this->assertEquals($existing_file_uri, $localized_resource->getFilePath());
    $this->assertFileExists($localized_resource->getFilePath());
    if ($use_existing) {
      // This proves that the pre-existing file was NOT replaced by the localizer.
      $this->assertEquals(
        $existing_file_content,
        file_get_contents($localized_resource->getFilePath())
      );
    }
    else {
      // This proves that the pre-existing file WAS replaced by the localizer.
      $this->assertNotEquals(
        $existing_file_content,
        file_get_contents($localized_resource->getFilePath())
      );
    }
    // Is it in the mapper?
    $this->assertInstanceOf(
      DataResource::class,
      $localized_resource = $mapper->get($source_resource->getIdentifier(), ResourceLocalizer::LOCAL_FILE_PERSPECTIVE)
    );
    $this->assertEquals(
      ResourceLocalizer::LOCAL_FILE_PERSPECTIVE,
      $localized_resource->getPerspective()
    );
  }

}
