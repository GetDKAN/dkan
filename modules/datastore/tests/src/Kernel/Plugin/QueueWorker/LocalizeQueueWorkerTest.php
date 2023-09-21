<?php

namespace Drupal\Tests\datastore\Kernel\Plugin\QueueWorker;

use Drupal\common\DataResource;
use Drupal\common\Storage\ImportedItemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\datastore\DatastoreService;
use Drupal\datastore\Plugin\QueueWorker\ImportQueueWorker;
use Drupal\datastore\Plugin\QueueWorker\LocalizeQueueWorker;
use Drupal\KernelTests\KernelTestBase;
use Procrastinator\Result;

/**
 * @covers \Drupal\datastore\Plugin\QueueWorker\LocalizeQueueWorker
 * @coversDefaultClass \Drupal\datastore\Plugin\QueueWorker\LocalizeQueueWorker
 *
 * @group datastore
 * @group kernel
 */
class LocalizeQueueWorkerTest extends KernelTestBase {

  const SOURCE_URL = 'https://dkan-default-content-files.s3.amazonaws.com/phpunit/district_centerpoints_small.csv';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'common',
    'datastore',
    'metastore',
  ];

  public function testLocalizing() {
    // Config for overwrite.
    $this->installConfig(['common']);
    $config = $this->config('common.settings');
    $config->set('always_use_existing_local_perspective', FALSE);
    $config->save();

    // Create a dataset.
    $source_resource = new DataResource(
      self::SOURCE_URL,
      'text/csv',
      DataResource::DEFAULT_SOURCE_PERSPECTIVE
    );
    /** @var \Drupal\metastore\ResourceMapper $mapper */
    $mapper = $this->container->get('dkan.metastore.resource_mapper');
    $mapper->register($source_resource);

    // Check the file system to be sure it's set the way it should be.
    /** @var \Drupal\datastore\Service\ResourceLocalizer $localizer */
    $localizer = $this->container->get('dkan.datastore.service.resource_localizer');
    $public_uri = $localizer->localizeFilePath($source_resource);
    // Assert file does not exist.

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
  }

  public function testErrorPath() {
    // The result we'll mock to come from the datastore service.
    $result = new Result();
    $result->setStatus(Result::ERROR);
    $result->setError('Oops');

    // Mock the datastore service. All the services are real, we only want to
    // mock import().
    $datastore_service = $this->getMockBuilder(DatastoreService::class)
      ->setConstructorArgs([
        $this->container->get('dkan.datastore.service.resource_localizer'),
        $this->container->get('dkan.datastore.service.factory.import'),
        $this->container->get('queue'),
        $this->container->get('dkan.common.job_store'),
        $this->container->get('dkan.datastore.import_info_list'),
        $this->container->get('dkan.datastore.service.resource_processor.dictionary_enforcer'),
        $this->container->get('dkan.metastore.resource_mapper'),
      ])
      ->onlyMethods(['import'])
      ->getMock();
    $datastore_service->method('import')
      ->willReturn([$result]);

    // Add our mock to the container.
    $this->container->set('dkan.datastore.service', $datastore_service);

    // Mock the logger so we can tell when the error occurs.
    $logger = $this->getMockForAbstractClass(LoggerChannelInterface::class);
    // We expect an error to be logged.
    $logger->expects($this->once())
      ->method('error');
    // We don't expect a notice to be logged.
    $logger->expects($this->never())
      ->method('notice');
    $logger_factory = $this->getMockForAbstractClass(LoggerChannelFactoryInterface::class);
    $logger_factory->method('get')
      ->willReturn($logger);
    // Add our log factory mock to the container.
    $this->container->set('logger.factory', $logger_factory);

    $queue_worker = ImportQueueWorker::create(
      $this->container,
      [],
      'id',
      ['cron' => ['lease_time' => 10]]
    );
    // Some random data to process.
    $data = ['data' => ['identifier' => '12345', 'version' => '23456']];
    $queue_worker->processItem((object) $data);
  }

  public function testRequeue() {
    // The result we'll mock to come from the datastore service.
    $result = new Result();
    $result->setStatus(Result::STOPPED);

    // Mock the datastore service. All the services are real, we only want to
    // mock import().
    $datastore_service = $this->getMockBuilder(DatastoreService::class)
      ->setConstructorArgs([
        $this->container->get('dkan.datastore.service.resource_localizer'),
        $this->container->get('dkan.datastore.service.factory.import'),
        $this->container->get('queue'),
        $this->container->get('dkan.common.job_store'),
        $this->container->get('dkan.datastore.import_info_list'),
        $this->container->get('dkan.datastore.service.resource_processor.dictionary_enforcer'),
        $this->container->get('dkan.metastore.resource_mapper'),
      ])
      ->onlyMethods(['import'])
      ->getMock();
    $datastore_service->method('import')
      ->willReturn([$result]);

    // Add our mock to the container.
    $this->container->set('dkan.datastore.service', $datastore_service);

    // Mock the logger so we can tell when the error occurs.
    $logger = $this->getMockForAbstractClass(LoggerChannelInterface::class);
    // We don't expect an error to be logged.
    $logger->expects($this->never())
      ->method('error');
    // We expect a notice to be logged.
    $logger->expects($this->once())
      ->method('notice');
    $logger_factory = $this->getMockForAbstractClass(LoggerChannelFactoryInterface::class);
    $logger_factory->method('get')
      ->willReturn($logger);
    // Add our log factory mock to the container.
    $this->container->set('logger.factory', $logger_factory);

    $queue_worker = ImportQueueWorker::create(
      $this->container,
      [],
      'id',
      ['cron' => ['lease_time' => 10]]
    );
    // Some random data to process.
    $data = ['data' => ['identifier' => '12345', 'version' => '23456']];
    $queue_worker->processItem((object) $data);
  }

  /**
   * @covers ::processItem
   */
  public function testProcessItemAlreadyImported() {
    $queue_worker = $this->createPartialMock(
      ImportQueueWorker::class,
      ['alreadyImported', 'importData']
    );
    // Always already imported.
    $queue_worker->method('alreadyImported')
      ->willReturn(TRUE);
    // If it's already imported, then code flow should never hit importData().
    $queue_worker->expects($this->once())
      ->method('alreadyImported');
    $queue_worker->expects($this->never())
      ->method('importData');

    // Set the state of the mock via constructor.
    $queue_worker->__construct(
      [],
      'id',
      ['cron' => ['lease_time' => 10]],
      $this->container->get('config.factory'),
      $this->container->get('dkan.datastore.service'),
      $this->container->get('logger.factory'),
      $this->container->get('dkan.metastore.reference_lookup'),
      $this->container->get('dkan.common.database_connection_factory'),
      $this->container->get('dkan.datastore.database_connection_factory')
    );

    // Some random data to process.
    $data = ['data' => ['identifier' => '12345', 'version' => '23456']];
    $queue_worker->processItem((object) $data);
  }

  /**
   * @covers ::processItem
   */
  public function testProcessItemImportException() {
    // Mock the logger so we can tell when the error occurs.
    $logger = $this->getMockForAbstractClass(LoggerChannelInterface::class);
    // We expect an error to be logged, and we set an expectation for the message.
    $logger->expects($this->once())
      ->method('error')
      ->with('Import for 12345 returned an error: ' . __METHOD__);
    // We don't expect a notice to be logged.
    $logger->expects($this->never())
      ->method('notice');
    $logger_factory = $this->getMockForAbstractClass(LoggerChannelFactoryInterface::class);
    $logger_factory->method('get')
      ->willReturn($logger);
    // Add our log factory mock to the container.
    $this->container->set('logger.factory', $logger_factory);

    $queue_worker = $this->createPartialMock(
      ImportQueueWorker::class,
      ['importData']
    );
    // Explosion on importData().
    $queue_worker->method('importData')
      ->willThrowException(new \Exception(__METHOD__));

    // Set the state of the mock via constructor.
    $queue_worker->__construct(
      [],
      'id',
      ['cron' => ['lease_time' => 10]],
      $this->container->get('config.factory'),
      $this->container->get('dkan.datastore.service'),
      $this->container->get('logger.factory'),
      $this->container->get('dkan.metastore.reference_lookup'),
      $this->container->get('dkan.common.database_connection_factory'),
      $this->container->get('dkan.datastore.database_connection_factory')
    );

    // Some random data to process.
    $data = ['data' => ['identifier' => '12345', 'version' => '23456']];
    $queue_worker->processItem((object) $data);
  }

  /**
   * @covers ::alreadyImported
   */
  public function testAlreadyImported() {
    // Storage of the type that can know whether the import has happened already.
    $storage = $this->getMockBuilder(ImportedItemInterface::class)
      ->onlyMethods(['hasBeenImported'])
      ->getMockForAbstractClass();
    // Say the import has happened already.
    $storage->method('hasBeenImported')
      ->willReturn(TRUE);
    // Ensure that this method is called during the test.
    $storage->expects($this->once())
      ->method('hasBeenImported');

    // Datastore factory for the storage we mocked above.
    $datastore_service = $this->getMockBuilder(DatastoreService::class)
      // These are real services, so that we can put all the pieces in place.
      ->setConstructorArgs([
        $this->container->get('dkan.datastore.service.resource_localizer'),
        $this->container->get('dkan.datastore.service.factory.import'),
        $this->container->get('queue'),
        $this->container->get('dkan.common.job_store'),
        $this->container->get('dkan.datastore.import_info_list'),
        $this->container->get('dkan.datastore.service.resource_processor.dictionary_enforcer'),
        $this->container->get('dkan.metastore.resource_mapper'),
      ])
      ->onlyMethods(['getStorage'])
      ->getMock();
    $datastore_service->method('getStorage')
      ->willReturn($storage);
    $this->container->set('dkan.datastore.service', $datastore_service);

    $queue_worker = ImportQueueWorker::create(
      $this->container,
      [],
      'id',
      ['cron' => ['lease_time' => 10]]
    );

    // Some random data to process.
    $data = ['data' => ['identifier' => '12345', 'version' => '23456']];

    $ref_already_imported = new \ReflectionMethod($queue_worker, 'alreadyImported');
    $ref_already_imported->setAccessible(TRUE);
    $this->assertTrue($ref_already_imported->invokeArgs($queue_worker, [$data]));
  }

}
