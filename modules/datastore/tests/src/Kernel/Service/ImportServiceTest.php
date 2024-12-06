<?php

namespace Drupal\Tests\datastore\Kernel\Service;

use Drupal\common\DataResource;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\datastore\Plugin\QueueWorker\ImportJob;
use Drupal\datastore\Service\ImportService;
use Drupal\KernelTests\KernelTestBase;
use Procrastinator\Result;
use Symfony\Component\ErrorHandler\BufferingLogger;

/**
 * @coversDefaultClass \Drupal\datastore\Service\ImportService
 * @covers \Drupal\datastore\Service\ImportService
 *
 * @group dkan
 * @group datastore
 * @group kernel
 */
class ImportServiceTest extends KernelTestBase {

  protected $strictConfigSchema = FALSE;

  protected static $modules = [
    'common',
    'datastore',
    'metastore',
    'node',
    'user',
    'field',
    'text',
    'system',
  ];

  /**
   * Test happy import path for result and data dictionary queue.
   *
   * @covers ::import
   */
  public function testImport() {
    $this->installEntitySchema('node');
    $this->installConfig(['node']);
    $this->installConfig(['metastore']);
    // Mock some services and statuses.
    $result = new Result();
    $result->setStatus(Result::DONE);

    $import_job = $this->getMockBuilder(ImportJob::class)
      ->onlyMethods(['run', 'getResult'])
      ->disableOriginalConstructor()
      ->getMock();
    $import_job->method('run')
      ->willReturn($result);
    $import_job->method('getResult')
      ->willReturn($result);

    /** @var \Drupal\datastore\Service\ImportService $import_service */
    $import_service = $this->getMockBuilder(ImportService::class)
      ->onlyMethods(['getImporter'])
      ->setConstructorArgs([
        new DataResource('abc.txt', 'text/csv'),
        $this->container->get('dkan.datastore.import_job_store_factory'),
        $this->container->get('dkan.datastore.database_table_factory'),
        $this->container->get('dkan.datastore.logger_channel'),
        $this->container->get('event_dispatcher'),
        $this->container->get('dkan.metastore.reference_lookup'), 
      ])
      ->getMock();
    $import_service->method('getImporter')
      ->willReturn($import_job);

    // There should be zero queue items to start with.
    /** @var \Drupal\Core\Queue\QueueInterface $queue */
    $queue = $this->container->get('queue')->get('post_import');
    $this->assertEquals(0, $queue->numberOfItems());

    // Perform the import.
    $import_service->import();

    $this->assertEquals(
      Result::DONE,
      $import_service->getImporter()->getResult()->getStatus()
    );

    // Did we add a queue item?
    $this->assertEquals(1, $queue->numberOfItems());
  }

  /**
   * @covers ::import
   */
  public function testLogImportError() {
    // Tell the logger channel factory to use a buffering logger.
    $logger = new BufferingLogger();
    $logger_factory = $this->createMock(LoggerChannelFactory::class);
    $logger_factory->expects($this->exactly(2))
      ->method('get')
      ->with(
        $this->logicalOr(
          $this->equalTo('datastore'),
          $this->equalTo('dkan')
        )
      )
      ->willReturn($logger);
    $this->container->set('logger.factory', $logger_factory);

    // Get an import service.
    /** @var \Drupal\datastore\Service\Factory\ImportServiceFactory $import_service_factory */
    $import_service_factory = $this->container->get('dkan.datastore.service.factory.import');
    $import_service = $import_service_factory->getInstance('id', [
      'resource' => new DataResource('abc.txt', 'text/csv'),
    ]);

    $import_service->import();

    // Ensure the log entry was created.
    $this->assertEquals(
      'Error importing resource id:%id path:%path message:%message',
      $logger->cleanLogs()[0][1]
    );
  }

}
