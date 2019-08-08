<?php

namespace Drupal\Tests\dkan_datastore\Unit\Manager;

use Drupal\dkan_common\Tests\DkanTestBase;
use Drupal\dkan_datastore\Service\Datastore;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dkan_datastore\Manager\Helper as DatastoreHelper;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\node\NodeInterface;
use Dkan\Datastore\Resource;
use Drupal\dkan_datastore\DeferredImportQueuer;
use Dkan\Datastore\Manager;
use Drupal\dkan_datastore\Manager\Builder;

/**
 * @coversDefaultClass Drupal\dkan_datastore\Service\Datastore
 * @group              dkan_datastore
 */
class DatastoreTest extends DkanTestBase {

  /**
   * Tests Construct().
   */
  public function testConstruct() {
    // Setup.
    $mock = $this->getMockBuilder(Datastore::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockEntityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $mockLogger            = $this->createMock(LoggerChannelInterface::class);
    $mockDatastoreHelper   = $this->createMock(DatastoreHelper::class);

    // Assert.
    $mock->__construct($mockEntityTypeManager, $mockLogger, $mockDatastoreHelper);

    $this->assertSame(
            $mockEntityTypeManager,
            $this->readAttribute($mock, 'entityTypeManager')
    );
    $this->assertSame(
            $mockLogger,
            $this->readAttribute($mock, 'logger')
    );
    $this->assertSame(
            $mockDatastoreHelper,
            $this->readAttribute($mock, 'helper')
    );
  }

  /**
   * Tests Import().
   */
  public function testImportNonDeferred() {
    // Setup.
    $mock = $this->getMockBuilder(Datastore::class)
      ->setMethods([
        'getDistributionsFromUuid',
        'queueImport',
        'getResource',
        'processImport',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    // Content doesn't matter at this point.
    $distribution        = new \stdClass();
    $distribtionFromUuid = [
      $distribution,
    ];

    $uuid     = uniqid('foo');
    $deferred = FALSE;

    // Expect.
    $mock->expects($this->once())
      ->method('getDistributionsFromUuid')
      ->with($uuid)
      ->willReturn($distribtionFromUuid);

    $mock->expects($this->never())
      ->method('queueImport');

    $mock->expects($this->never())
      ->method('getResource');

    $mock->expects($this->once())
      ->method('processImport')
      ->with($distribution);

    // Assert.
    $mock->import($uuid, $deferred);
  }

  /**
   * Tests Import() defferred.
   */
  public function testImportDeferred() {
    // Setup.
    $mock = $this->getMockBuilder(Datastore::class)
      ->setMethods([
        'getDistributionsFromUuid',
        'queueImport',
        'getResource',
        'processImport',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $mockResource = $this->createMock(Resource::class);

    // Content doesn't matter at this point.
    $distribution        = new \stdClass();
    $distribtionFromUuid = [
      $distribution,
    ];

    $uuid     = uniqid('foo');
    $deferred = TRUE;

    // Expect.
    $mock->expects($this->once())
      ->method('getDistributionsFromUuid')
      ->with($uuid)
      ->willReturn($distribtionFromUuid);

    $mock->expects($this->once())
      ->method('queueImport')
      ->with($uuid, $mockResource);

    $mock->expects($this->once())
      ->method('getResource')
      ->with($distribution)
      ->willReturn($mockResource);

    $mock->expects($this->never())
      ->method('processImport');

    // Assert.
    $mock->import($uuid, $deferred);
  }

  /**
   * Tests Drop().
   */
  public function testDrop() {
    // Setup.
    $mock = $this->getMockBuilder(Datastore::class)
      ->setMethods([
        'getDistributionsFromUuid',
        'processDrop',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    // Content doesn't matter at this point.
    $distribution        = new \stdClass();
    $distribtionFromUuid = [
      $distribution,
    ];
    $uuid                = uniqid('foo');

    // Expect.
    $mock->expects($this->once())
      ->method('getDistributionsFromUuid')
      ->with($uuid)
      ->willReturn($distribtionFromUuid);

    $mock->expects($this->once())
      ->method('processDrop')
      ->with($distribution);

    // Assert.
    $mock->drop($uuid);
  }

  /**
   * Tests QueueImport().
   */
  public function testQueueImport() {
    // Setup.
    $mock = $this->getMockBuilder(Datastore::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockDeferredImportQueuer = $this->getMockBuilder(DeferredImportQueuer::class)
      ->setMethods(['createDeferredResourceImport'])
      ->disableOriginalConstructor()
      ->getMock();

    $this->setActualContainer([
      'dkan_datastore.manager.deferred_import_queuer' => $mockDeferredImportQueuer,
    ]);

    $mockLogger = $this->getMockBuilder(LoggerChannelInterface::class)
      ->setMethods(['notice'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $this->writeProtectedProperty($mock, 'logger', $mockLogger);

    $mockResource = $this->createMock(Resource::class);

    $uuid    = uniqid('foo');
    $queueId = uniqid('queueid');

    // Expect.
    $mockDeferredImportQueuer->expects($this->once())
      ->method('createDeferredResourceImport')
      ->with($uuid, $mockResource)
      ->willReturn($queueId);

    $mockLogger->expects($this->once())
      ->method('notice')
      ->with("New queue (ID:{$queueId}) was created for `{$uuid}`");

    // Assert.
    $this->invokeProtectedMethod($mock, 'queueImport', $uuid, $mockResource);
  }

  /**
   * Tests ProcessImport().
   */
  public function testProcessImport() {
    // Setup.
    $mock = $this->getMockBuilder(Datastore::class)
      ->setMethods(['getDatastore', 'getResource'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockResource = $this->createMock(Resource::class);

    $mockDatastoreManager = $this->getMockBuilder(Manager::class)
      ->setMethods(['import'])
      ->disableOriginalConstructor()
      ->getMock();

    $distribution = (object) [];

    // Expect.
    $mock->expects($this->once())
      ->method('getResource')
      ->with($distribution)
      ->willReturn($mockResource);

    $mock->expects($this->once())
      ->method('getDatastore')
      ->with($mockResource)
      ->willReturn($mockDatastoreManager);

    $mockDatastoreManager->expects($this->once())
      ->method('import');

    // Assert.
    $this->invokeProtectedMethod($mock, 'processImport', $distribution);
  }

  /**
   * Tests ProcessDrop().
   */
  public function testProcessDrop() {
    // Setup.
    $mock = $this->getMockBuilder(Datastore::class)
      ->setMethods(['getDatastore', 'getResource'])
      ->disableOriginalConstructor()
      ->getMock();

    $mockResource = $this->createMock(Resource::class);

    $mockDatastoreManager = $this->getMockBuilder(Manager::class)
      ->setMethods(['drop'])
      ->disableOriginalConstructor()
      ->getMock();

    $distribution = (object) [];

    // Expect.
    $mock->expects($this->once())
      ->method('getResource')
      ->with($distribution)
      ->willReturn($mockResource);

    $mock->expects($this->once())
      ->method('getDatastore')
      ->with($mockResource)
      ->willReturn($mockDatastoreManager);

    $mockDatastoreManager->expects($this->once())
      ->method('drop');

    // Assert.
    $this->invokeProtectedMethod($mock, 'processDrop', $distribution);
  }

  /**
   * Tests GetResource().
   */
  public function testGetResource() {
    // Setup.
    $mock = $this->getMockBuilder(Datastore::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockHelper = $this->getMockBuilder(DatastoreHelper::class)
      ->setMethods([
        'loadNodeByUuid',
        'newResource',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $this->writeProtectedProperty($mock, 'helper', $mockHelper);

    $mockNode = $this->getMockBuilder(NodeInterface::class)
      ->setMethods(['id'])
      ->disableOriginalConstructor()
      ->getMockForAbstractClass();

    $mockResource = $this->createMock(Resource::class);

    $id           = 42;
    $identifier   = uniqid('identifier');
    $downloadURL  = uniqid('downloadURL');
    $distribution = (object) [
      'identifier' => $identifier,
      'data'       => (object) [
        'downloadURL' => $downloadURL,
      ],
    ];

    // Expect.
    $mockHelper->expects($this->once())
      ->method('loadNodeByUuid')
      ->with($identifier)
      ->willReturn($mockNode);

    $mockNode->expects($this->once())
      ->method('id')
      ->willReturn($id);

    $mockHelper->expects($this->once())
      ->method('newResource')
      ->with($id, $downloadURL)
      ->willReturn($mockResource);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getResource', $distribution);
    $this->assertSame($mockResource, $actual);
  }

  /**
   * Tests GetDatastore().
   */
  public function testGetDatastore() {
    // Setup.
    $mock = $this->getMockBuilder(Datastore::class)
      ->setMethods(NULL)
      ->disableOriginalConstructor()
      ->getMock();

    $mockBuilder = $this->getMockBuilder(Builder::class)
      ->setMethods([
        'setResource',
        'build',
      ])
      ->disableOriginalConstructor()
      ->getMock();

    $this->setActualContainer([
      'dkan_datastore.manager.builder' => $mockBuilder,
    ]);

    $mockResource = $this->createMock(Resource::class);

    $mockDatastoreManager = $this->createMock(Manager::class);

    // Expect.
    $mockBuilder->expects($this->once())
      ->method('setResource')
      ->with($mockResource)
      ->willReturnSelf();

    $mockBuilder->expects($this->once())
      ->method('build')
      ->willReturn($mockDatastoreManager);

    // Assert.
    $actual = $this->invokeProtectedMethod($mock, 'getDatastore', $mockResource);
    $this->assertSame($mockDatastoreManager, $actual);
  }

}
