<?php

namespace Drupal\Tests\datastore\Kernel\Plugin\QueueWorker;

use Drupal\common\DataResource;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\datastore\Plugin\QueueWorker\LocalizeQueueWorker;
use Drupal\datastore\Service\ResourceLocalizer;
use Drupal\KernelTests\KernelTestBase;
use Procrastinator\Result;

/**
 * @covers \Drupal\datastore\Plugin\QueueWorker\LocalizeQueueWorker
 * @coversDefaultClass \Drupal\datastore\Plugin\QueueWorker\LocalizeQueueWorker
 *
 * @group datastore
 * @group kernel
 *
 * @todo Expand test to ensure that no file is localized on ERROR, after
 *   https://github.com/GetDKAN/file-fetcher/pull/36
 */
class LocalizeQueueWorkerTest extends KernelTestBase {

  protected const SOURCE_URL = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'common',
    'datastore',
    'metastore',
    'node',
  ];

  protected function setUp() : void {
    parent::setUp();
    $this->installEntitySchema('resource_mapping');
  }

  public static function provideUseExisting() {
    return [
      'Use existing localized file' => [TRUE],
      'Do not use existing localized file' => [FALSE],
    ];
  }

  /**
   * @dataProvider provideUseExisting
   */
  public function testLocalizing(bool $always_use_existing_local_perspective) {
    $existing_local_file_contents = 'pre-existing,file';
    // Config for overwrite.
    $this->installConfig(['common']);
    $config = $this->config('common.settings');
    $config->set(
      'always_use_existing_local_perspective',
      $always_use_existing_local_perspective
    );
    $config->save();

    // Create a dataset.
    $source_resource = new DataResource(
      self::SOURCE_URL,
      'text/csv',
      DataResource::DEFAULT_SOURCE_PERSPECTIVE
    );
    /** @var \Drupal\metastore\ResourceMapper $mapper */
    $mapper = $this->container->get('dkan.metastore.resource_mapper');
    // Make sure the resource isn't registered already.
    $this->assertNull($mapper->get($source_resource->getIdentifier()));
    $mapper->register($source_resource);
    $this->assertNotNull($mapper->get($source_resource->getIdentifier()));

    // Check the file system to be sure it's set the way it should be.
    /** @var \Drupal\datastore\Service\ResourceLocalizer $localizer */
    $localizer = $this->container->get('dkan.datastore.service.resource_localizer');
    // Compute the public file URI for the resource.
    $public_uri = $localizer->localizeFilePath($source_resource);

    // Set up the file to either be pre-existing or not, based on arguments.
    /** @var \Drupal\Core\File\FileSystem $fs */
    $fs = $this->container->get('file_system');

    if ($always_use_existing_local_perspective) {
      file_put_contents($fs->realpath($public_uri), $existing_local_file_contents);
      $this->assertFileExists($fs->realpath($public_uri));
    }
    else {
      $this->assertFileDoesNotExist($fs->realpath($public_uri));
      // Put the wrong contents into the file after the assertion, to ensure
      // it is replaced.
      file_put_contents($fs->realpath($public_uri), $existing_local_file_contents);
    }

    // Make a localize queue worker.
    $queue_worker = LocalizeQueueWorker::create(
      $this->container,
      [],
      'localize_import',
      ['cron' => ['lease_time' => 10]]
    );

    // Process the queue item with our dataset.
    $queue_worker->processItem([
      'identifier' => $source_resource->getIdentifier(),
      'version' => $source_resource->getVersion(),
    ]);

    // Ensure the file was localized.
    $this->assertFileExists($fs->realpath($public_uri));
    // Ensure the file was either replaced or not replaced.
    if ($always_use_existing_local_perspective) {
      $this->assertStringEqualsFile(
        $fs->realpath($public_uri),
        $existing_local_file_contents
      );
    }
    else {
      $this->assertStringNotEqualsFile(
        $fs->realpath($public_uri),
        $existing_local_file_contents
      );
    }
  }

  /**
   * @covers ::processItem
   */
  public function testLocalizerNotDone() {
    // Mock the localizer so it will not return DONE.
    $localizer = $this->getMockBuilder(ResourceLocalizer::class)
      ->setConstructorArgs([
        // These are the real services, we just want to mock localizeTask().
        $this->container->get('dkan.metastore.resource_mapper'),
        $this->container->get('dkan.common.file_fetcher'),
        $this->container->get('dkan.common.drupal_files'),
        $this->container->get('dkan.common.filefetcher_job_store_factory'),
        $this->container->get('queue'),
        $this->container->get('event_dispatcher')
      ])
      ->onlyMethods(['localizeTask'])
      ->getMock();
    $not_done_result = new Result();
    $not_done_result->setStatus(Result::IN_PROGRESS);
    $not_done_result->setError('error message');
    $localizer->method('localizeTask')
      ->willReturn($not_done_result);

    $this->container->set('dkan.datastore.service.resource_localizer', $localizer);

    $queue_worker = LocalizeQueueWorker::create($this->container, [], 'localize_import', []);

    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Localization of resource 12345: error message');
    $queue_worker->processItem(['identifier' => '12345', 'version' => 'version']);
  }

  /**
   * @covers ::processItem
   */
  public function testNotRequeuedOnError() {
    // The queue worker should log an error, and not throw an exception, if the
    // result of localization is an error.

    // Mock the localizer so it will return ERROR.
    $localizer = $this->getMockBuilder(ResourceLocalizer::class)
      ->setConstructorArgs([
        // These are the real services, we just want to mock localizeTask().
        $this->container->get('dkan.metastore.resource_mapper'),
        $this->container->get('dkan.common.file_fetcher'),
        $this->container->get('dkan.common.drupal_files'),
        $this->container->get('dkan.common.filefetcher_job_store_factory'),
        $this->container->get('queue'),
        $this->container->get('event_dispatcher')
      ])
      ->onlyMethods(['localizeTask'])
      ->getMock();
    $error_result = new Result();
    $error_result->setStatus(Result::ERROR);
    $error_result->setError('error message');
    $localizer->method('localizeTask')
      ->willReturn($error_result);

    // Mock the logger so it expects error() once.
    $logger = $this->getMockBuilder(LoggerChannelInterface::class)
      ->onlyMethods(['error'])
      ->getMockForAbstractClass();
    $logger->expects($this->once())
      ->method('error');

    // Logger factory gives us mocked logger.
    $logger_factory = $this->getMockBuilder(LoggerChannelFactoryInterface::class)
      ->onlyMethods(['get'])
      ->getMockForAbstractClass();
    $logger_factory->method('get')
      ->willReturn($logger);

    $this->container->set('logger.factory', $logger_factory);
    $this->container->set('dkan.datastore.service.resource_localizer', $localizer);

    $queue_worker = LocalizeQueueWorker::create($this->container, [], 'localize_import', []);
    $queue_worker->processItem(['identifier' => '12345', 'version' => 'version']);
  }

}
