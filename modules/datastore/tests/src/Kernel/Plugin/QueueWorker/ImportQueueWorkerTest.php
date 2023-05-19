<?php

namespace Drupal\Tests\datastore\Kernel\Plugin\QueueWorker;

use Drupal\datastore\DatastoreService;
use Drupal\datastore\Plugin\QueueWorker\ImportQueueWorker;
use Drupal\KernelTests\KernelTestBase;
use Procrastinator\Result;

/**
 * @covers \Drupal\datastore\Plugin\QueueWorker\ImportQueueWorker
 * @coversDefaultClass \Drupal\datastore\Plugin\QueueWorker\ImportQueueWorker
 *
 * @group datastore
 * @group kernel
 */
class ImportQueueWorkerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'common',
    'datastore',
    'metastore',
  ];

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
      ])
      ->onlyMethods(['import'])
      ->getMock();
    $datastore_service->method('import')
      ->willReturn([$result]);

    // Add our mock to the container.
    $this->container->set('dkan.datastore.service', $datastore_service);

    // Make a partial mock of ImportQueueWorker so that we can tell the test
    // to expect never to call notice() and only to call error() once.
    $queue_worker = $this->createPartialMock(
      ImportQueueWorker::class,
      ['error', 'notice']
    );
    $queue_worker->expects($this->once())
      ->method('error');
    $queue_worker->expects($this->never())
      ->method('notice');
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

}
