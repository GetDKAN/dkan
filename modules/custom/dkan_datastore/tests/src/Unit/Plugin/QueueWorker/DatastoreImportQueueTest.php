<?php

namespace Drupal\Tests\dkan_datastore\Unit\Plugin\QueueWorker;

use Dkan\Datastore\Manager;
use Dkan\Datastore\Resource;
use Drupal\dkan_datastore\Plugin\QueueWorker\DatastoreImportQueue;
use Drupal\dkan_datastore\Manager\Builder;
use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\Core\Queue\QueueWorkerBase;
use Dkan\Datastore\Manager\IManager;
use Drupal\Core\Queue\SuspendQueueException;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\File\FileSystem;
use Psr\Log\LoggerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\Queue\QueueInterface;

/**
 * @coversDefaultClass Drupal\dkan_datastore\Plugin\QueueWorker\DatastoreImportQueue
 * @group dkan_datastore
 */
class DatastoreImportQueueTest extends DkanTestBase {

  public function dataProcessItem() {
    return [
      [IManager::DATA_IMPORT_IN_PROGRESS],
      [IManager::DATA_IMPORT_PAUSED],
    ];
  }

  /**
   * Tests ProcessItem() for successful operation.
   * @dataProvider dataProcessItem
   */
  public function testProcessItem($status) {
    // setup
    $mock = $this->getMockBuilder(DatastoreImportQueue::class)
      ->setMethods([
        'sanitizeData',
        'getManager',
        'import',
        'refreshQueueState',
        'requeue',
        'log',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $mockManager = $this->getMockBuilder(IManager::class)
      ->setMethods(['import'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    // barebones dummy.
    $data          = [
      'uuid'            => uniqid('uuid'),
      'resource_id'     => uniqid('resource_id'),
      'file_path'       => uniqid('file_path'),
      'import_config'   => [uniqid('import_config')],
      'queue_iteration' => uniqid('queue_iteration'),
    ];
    $dataSanitized = array_merge($data, ['sanitized' => 1]);
    $dataRefreshed = array_merge($data, ['refreshed' => 1]);

    $newQueueItemId = 42;

    // expect
    $mock->expects($this->once())
      ->method('sanitizeData')
      ->with($data)
      ->willReturn($dataSanitized);

    $mock->expects($this->once())
      ->method('getManager')
      ->with($dataSanitized['resource_id'], $dataSanitized['file_path'], $dataSanitized['import_config'])
      ->willReturn($mockManager);

    $mockManager->expects($this->once())
      ->method('import')
      ->willReturn($status);

    $mock->expects($this->once())
      ->method('refreshQueueState')
      ->with($dataSanitized, $mockManager)
      ->willReturn($dataRefreshed);

    $mock->expects($this->once())
      ->method('requeue')
      ->with($dataRefreshed)
      ->willReturn($newQueueItemId);

    $mock->expects($this->once())
      ->method('log')
      ->with(RfcLogLevel::INFO, "Import for {$data['uuid']} is requeueing for iteration No. {$data['queue_iteration']}. (ID:{$newQueueItemId}).");

    // assert
    $mock->processItem($data);
  }

  /**
   * Tests ProcessItem() on import error.
   * @dataProvider dataProcessItem
   */
  public function testProcessItemError() {
    // setup
    $mock = $this->getMockBuilder(DatastoreImportQueue::class)
      ->setMethods([
        'sanitizeData',
        'getManager',
        'import',
        'refreshQueueState',
        'requeue',
        'log',
        'cleanup',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $mockManager = $this->getMockBuilder(IManager::class)
      ->setMethods(['import'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    // barebones dummy.
    $data          = [
      'uuid'          => uniqid('uuid'),
      'resource_id'   => uniqid('resource_id'),
      'file_path'     => uniqid('file_path'),
      'import_config' => [uniqid('import_config')],
    ];
    $dataSanitized = array_merge($data, ['sanitized' => 1]);


    // expect
    $mock->expects($this->once())
      ->method('sanitizeData')
      ->with($data)
      ->willReturn($dataSanitized);

    $mock->expects($this->once())
      ->method('getManager')
      ->with($dataSanitized['resource_id'], $dataSanitized['file_path'], $dataSanitized['import_config'])
      ->willReturn($mockManager);

    $mockManager->expects($this->once())
      ->method('import')
      ->willReturn(IManager::DATA_IMPORT_ERROR);

    $mock->expects($this->never())
      ->method('refreshQueueState');

    $mock->expects($this->never())
      ->method('requeue');

    $mock->expects($this->exactly(2))
      ->method('log')
      ->withConsecutive(
        [RfcLogLevel::ERROR, "Import for {$data['uuid']} returned an error."],
        [RfcLogLevel::INFO, "Import for {$data['uuid']} complete/stopped."]
    );

    $mock->expects($this->once())
      ->method('cleanup')
      ->willReturn($dataSanitized);

    // assert
    $mock->processItem($data);
  }

  /**
   * Tests ProcessItem() for when import done.
   * @dataProvider dataProcessItem
   */
  public function testProcessItemDone() {
    // setup
    $mock = $this->getMockBuilder(DatastoreImportQueue::class)
      ->setMethods([
        'sanitizeData',
        'getManager',
        'import',
        'refreshQueueState',
        'requeue',
        'log',
        'cleanup',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $mockManager = $this->getMockBuilder(IManager::class)
      ->setMethods(['import'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    // barebones dummy.
    $data          = [
      'uuid'          => uniqid('uuid'),
      'resource_id'   => uniqid('resource_id'),
      'file_path'     => uniqid('file_path'),
      'import_config' => [uniqid('import_config')],
    ];
    $dataSanitized = array_merge($data, ['sanitized' => 1]);


    // expect
    $mock->expects($this->once())
      ->method('sanitizeData')
      ->with($data)
      ->willReturn($dataSanitized);

    $mock->expects($this->once())
      ->method('getManager')
      ->with($dataSanitized['resource_id'], $dataSanitized['file_path'], $dataSanitized['import_config'])
      ->willReturn($mockManager);

    $mockManager->expects($this->once())
      ->method('import')
      ->willReturn(IManager::DATA_IMPORT_DONE);

    $mock->expects($this->never())
      ->method('refreshQueueState');

    $mock->expects($this->never())
      ->method('requeue');

    $mock->expects($this->once())
      ->method('log')
      ->with(RfcLogLevel::INFO, "Import for {$data['uuid']} complete/stopped.");

    $mock->expects($this->once())
      ->method('cleanup')
      ->willReturn($dataSanitized);

    // assert
    $mock->processItem($data);
  }

  /**
   * Data provider for testSanitizeData.
   * 
   * @return array
   *   Array of arguments.
   */
  public function dataSanitizeData() {

    $requiredFields = [
      'uuid'        => uniqid('uuid'),
      'resource_id' => uniqid('resource_id'),
      'file_path'   => uniqid('file_path'),
    ];

    return [
      // if missing optional fields.
      [
        $requiredFields,
        array_merge($requiredFields, [
          'import_config'     => [],
          'file_is_temporary' => False,
          'queue_iteration'   => 0,
          'rows_done'         => 0,
          'import_fail_count' => 0,
        ])
      ],
      // with all optional fields, should be identical
      [
        array_merge($requiredFields, [
          'import_config'     => ['fooo-bar'],
          'file_is_temporary' => true,
          'queue_iteration'   => 42,
          'rows_done'         => 43,
          'import_fail_count' => 44,
        ]),
        array_merge($requiredFields, [
          'import_config'     => ['fooo-bar'],
          'file_is_temporary' => true,
          'queue_iteration'   => 42,
          'rows_done'         => 43,
          'import_fail_count' => 44,
        ]),
      ],
    ];
  }

  /**
   * Tests SanitizeData().
   *
   * @dataProvider dataSanitizeData
   */
  public function testSanitizeData(array $data, array $expected) {
    // setup
    $mock = $this->getMockBuilder(DatastoreImportQueue::class)
      ->setMethods(null)
      ->disableOriginalConstructor()
      ->getMock();

    // assert
    $actual = $this->invokeProtectedMethod($mock, 'sanitizeData', $data);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests SanitizeData() on exception.
   */
  public function testSanitizeDataOnException() {
    // setup
    $mock = $this->getMockBuilder(DatastoreImportQueue::class)
      ->setMethods(null)
      ->disableOriginalConstructor()
      ->getMock();

    $data = [];

    // expect
    $this->setExpectedException(SuspendQueueException::class, 'Queue input data is invalid. Missing required `uuid` or `resource_id`, `file_path`');

    // assert
    $this->invokeProtectedMethod($mock, 'sanitizeData', $data);
  }

  /**
   * Tests RefreshQueueState() if stalling.
   */
  public function testRefreshQueueStateIfStalling() {
    // setup

    $mock = $this->getMockBuilder(DatastoreImportQueue::class)
      ->setMethods(['log'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockManager = $this->getMockBuilder(IManager::class)
      ->setMethods(['numberOfRecordsImported'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $rowsDone                = 42;
    $newRowsDone             = 42;
    //must be less than stall limit
    $importFailCount         = 0;
    $queueIteration          = 7;
    $expectedImportFailCount = $importFailCount + 1;
    $expectedQueueInteration = $queueIteration + 1;

    $data = [
      'uuid'              => uniqid('uuid'),
      'rows_done'         => $rowsDone,
      'import_fail_count' => $importFailCount,
      'queue_iteration'   => $queueIteration,
    ];

    $expected = [
      'uuid'              => $data['uuid'],
      'rows_done'         => $newRowsDone,
      'import_fail_count' => $expectedImportFailCount,
      'queue_iteration'   => $expectedQueueInteration,
    ];

    // expect
    $mockManager->expects($this->once())
      ->method('numberOfRecordsImported')
      ->willReturn($newRowsDone);

    $mock->expects($this->once())
      ->method('log')
      ->with(RfcLogLevel::WARNING, "Import for {$data['uuid']} seemd to be lagging behind {$expectedImportFailCount} times. Rows done:{$rowsDone} vs {$newRowsDone}");

    // assert
    $actual = $this->invokeProtectedMethod($mock, 'refreshQueueState', $data, $mockManager);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests RefreshQueueState() if stalled.
   */
  public function testRefreshQueueStateIfStalled() {
    // setup

    $mock = $this->getMockBuilder(DatastoreImportQueue::class)
      ->setMethods(['log'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockManager = $this->getMockBuilder(IManager::class)
      ->setMethods(['numberOfRecordsImported'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $rowsDone        = 0;
    $newRowsDone     = 40;
    //must be less than stall limit
    $importFailCount = DatastoreImportQueue::STALL_LIMIT + 1;

    $data = [
      'uuid'              => uniqid('uuid'),
      'rows_done'         => $rowsDone,
      'import_fail_count' => $importFailCount,
      'queue_iteration'   => 0,
      'file_path'         => '/foo/bar',
    ];

    // expect
    $mockManager->expects($this->once())
      ->method('numberOfRecordsImported')
      ->willReturn($newRowsDone);

    $mock->expects($this->once())
      ->method('log')
      ->with(RfcLogLevel::ERROR, "Import for {$data['uuid']} lagged for {$importFailCount} times. Suspending.");

    $this->setExpectedException(SuspendQueueException::class, "Import for {$data['uuid']}[{$data['file_path']}] appears to have stalled past allowed limits.");

    // assert
    $this->invokeProtectedMethod($mock, 'refreshQueueState', $data, $mockManager);
  }

  /**
   * Tests Cleanup().
   */
  public function testCleanup() {
    // setup
    $mock = $this->getMockBuilder(DatastoreImportQueue::class)
      ->setMethods(null)
      ->disableOriginalConstructor()
      ->getMock();

    $mockFileSystem = $this->getMockBuilder(FileSystem::class)
      ->setMethods(['unlink'])
      ->disableOriginalConstructor()
      ->getMock();

    $this->setActualContainer([
      'file_system' => $mockFileSystem,
    ]);

    $data = [
      'file_is_temporary' => true,
      'file_path'         => uniqid('/path/to/thing'),
    ];

    // expect
    $mockFileSystem->expects($this->once())
      ->method('unlink')
      ->with($data['file_path']);

    // assert
    $this->invokeProtectedMethod($mock, 'cleanup', $data);
  }

  /**
   * Tests Cleanup() with nothing to do.
   */
  public function testCleanupNothingToDo() {
    // setup
    $mock = $this->getMockBuilder(DatastoreImportQueue::class)
      ->setMethods(null)
      ->disableOriginalConstructor()
      ->getMock();

    $mockFileSystem = $this->getMockBuilder(FileSystem::class)
      ->setMethods(['unlink'])
      ->disableOriginalConstructor()
      ->getMock();

    $this->setActualContainer([
      'file_system' => $mockFileSystem,
    ]);

    $data = [
      'file_is_temporary' => false,
      'file_path'         => uniqid('/path/to/thing'),
    ];

    // expect
    $mockFileSystem->expects($this->never())
      ->method('unlink');

    // assert
    $this->invokeProtectedMethod($mock, 'cleanup', $data);
  }

  /**
   * Tests GetManager().
   */
  public function testGetManager() {
    // setup
    $mock = $this->getMockBuilder(DatastoreImportQueue::class)
      ->setMethods([
        'sanitizeImportConfig'
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $mockManagerBuilder = $this->getMockBuilder(Builder::class)
      ->setMethods([
        'setResource',
        'build',
      ])
      ->disableOriginalConstructor()
      ->getMock();
    $this->setActualContainer([
      'dkan_datastore.manager.builder' => $mockManagerBuilder,
    ]);

    $mockManager = $this->getMockBuilder(Manager::class)
      ->setMethods([
        'setConfigurableProperties',
        'setImportTimelimit',
      ])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $resourceId            = 42;
    $filePath              = uniqid('/path/to/foo');
    $importConfig          = ['foo'];
    $sanitizedImportConfig = ['foo-sanitised'];

    // expect
    $mockManagerBuilder->expects($this->once())
      ->method('setResource')
      ->with(new Resource($resourceId, $filePath))
      ->willReturnSelf();

    $mockManagerBuilder->expects($this->once())
      ->method('build')
      ->willReturn($mockManager);

    $mockManager->expects($this->once())
      ->method('setConfigurableProperties')
      ->with($sanitizedImportConfig);

    $mock->expects($this->once())
      ->method('sanitizeImportConfig')
      ->with($importConfig)
      ->willReturn($sanitizedImportConfig);

    $mockManager->expects($this->once())
      ->method('setImportTimelimit')
      ->with(55);

    // assert
    $actual = $this->invokeProtectedMethod($mock, 'getManager', $resourceId, $filePath, $importConfig);
    $this->assertSame($mockManager, $actual);
  }

  /**
   * Data provider for testSanitizeImportConfig.
   *
   * @return array
   *   Array of arguments.
   */
  public function dataSanitizeImportConfig() {
    return [
      [
        [],
        [
          'delimiter' => ",",
          'quote'     => '"',
          'escape'    => "\\",
        ],
      ],
      [
        [
          'delimiter' => "foo",
          'escape'    => "bar",],
        [
          'delimiter' => "foo",
          'quote'     => '"',
          'escape'    => "bar",
        ],
      ],
    ];
  }

  /**
   * Tests SanitizeImportConfig().
   *
   * @dataProvider dataSanitizeImportConfig
   */
  public function testSanitizeImportConfig(array $importConfig, array $expected) {
    // setup
    $mock = $this->getMockBuilder(DatastoreImportQueue::class)
      ->setMethods(null)
      ->disableOriginalConstructor()
      ->getMock();

    // assert
    $actual = $this->invokeProtectedMethod($mock, 'sanitizeImportConfig', $importConfig);
    $this->assertEquals($expected, $actual);
  }

  /**
   * Tests Log().
   */
  public function testLog() {
    // setup
    $mock = $this->getMockBuilder(DatastoreImportQueue::class)
      ->setMethods([
        'getLogger',
        'getPluginId',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $mockLogger = $this->getMockBuilder(LoggerInterface::class)
      ->setMethods(['log'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $pluginId = uniqid('foo');
    $level    = 1;
    $message  = uniqid('message');
    $context  = [];

    // expect
    $mock->expects($this->once())
      ->method('getPluginId')
      ->willReturn($pluginId);

    $mock->expects($this->once())
      ->method('getLogger')
      ->with($pluginId)
      ->willReturn($mockLogger);

    $mockLogger->expects($this->once())
      ->method('log')
      ->with($level, $message, $context);

    // assert
    $this->invokeProtectedMethod($mock, 'log', $level, $message, $context);
  }

  /**
   * Tests Requeue().
   */
  public function testRequeue() {
    // setup
    $mock = $this->getMockBuilder(DatastoreImportQueue::class)
      ->setMethods(['getPluginId'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockQueueFactory = $this->getMockBuilder(QueueFactory::class)
      ->setMethods([
        'get',
      ])
      ->disableOriginalConstructor()
      ->getMock();
    $this->setActualContainer([
      'queue' => $mockQueueFactory,
    ]);

    $mockQueue = $this->getMockBuilder(QueueInterface::class)
      ->setMethods(['createItem'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $pluginId = uniqid('foo');
    $data     = ['foo'];
    $expected = uniqid('queueid');

    // expect
    $mock->expects($this->once())
      ->method('getPluginId')
      ->willReturn($pluginId);

    $mockQueueFactory->expects($this->once())
      ->method('get')
      ->with($pluginId)
      ->willReturn($mockQueue);

    $mockQueue->expects($this->once())
      ->method('createItem')
      ->with($data)
      ->willReturn($expected);

    // assert
    $actual = $this->invokeProtectedMethod($mock, 'requeue',$data);
    $this->assertEquals($expected, $actual);
  }

}
