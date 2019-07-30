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
 * @group dkan_datastore
 */
class DatastoreTest extends DkanTestBase {

    /**
     * Tests Construct().
     */
    public function testConstruct() {
        // setup
        $mock = $this->getMockBuilder(Datastore::class)
                ->setMethods(null)
                ->disableOriginalConstructor()
                ->getMock();

        $mockEntityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
        $mockLogger            = $this->createMock(LoggerChannelInterface::class);
        $mockDatastoreHelper   = $this->createMock(DatastoreHelper::class);

        // assert

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
        // setup
        $mock = $this->getMockBuilder(Datastore::class)
                ->setMethods([
                    'getDistributionsFromUuid',
                    'queueImport',
                    'getResource',
                    'processImport',
                ])
                ->disableOriginalConstructor()
                ->getMock();

        // content doesn't matter at this point.
        $distribution        = new \stdClass();
        $distribtionFromUuid = [
            $distribution,
        ];

        $uuid     = uniqid('foo');
        $deferred = false;

        // expect
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

        // assert
        $mock->import($uuid, $deferred);
    }

    /**
     * Tests Import() defferred.
     */
    public function testImportDeferred() {
        // setup
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

        // content doesn't matter at this point.
        $distribution        = new \stdClass();
        $distribtionFromUuid = [
            $distribution,
        ];

        $uuid     = uniqid('foo');
        $deferred = true;

        // expect
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

        // assert
        $mock->import($uuid, $deferred);
    }

    /**
     * Tests Drop().
     */
    public function testDrop() {
        // setup
        $mock = $this->getMockBuilder(Datastore::class)
                ->setMethods([
                    'getDistributionsFromUuid',
                    'processDrop',
                ])
                ->disableOriginalConstructor()
                ->getMock();

        // content doesn't matter at this point.
        $distribution        = new \stdClass();
        $distribtionFromUuid = [
            $distribution,
        ];
        $uuid                = uniqid('foo');

        // expect
        $mock->expects($this->once())
                ->method('getDistributionsFromUuid')
                ->with($uuid)
                ->willReturn($distribtionFromUuid);

        $mock->expects($this->once())
                ->method('processDrop')
                ->with($distribution);

        // assert
        $mock->drop($uuid);
    }

    /**
     * Tests QueueImport().
     */
    public function testQueueImport() {
        // setup
        $mock = $this->getMockBuilder(Datastore::class)
                ->setMethods(null)
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

        // expect
        $mockDeferredImportQueuer->expects($this->once())
                ->method('createDeferredResourceImport')
                ->with($uuid, $mockResource)
                ->willReturn($queueId);

        $mockLogger->expects($this->once())
                ->method('notice')
                ->with("New queue (ID:{$queueId}) was created for `{$uuid}`");

        // assert
        $this->invokeProtectedMethod($mock, 'queueImport', $uuid, $mockResource);
    }

    /**
     * Tests ProcessImport().
     */
    public function testProcessImport() {
        // setup
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

        // expect
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

        // assert
        $this->invokeProtectedMethod($mock, 'processImport', $distribution);
    }

    /**
     * Tests ProcessDrop().
     */
    public function testProcessDrop() {
        // setup
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

        // expect
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

        // assert
        $this->invokeProtectedMethod($mock, 'processDrop', $distribution);
    }

    /**
     * Tests GetResource().
     */
    public function testGetResource() {
        // setup
        $mock = $this->getMockBuilder(Datastore::class)
                ->setMethods(null)
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
                    ]
        ];

        // expect
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

        // assert
        $actual = $this->invokeProtectedMethod($mock, 'getResource', $distribution);
        $this->assertSame($mockResource, $actual);
    }

    /**
     * Tests GetDatastore().
     */
    public function testGetDatastore() {
        // setup
        $mock = $this->getMockBuilder(Datastore::class)
                ->setMethods(null)
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

        // expect

        $mockBuilder->expects($this->once())
                ->method('setResource')
                ->with($mockResource)
                ->willReturnSelf();

        $mockBuilder->expects($this->once())
                ->method('build')
                ->willReturn($mockDatastoreManager);

        // assert
        $actual = $this->invokeProtectedMethod($mock, 'getDatastore', $mockResource);
        $this->assertSame($mockDatastoreManager, $actual);
    }

}
