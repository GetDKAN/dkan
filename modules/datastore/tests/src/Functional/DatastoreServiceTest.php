<?php

namespace Drupal\Tests\datastore\Functional;

use Drupal\common\DataResource;
use Drupal\Core\File\FileSystemInterface;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\Tests\BrowserTestBase;
use Procrastinator\Result;

/**
 * Functional tests for the datastore service.
 *
 * This test has to be a BTB test because the import services rely on MySQL, and
 * kernel tests use SQLite.
 *
 * @covers \Drupal\common\FileFetcher\FileFetcherFactory
 * @covers \Drupal\datastore\DatastoreService
 * @coversDefaultClass \Drupal\datastore\DatastoreService
 *
 * @group datastore
 * @group btb
 * @group functional
 * @group functional2
 */
class DatastoreServiceTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'common',
    'datastore',
    'metastore',
    'node',
  ];

  protected $defaultTheme = 'stark';

  protected const SOURCE_URL = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv';

  public static function provideUseExisting() {
    return [
      'Use existing localized file' => [TRUE],
      'Do not use existing localized file' => [FALSE],
    ];
  }

  /**
   * @dataProvider provideUseExisting
   *
   * @see \Drupal\Tests\datastore\Kernel\Service\ResourceLocalizerTest::testLocalizeOverwriteExistingLocalFile()
   */
  public function testLocalizeOverwriteExistingLocalFile($use_existing) {
    // Config for overwrite.
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
    $existing_filename = basename(self::SOURCE_URL);
    $existing_file_content = 'i,am,not,district,centerpoints,content';

    /** @var \Drupal\Core\File\FileSystem $fs */
    $fs = \Drupal::service('file_system');
    $fs->prepareDirectory(
      $existing_path_uri,
      FileSystemInterface::CREATE_DIRECTORY
    );
    $existing_file_uri = $fs->createFilename($existing_filename, $existing_path_uri);
    file_put_contents($existing_file_uri, $existing_file_content);

    // Make sure it's not in the mapper as a localized resource yet...
    $this->assertNull(
      $mapper->get($source_resource->getIdentifier(), ResourceLocalizer::LOCAL_FILE_PERSPECTIVE)
    );

    // Now we perform the import using DatastoreService, never deferred because
    // this is a test.
    /** @var \Drupal\datastore\DatastoreService $datastore_service */
    $datastore_service = $this->container->get('dkan.datastore.service');
    $response = $datastore_service->import($source_resource->getIdentifier(), FALSE);

    // Did we get all the responses we should have?
    // @todo Why must import() return an array of statuses?
    foreach (['ResourceLocalizer', 'ImportService'] as $key) {
      $item = $response[$key];
      $this->assertInstanceOf(Result::class, $item);
      $this->assertEquals(Result::DONE, $item->getStatus(), $key . ': ' . $item->getError());
    }

    // What about our local perspective?
    /** @var \Drupal\datastore\Service\ResourceLocalizer $localizer */
    $localizer = $this->container->get('dkan.datastore.service.resource_localizer');
    $this->assertInstanceOf(
      DataResource::class,
      $localized_resource = $localizer->get(
        $source_resource->getIdentifier(),
        NULL,
        ResourceLocalizer::LOCAL_FILE_PERSPECTIVE
      )
    );

    // Same path?
    $this->assertEquals($existing_file_uri, $localized_resource->getFilePath());
    $this->assertFileExists($localized_resource->getFilePath());

    // The file contents should be the same or different based on config.
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
